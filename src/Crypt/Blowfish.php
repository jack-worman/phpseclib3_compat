<?php

/**
 * Pure-PHP implementation of Blowfish.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * Useful resources are as follows:
 *
 *  - {@link http://en.wikipedia.org/wiki/Blowfish_(cipher) Wikipedia description of Blowfish}
 *
 * # An overview of bcrypt vs Blowfish
 *
 * OpenSSH private keys use a customized version of bcrypt. Specifically, instead of
 * encrypting OrpheanBeholderScryDoubt 64 times OpenSSH's bcrypt variant encrypts
 * OxychromaticBlowfishSwatDynamite 64 times. so we can't use crypt().
 *
 * bcrypt is basically Blowfish but instead of performing the key expansion once it performs
 * the expansion 129 times for each round, with the first key expansion interleaving the salt
 * and password. This renders OpenSSL unusable and forces us to use a pure-PHP implementation
 * of blowfish.
 *
 * # phpseclib's four different _encryptBlock() implementations
 *
 * When using Blowfish as an encryption algorithm, _encryptBlock() is called 9 + 512 +
 * (the number of blocks in the plaintext) times.
 *
 * Each of the first 9 calls to _encryptBlock() modify the P-array. Each of the next 512
 * calls modify the S-boxes. The remaining _encryptBlock() calls operate on the plaintext to
 * produce the ciphertext. In the pure-PHP implementation of Blowfish these remaining
 * _encryptBlock() calls are highly optimized through the use of eval(). Among other things,
 * P-array lookups are eliminated by hard-coding the key-dependent P-array values, and thus we
 * have explained 2 of the 4 different _encryptBlock() implementations.
 *
 * With bcrypt things are a bit different. _encryptBlock() is called 1,079,296 times,
 * assuming 16 rounds (which is what OpenSSH's bcrypt defaults to). The eval()-optimized
 * _encryptBlock() isn't as beneficial because the P-array values are not constant. Well, they
 * are constant, but only for, at most, 777 _encryptBlock() calls, which is equivalent to ~6KB
 * of data. The average length of back to back _encryptBlock() calls with a fixed P-array is
 * 514.12, which is ~4KB of data. Creating an eval()-optimized _encryptBlock() has an upfront
 * cost, which is CPU dependent and is probably not going to be worth it for just ~4KB of
 * data. Conseqeuently, bcrypt does not benefit from the eval()-optimized _encryptBlock().
 *
 * The regular _encryptBlock() does unpack() and pack() on every call, as well, and that can
 * begin to add up after one million function calls.
 *
 * In theory, one might think that it might be beneficial to rewrite all block ciphers so
 * that, instead of passing strings to _encryptBlock(), you convert the string to an array of
 * integers and then pass successive subarrays of that array to _encryptBlock. This, however,
 * kills PHP's memory use. Like let's say you have a 1MB long string. After doing
 * $in = str_repeat('a', 1024 * 1024); PHP's memory utilization jumps up by ~1MB. After doing
 * $blocks = str_split($in, 4); it jumps up by an additional ~16MB. After
 * $blocks = array_map(fn($x) => unpack('N*', $x), $blocks); it jumps up by an additional
 * ~90MB, yielding a 106x increase in memory usage. Consequently, it bcrypt calls a different
 * _encryptBlock() then the regular Blowfish does. That said, the Blowfish _encryptBlock() is
 * basically just a thin wrapper around the bcrypt _encryptBlock(), so there's that.
 *
 * This explains 3 of the 4 _encryptBlock() implementations. the last _encryptBlock()
 * implementation can best be understood by doing Ctrl + F and searching for where
 * self::$use_reg_intval is defined.
 *
 * # phpseclib's three different _setupKey() implementations
 *
 * Every bcrypt round is the equivalent of encrypting 512KB of data. Since OpenSSH uses 16
 * rounds by default that's ~8MB of data that's essentially being encrypted whenever
 * you use bcrypt. That's a lot of data, however, bcrypt operates within tighter constraints
 * than regular Blowfish, so we can use that to our advantage. In particular, whereas Blowfish
 * supports variable length keys, in bcrypt, the initial "key" is the sha512 hash of the
 * password. sha512 hashes are 512 bits or 64 bytes long and thus the bcrypt keys are of a
 * fixed length whereas Blowfish keys are not of a fixed length.
 *
 * bcrypt actually has two different key expansion steps. The first one (expandstate) is
 * constantly XOR'ing every _encryptBlock() parameter against the salt prior _encryptBlock()'s
 * being called. The second one (expand0state) is more similar to Blowfish's _setupKey()
 * but it can still use the fixed length key optimization discussed above and can do away with
 * the pack() / unpack() calls.
 *
 * I suppose _setupKey() could be made to be a thin wrapper around expandstate() but idk it's
 * just a lot of work for very marginal benefits as _setupKey() is only called once for
 * regular Blowfish vs the 128 times it's called --per round-- with bcrypt.
 *
 * # blowfish + bcrypt in the same class
 *
 * Altho there's a lot of Blowfish code that bcrypt doesn't re-use, bcrypt does re-use the
 * initial S-boxes, the initial P-array and the int-only _encryptBlock() implementation.
 *
 * # Credit
 *
 * phpseclib's bcrypt implementation is based losely off of OpenSSH's implementation:
 *
 * https://github.com/openssh/openssh-portable/blob/master/openbsd-compat/bcrypt_pbkdf.c
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $blowfish = new \phpseclib3\Crypt\Blowfish('ctr');
 *
 *    $blowfish->setKey('12345678901234567890123456789012');
 *
 *    $plaintext = str_repeat('a', 1024);
 *
 *    echo $blowfish->decrypt($blowfish->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @author    Hans-Juergen Petrich <petrich@tronic-media.com>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt;

use phpseclib3\Crypt\Common\BlockCipher;
use phpseclib3\Compat\Convert;
use phpseclib4\Exception\BaseException;

/**
 * Pure-PHP implementation of Blowfish.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @author  Hans-Juergen Petrich <petrich@tronic-media.com>
 */
class Blowfish extends BlockCipher
{
    /**
     * Performs OpenSSH-style bcrypt
     *
     * @param string $pass
     * @param string $salt
     * @param int $keylen
     * @param int $rounds
     * @access public
     * @return string
     */
    public static function bcrypt_pbkdf($pass, $salt, $keylen, $rounds)
    {
        try {
            return \phpseclib4\Crypt\Blowfish::bcrypt_pbkdf($pass, $salt, $keylen, $rounds);
        } catch (BaseException $e) {
            throw Convert::exception($e);
        }
    }
}
