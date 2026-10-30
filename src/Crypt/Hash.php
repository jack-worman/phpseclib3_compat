<?php

/**
 * Wrapper around hash() and hash_hmac() functions supporting truncated hashes
 * such as sha256-96.  Any hash algorithm returned by hash_algos() (and
 * truncated versions thereof) are supported.
 *
 * If {@link self::setKey() setKey()} is called, {@link self::hash() hash()} will
 * return the HMAC as opposed to the hash.
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $hash = new \phpseclib3\Crypt\Hash('sha512');
 *
 *    $hash->setKey('abcdefg');
 *
 *    echo base64_encode($hash->hash('abcdefg'));
 * ?>
 * </code>
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2015 Jim Wigginton
 * @author    Andreas Fischer <bantu@phpbb.com>
 * @copyright 2015 Andreas Fischer
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt;

use phpseclib3\Compat\Convert;
use phpseclib3\Compat\Wrapper;
use phpseclib3\Compat\WrapperTrait;
use phpseclib4\Exception\BaseException;

/**
 * @author  Jim Wigginton <terrafrost@php.net>
 * @author  Andreas Fischer <bantu@phpbb.com>
 */
class Hash implements Wrapper
{
    use WrapperTrait;

    /**
     * Padding Types
     *
     */
    const PADDING_KECCAK = 1;

    /**
     * Padding Types
     *
     */
    const PADDING_SHA3 = 2;

    /**
     * Padding Types
     *
     */
    const PADDING_SHAKE = 3;

    /**
     * Default Constructor.
     *
     * @param string $hash
     */
    public function __construct($hash = 'sha256')
    {
        try {
            $this->v4 = new \phpseclib4\Crypt\Hash($hash);
        } catch (BaseException $e) {
            throw Convert::exception($e);
        }
    }

    /**
     * Sets the key for HMACs
     *
     * Keys can be of any length.
     *
     * @param string $key
     */
    public function setKey($key = false)
    {
        $this->call('setKey', $key === false ? null : $key);
    }

    /**
     * Sets the nonce for UMACs
     *
     * Keys can be of any length.
     *
     * @param string $nonce
     */
    public function setNonce($nonce = false)
    {
        $this->call('setNonce', $nonce === false ? null : $nonce);
    }

    /**
     * Gets the hash function.
     *
     * As set by the constructor or by the setHash() method.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->call('getHash');
    }

    /**
     * Sets the hash function.
     *
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->call('setHash', $hash);
    }

    /**
     * Compute the Hash / HMAC / UMAC.
     *
     * @param string $text
     * @return string
     */
    public function hash($text)
    {
        return $this->call('hash', $text);
    }

    /**
     * Returns the hash length (in bits)
     *
     * @return int
     */
    public function getLength()
    {
        return $this->call('getLength');
    }

    /**
     * Returns the hash length (in bytes)
     *
     * @return int
     */
    public function getLengthInBytes()
    {
        return $this->call('getLengthInBytes');
    }

    /**
     * Returns the block length (in bits)
     *
     * @return int
     */
    public function getBlockLength()
    {
        return $this->call('getBlockLength');
    }

    /**
     * Returns the block length (in bytes)
     *
     * @return int
     */
    public function getBlockLengthInBytes()
    {
        return $this->call('getBlockLengthInBytes');
    }

    /**
     *  __toString() magic method
     */
    public function __toString()
    {
        return $this->v4->__toString();
    }
}
