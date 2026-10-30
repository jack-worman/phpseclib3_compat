<?php

/**
 * Base Class for all \phpseclib3\Crypt\* cipher classes
 *
 * Internally for phpseclib developers:
 *  If you plan to add a new cipher class, please note following rules:
 *
 *  - The new \phpseclib3\Crypt\* cipher class should extend \phpseclib3\Crypt\Common\SymmetricKey
 *
 *  - Following methods are then required to be overridden/overloaded:
 *
 *    - encryptBlock()
 *
 *    - decryptBlock()
 *
 *    - setupKey()
 *
 *  - All other methods are optional to be overridden/overloaded
 *
 *  - Look at the source code of the current ciphers how they extend \phpseclib3\Crypt\Common\SymmetricKey
 *    and take one of them as a start up for the new cipher class.
 *
 *  - Please read all the other comments/notes/hints here also for each class var/method
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @author    Hans-Juergen Petrich <petrich@tronic-media.com>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt\Common;

use phpseclib3\Compat\Convert;
use phpseclib3\Compat\Wrapper;
use phpseclib3\Compat\WrapperTrait;
use phpseclib4\Exception\BaseException;

/**
 * Base Class for all \phpseclib3\Crypt\* cipher classes
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @author  Hans-Juergen Petrich <petrich@tronic-media.com>
 */
abstract class SymmetricKey implements Wrapper
{
    use WrapperTrait;

    /**
     * Encrypt / decrypt using the Counter mode.
     *
     * Set to -1 since that's what Crypt/Random.php uses to index the CTR mode.
     *
     * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Counter_.28CTR.29
     * SymmetricKey::encrypt()
     * SymmetricKey::decrypt()
     */
    const MODE_CTR = -1;

    /**
     * Encrypt / decrypt using the Electronic Code Book mode.
     *
     * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Electronic_codebook_.28ECB.29
     * SymmetricKey::encrypt()
     * SymmetricKey::decrypt()
     */
    const MODE_ECB = 1;

    /**
     * Encrypt / decrypt using the Code Book Chaining mode.
     *
     * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher-block_chaining_.28CBC.29
     * SymmetricKey::encrypt()
     * SymmetricKey::decrypt()
     */
    const MODE_CBC = 2;

    /**
     * Encrypt / decrypt using the Cipher Feedback mode.
     *
     * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher_feedback_.28CFB.29
     * SymmetricKey::encrypt()
     * SymmetricKey::decrypt()
     */
    const MODE_CFB = 3;

    /**
     * Encrypt / decrypt using the Cipher Feedback mode (8bit)
     *
     * SymmetricKey::encrypt()
     * SymmetricKey::decrypt()
     */
    const MODE_CFB8 = 7;

    /**
     * Encrypt / decrypt using the Output Feedback mode (8bit)
     *
     * SymmetricKey::encrypt()
     * SymmetricKey::decrypt()
     */
    const MODE_OFB8 = 8;

    /**
     * Encrypt / decrypt using the Output Feedback mode.
     *
     * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Output_feedback_.28OFB.29
     * SymmetricKey::encrypt()
     * SymmetricKey::decrypt()
     */
    const MODE_OFB = 4;

    /**
     * Encrypt / decrypt using Galois/Counter mode.
     *
     * @link https://en.wikipedia.org/wiki/Galois/Counter_Mode
     * SymmetricKey::encrypt()
     * SymmetricKey::decrypt()
     */
    const MODE_GCM = 5;

    /**
     * Encrypt / decrypt using streaming mode.
     *
     * SymmetricKey::encrypt()
     * SymmetricKey::decrypt()
     */
    const MODE_STREAM = 6;

    /**
     * Mode Map
     *
     * SymmetricKey::__construct()
     */
    const MODE_MAP = [
        'ctr' => -1,
        'ecb' => 1,
        'cbc' => 2,
        'cfb' => 3,
        'cfb8' => 7,
        'ofb' => 4,
        'ofb8' => 8,
        'gcm' => 5,
        'stream' => 6,
          ];

    /**
     * Base value for the internal implementation $engine switch
     *
     * SymmetricKey::__construct()
     */
    const ENGINE_INTERNAL = 1;

    /**
     * Base value for the eval() implementation $engine switch
     *
     * SymmetricKey::__construct()
     */
    const ENGINE_EVAL = 2;

