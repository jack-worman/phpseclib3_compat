<?php

/**
 * Pure-PHP PKCS#1 (v2.1) compliant implementation of RSA.
 *
 * Here's an example of how to encrypt and decrypt text with this library:
 * <code>
 * <?php
 * include 'vendor/autoload.php';
 *
 * $private = Crypt\RSA::createKey();
 * $public = $private->getPublicKey();
 *
 * $plaintext = 'terrafrost';
 *
 * $ciphertext = $public->encrypt($plaintext);
 *
 * echo $private->decrypt($ciphertext);
 * ?>
 * </code>
 *
 * Here's an example of how to create signatures and verify signatures with this library:
 * <code>
 * <?php
 * include 'vendor/autoload.php';
 *
 * $private = Crypt\RSA::createKey();
 * $public = $private->getPublicKey();
 *
 * $plaintext = 'terrafrost';
 *
 * $signature = $private->sign($plaintext);
 *
 * echo $public->verify($plaintext, $signature) ? 'verified' : 'unverified';
 * ?>
 * </code>
 *
 * One thing to consider when using this: so phpseclib uses PSS mode by default.
 * Technically, id-RSASSA-PSS has a different key format than rsaEncryption. So
 * should phpseclib save to the id-RSASSA-PSS format by default or the
 * rsaEncryption format? For stand-alone keys I figure rsaEncryption is better
 * because SSH doesn't use PSS and idk how many SSH servers would be able to
 * decode an id-RSASSA-PSS key. For X.509 certificates the id-RSASSA-PSS
 * format is used by default (unless you change it up to use PKCS1 instead)
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2009 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt;

use phpseclib3\Compat\Convert;
use phpseclib3\Crypt\Common\AsymmetricKey;

/**
 * Pure-PHP PKCS#1 compliant implementation of RSA.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class RSA extends AsymmetricKey
{
    /**
     * Algorithm Name
     *
     * @var string
     */
    const ALGORITHM = 'RSA';

    /**
     * Use {@link http://en.wikipedia.org/wiki/Optimal_Asymmetric_Encryption_Padding Optimal Asymmetric Encryption Padding}
     * (OAEP) for encryption / decryption.
     *
     * Uses sha256 by default
     *
     * @see self::setHash()
     * @see self::setMGFHash()
     * @see self::encrypt()
     * @see self::decrypt()
     */
    const ENCRYPTION_OAEP = 1;

    /**
     * Use PKCS#1 padding.
     *
     * Although self::PADDING_OAEP / self::PADDING_PSS  offers more security, including PKCS#1 padding is necessary for purposes of backwards
     * compatibility with protocols (like SSH-1) written before OAEP's introduction.
     *
     * @see self::encrypt()
     * @see self::decrypt()
     */
    const ENCRYPTION_PKCS1 = 2;

    /**
     * Do not use any padding
     *
     * Although this method is not recommended it can none-the-less sometimes be useful if you're trying to decrypt some legacy
     * stuff, if you're trying to diagnose why an encrypted message isn't decrypting, etc.
     *
     * @see self::encrypt()
     * @see self::decrypt()
     */
    const ENCRYPTION_NONE = 4;

    /**
     * Use the Probabilistic Signature Scheme for signing
     *
     * Uses sha256 and 0 as the salt length
     *
     * @see self::setSaltLength()
     * @see self::setMGFHash()
     * @see self::setHash()
     * @see self::sign()
     * @see self::verify()
     * @see self::setHash()
     */
    const SIGNATURE_PSS = 16;

    /**
     * Use a relaxed version of PKCS#1 padding for signature verification
     *
     * @see self::sign()
     * @see self::verify()
     * @see self::setHash()
     */
    const SIGNATURE_RELAXED_PKCS1 = 32;

    /**
     * Use PKCS#1 padding for signature verification
     *
     * @see self::sign()
     * @see self::verify()
     * @see self::setHash()
     */
    const SIGNATURE_PKCS1 = 64;

    /**
     * Sets the public exponent for key generation
     *
     * This will be 65537 unless changed.
     *
     * @param int $val
     */
    public static function setExponent($val)
    {
        self::callStatic('setExponent', $val);
    }

    /**
     * Sets the smallest prime number in bits. Used for key generation
     *
     * This will be 4096 unless changed.
     *
     * @param int $val
     */
    public static function setSmallestPrime($val)
    {
        self::callStatic('setSmallestPrime', $val);
    }

    /**
     * Create a private key
     *
     * The public key can be extracted from the private key
     *
     * @return PrivateKey
     * @param int $bits
     */
    public static function createKey($bits = 2048)
    {
        return self::callStatic('createKey', $bits);
    }

    /**
     * Returns the key size
     *
     * More specifically, this returns the size of the modulo in bits.
     *
     * @return int
     */
    public function getLength()
    {
        return $this->call('getLength');
    }

    /**
     * Determines which hashing function should be used for the mask generation function
     *
     * The mask generation function is used by self::PADDING_OAEP and self::PADDING_PSS and although it's
     * best if Hash and MGFHash are set to the same thing this is not a requirement.
     *
     * @param string $hash
     */
    public function withMGFHash($hash)
    {
        return $this->call('withMGFHash', $hash);
    }

    /**
     * Returns the MGF hash algorithm currently being used
     */
    public function getMGFHash()
    {
        return $this->call('getMGFHash');
    }

    /**
     * Determines the salt length
     *
     * Used by RSA::PADDING_PSS
     *
     * To quote from {@link http://tools.ietf.org/html/rfc3447#page-38 RFC3447#page-38}:
     *
     *    Typical salt lengths in octets are hLen (the length of the output
     *    of the hash function Hash) and 0.
     *
     * @param int $sLen
     */
    public function withSaltLength($sLen)
    {
        return $this->call('withSaltLength', $sLen);
    }

    /**
     * Returns the salt length currently being used
     *
     */
    public function getSaltLength()
    {
        return $this->call('getSaltLength');
    }

    /**
     * Determines the label
     *
     * Used by RSA::PADDING_OAEP
     *
     * To quote from {@link http://tools.ietf.org/html/rfc3447#page-17 RFC3447#page-17}:
     *
     *    Both the encryption and the decryption operations of RSAES-OAEP take
     *    the value of a label L as input.  In this version of PKCS #1, L is
     *    the empty string; other uses of the label are outside the scope of
     *    this document.
     *
     * @param string $label
     */
    public function withLabel($label)
    {
        return $this->call('withLabel', $label);
    }

    /**
     * Returns the label currently being used
     *
     */
    public function getLabel()
    {
        return $this->call('getLabel');
    }

    /**
     * Determines the padding modes
     *
     * Example: $key->withPadding(RSA::ENCRYPTION_PKCS1 | RSA::SIGNATURE_PKCS1);
     *
     * @param int $padding
     */
    public function withPadding($padding)
    {
        return $this->call('withPadding', Convert::rsaPaddingToV4($padding));
    }

    /**
     * Returns the padding currently being used
     *
     */
    public function getPadding()
    {
        return Convert::rsaPaddingToV3($this->call('getPadding'));
    }

    /**
     * Enable RSA Blinding
     *
     */
    public static function enableBlinding()
    {
        self::callStatic('enableBlinding');
    }

    /**
     * Disable RSA Blinding
     *
     */
    public static function disableBlinding()
    {
        self::callStatic('disableBlinding');
    }

    public static function enableSaltLengthDiscovery()
    {
        self::callStatic('enableSaltLengthDiscovery');
    }

    public static function disableSaltLengthDiscovery()
    {
        self::callStatic('disableSaltLengthDiscovery');
    }
}