    /**
     * Base value for the mcrypt implementation $engine switch
     *
     * SymmetricKey::__construct()
     */
    const ENGINE_MCRYPT = 3;

    /**
     * Base value for the openssl implementation $engine switch
     *
     * SymmetricKey::__construct()
     */
    const ENGINE_OPENSSL = 4;

    /**
     * Base value for the libsodium implementation $engine switch
     *
     * SymmetricKey::__construct()
     */
    const ENGINE_LIBSODIUM = 5;

    /**
     * Base value for the openssl / gcm implementation $engine switch
     *
     * SymmetricKey::__construct()
     */
    const ENGINE_OPENSSL_GCM = 6;

    /**
     * Engine Reverse Map
     *
     * SymmetricKey::getEngine()
     */
    const ENGINE_MAP = [
        1 => 'PHP',
        2 => 'Eval',
        3 => 'mcrypt',
        4 => 'OpenSSL',
        5 => 'libsodium',
        6 => 'OpenSSL (GCM)',
          ];

    /**
     * Default Constructor.
     *
     * $mode could be:
     *
     * - ecb
     *
     * - cbc
     *
     * - ctr
     *
     * - cfb
     *
     * - cfb8
     *
     * - ofb
     *
     * - ofb8
     *
     * - gcm
     *
     * @param string $mode
     * @throws BadModeException if an invalid / unsupported mode is provided
     */
    public function __construct($mode)
    {
        $class = Convert::v4Class(static::class);
        try {
            $this->v4 = new $class($mode);
        } catch (BaseException $e) {
            throw Convert::exception($e);
        }
    }

    /**
     * Sets the initialization vector.
     *
     * setIV() is not required when ecb or gcm modes are being used.
     *
     * {@internal Can be overwritten by a sub class, but does not have to be}
     *
     * @param string $iv
     * @throws \LengthException if the IV length isn't equal to the block size
     * @throws \BadMethodCallException if an IV is provided when one shouldn't be
     */
    public function setIV($iv)
    {
        $this->call('setIV', $iv);
    }

    /**
     * Enables Poly1305 mode.
     *
     * Once enabled Poly1305 cannot be disabled.
     *
     * @throws \BadMethodCallException if Poly1305 is enabled whilst in GCM mode
     */
    public function enablePoly1305()
    {
        $this->call('enablePoly1305');
    }

    /**
     * Enables Poly1305 mode.
     *
     * Once enabled Poly1305 cannot be disabled. If $key is not passed then an attempt to call createPoly1305Key
     * will be made.
     *
     * @param string $key optional
     * @throws \LengthException if the key isn't long enough
     * @throws \BadMethodCallException if Poly1305 is enabled whilst in GCM mode
     */
    public function setPoly1305Key($key = null)
    {
        $this->call('setPoly1305Key', $key);
    }

    /**
     * Sets the nonce.
     *
     * setNonce() is only required when gcm is used
     *
     * @param string $nonce
     * @throws \BadMethodCallException if an nonce is provided when one shouldn't be
     */
    public function setNonce($nonce)
    {
        $this->call('setNonce', $nonce);
    }

    /**
     * Sets additional authenticated data
     *
     * setAAD() is only used by gcm or in poly1305 mode
     *
     * @param string $aad
     * @throws \BadMethodCallException if mode isn't GCM or if poly1305 isn't being utilized
     */
    public function setAAD($aad)
    {
        $this->call('setAAD', $aad);
    }

    /**
     * Returns whether or not the algorithm uses an IV
     *
     * @return bool
     */
    public function usesIV()
    {
        return $this->call('usesIV');
    }

    /**
     * Returns whether or not the algorithm uses a nonce
     *
     * @return bool
     */
    public function usesNonce()
    {
        return $this->call('usesNonce');
    }

    /**
     * Returns the current key length in bits
     *
     * @return int
     */
    public function getKeyLength()
    {
        return $this->call('getKeyLength');
    }

    /**
     * Returns the current block length in bits
     *
     * @return int
     */
    public function getBlockLength()
    {
        return $this->call('getBlockLength');
    }

    /**
     * Returns the current block length in bytes
     *
     * @return int
     */
    public function getBlockLengthInBytes()
    {
        return $this->call('getBlockLengthInBytes');
    }

    /**
     * Sets the key length.
     *
     * Keys with explicitly set lengths need to be treated accordingly
     *
     * @param int $length
     */
    public function setKeyLength($length)
    {
        $this->call('setKeyLength', $length);
    }

    /**
     * Sets the key.
     *
     * The min/max length(s) of the key depends on the cipher which is used.
     * If the key not fits the length(s) of the cipher it will paded with null bytes
     * up to the closest valid key length.  If the key is more than max length,
     * we trim the excess bits.
     *
     * If the key is not explicitly set, it'll be assumed to be all null bytes.
     *
     * {@internal Could, but not must, extend by the child Crypt_* class}
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->call('setKey', $key);
    }

    /**
     * Sets the password.
     *
     * Depending on what $method is set to, setPassword()'s (optional) parameters are as follows:
     *     {@link http://en.wikipedia.org/wiki/PBKDF2 pbkdf2} or pbkdf1:
     *         $hash, $salt, $count, $dkLen
     *
     *         Where $hash (default = sha1) currently supports the following hashes: see: Crypt/Hash.php
     *     {@link https://en.wikipedia.org/wiki/Bcrypt bcypt}:
     *         $salt, $rounds, $keylen
     *
     *         This is a modified version of bcrypt used by OpenSSH.
     *
     * {@internal Could, but not must, extend by the child Crypt_* class}
     *
     * @see Crypt/Hash.php
     * @param string $password
     * @param string $method
     * @param int|string ...$func_args
     * @throws \LengthException if pbkdf1 is being used and the derived key length exceeds the hash length
     * @throws \RuntimeException if bcrypt is being used and a salt isn't provided
     * @return bool
     */
    public function setPassword($password, $method = 'pbkdf2', ...$func_args)
    {
        $this->call('setPassword', $password, $method, ...$func_args);
        return true;
    }

    /**
     * Encrypts a message.
     *
     * $plaintext will be padded with additional bytes such that it's length is a multiple of the block size. Other cipher
     * implementations may or may not pad in the same manner.  Other common approaches to padding and the reasons why it's
     * necessary are discussed in the following
     * URL:
     *
     * {@link http://www.di-mgt.com.au/cryptopad.html http://www.di-mgt.com.au/cryptopad.html}
     *
     * An alternative to padding is to, separately, send the length of the file.  This is what SSH, in fact, does.
     * strlen($plaintext) will still need to be a multiple of the block size, however, arbitrary values can be added to make it that
     * length.
     *
     * {@internal Could, but not must, extend by the child Crypt_* class}
     *
     * @see self::decrypt()
     * @param string $plaintext
     * @return string $ciphertext
     */
    public function encrypt($plaintext)
    {
        return $this->call('encrypt', $plaintext);
    }

    /**
     * Decrypts a message.
     *
     * If strlen($ciphertext) is not a multiple of the block size, null bytes will be added to the end of the string until
     * it is.
     *
     * {@internal Could, but not must, extend by the child Crypt_* class}
     *
     * @see self::encrypt()
     * @param string $ciphertext
     * @return string $plaintext
     * @throws \LengthException if we're inside a block cipher and the ciphertext length is not a multiple of the block size
     */
    public function decrypt($ciphertext)
    {
        return $this->call('decrypt', $ciphertext);
    }

    /**
     * Get the authentication tag
     *
     * Only used in GCM or Poly1305 mode
     *
     * @see self::encrypt()
     * @param int $length optional
     * @return string
     * @throws \LengthException if $length isn't of a sufficient length
     * @throws \RuntimeException if GCM mode isn't being used
     */
    public function getTag($length = 16)
    {
        return $this->call('getTag', $length);
    }

    /**
     * Sets the authentication tag
     *
     * Only used in GCM mode
     *
     * @see self::decrypt()
     * @param string $tag
     * @throws \LengthException if $length isn't of a sufficient length
     * @throws \RuntimeException if GCM mode isn't being used
     */
    public function setTag($tag)
    {
        $this->call('setTag', $tag);
    }

    /**
     * Pad "packets".
     *
     * Block ciphers working by encrypting between their specified [$this->]block_size at a time
     * If you ever need to encrypt or decrypt something that isn't of the proper length, it becomes necessary to
     * pad the input so that it is of the proper length.
     *
     * Padding is enabled by default.  Sometimes, however, it is undesirable to pad strings.  Such is the case in SSH,
     * where "packets" are padded with random bytes before being encrypted.  Unpad these packets and you risk stripping
     * away characters that shouldn't be stripped away. (SSH knows how many bytes are added because the length is
     * transmitted separately)
     *
     * @see self::disablePadding()
     */
    public function enablePadding()
    {
        $this->call('enablePadding');
    }

    /**
     * Do not pad packets.
     *
     * @see self::enablePadding()
     */
    public function disablePadding()
    {
        $this->call('disablePadding');
    }

    /**
     * Treat consecutive "packets" as if they are a continuous buffer.
     *
     * Say you have a 32-byte plaintext $plaintext.  Using the default behavior, the two following code snippets
     * will yield different outputs:
     *
     * <code>
     *    echo $rijndael->encrypt(substr($plaintext,  0, 16));
     *    echo $rijndael->encrypt(substr($plaintext, 16, 16));
     * </code>
     * <code>
     *    echo $rijndael->encrypt($plaintext);
     * </code>
     *
     * The solution is to enable the continuous buffer.  Although this will resolve the above discrepancy, it creates
     * another, as demonstrated with the following:
     *
     * <code>
     *    $rijndael->encrypt(substr($plaintext, 0, 16));
     *    echo $rijndael->decrypt($rijndael->encrypt(substr($plaintext, 16, 16)));
     * </code>
     * <code>
     *    echo $rijndael->decrypt($rijndael->encrypt(substr($plaintext, 16, 16)));
     * </code>
     *
     * With the continuous buffer disabled, these would yield the same output.  With it enabled, they yield different
     * outputs.  The reason is due to the fact that the initialization vector's change after every encryption /
     * decryption round when the continuous buffer is enabled.  When it's disabled, they remain constant.
     *
     * Put another way, when the continuous buffer is enabled, the state of the \phpseclib3\Crypt\*() object changes after each
     * encryption / decryption round, whereas otherwise, it'd remain constant.  For this reason, it's recommended that
     * continuous buffers not be used.  They do offer better security and are, in fact, sometimes required (SSH uses them),
     * however, they are also less intuitive and more likely to cause you problems.
     *
     * {@internal Could, but not must, extend by the child Crypt_* class}
     *
     * @see self::disableContinuousBuffer()
     */
    public function enableContinuousBuffer()
    {
        $this->call('enableContinuousBuffer');
    }

    /**
     * Treat consecutive packets as if they are a discontinuous buffer.
     *
     * The default behavior.
     *
     * {@internal Could, but not must, extend by the child Crypt_* class}
     *
     * @see self::enableContinuousBuffer()
     */
    public function disableContinuousBuffer()
    {
        $this->call('disableContinuousBuffer');
    }

    /**
     * Test for engine validity
     *
     * @see self::__construct()
     * @param string $engine
     * @return bool
     */
    public function isValidEngine($engine)
    {
        return $this->call('isValidEngine', self::engineToV4($engine));
    }

    /**
     * Sets the preferred crypt engine
     *
     * Currently, $engine could be:
     *
     * - libsodium[very fast]
     *
     * - OpenSSL  [very fast]
     *
     * - mcrypt   [fast]
     *
     * - Eval     [slow]
     *
     * - PHP      [slowest]
     *
     * If the preferred crypt engine is not available the fastest available one will be used
     *
     * @see self::__construct()
     * @param string $engine
     */
    public function setPreferredEngine($engine)
    {
        $this->call('setPreferredEngine', is_string($engine) ? self::engineToV4($engine) : '');
    }

    /**
     * Returns the engine currently being utilized
     *
     * @see self::setEngine()
     */
    public function getEngine()
    {
        $engine = $this->call('getEngine');
        return $engine == 'OpenSSL (AEAD)' ? 'OpenSSL (GCM)' : $engine;
    }

    /**
     * Converts a phpseclib 3.0 engine name to a phpseclib 4.0 engine name
     *
     * @param string $engine
     * @return string
     */
    private static function engineToV4($engine)
    {
        return strtolower($engine) == 'openssl (gcm)' ? 'OpenSSL (AEAD)' : $engine;
    }

    /**
     * Return the mode
     *
     * You can do $obj instanceof AES or whatever to get the cipher but you can't do that to get the mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->call('getMode');
    }

    /**
     * Is the continuous buffer enabled?
     *
     * @return boolean
     */
    public function continuousBufferEnabled()
    {
        return $this->call('continuousBufferEnabled');
    }
}
