<?php

/**
 * RSA Private Key
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2015 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt\RSA;

use phpseclib3\Crypt\Common;
use phpseclib3\Crypt\RSA;
use phpseclib3\Compat\Convert;
use phpseclib3\Exception\UnsupportedFormatException;

/**
 * Raw RSA Key Handler
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
final class PrivateKey extends RSA implements Common\PrivateKey
{
    /**
     * Create a signature
     *
     * @see self::verify()
     * @param string $message
     * @return string
     */
    public function sign($message)
    {
        return $this->call('sign', $message);
    }

    /**
     * Decryption
     *
     * @see self::encrypt()
     * @param string $ciphertext
     * @return bool|string
     */
    public function decrypt($ciphertext)
    {
        return $this->call('decrypt', $ciphertext);
    }

    /**
     * Returns the public key
     *
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->call('getPublicKey');
    }

    /**
     * Sets the password
     *
     * Private keys can be encrypted with a password.  To unset the password, pass in the empty string or false.
     * Or rather, pass in $password such that empty($password) && !is_string($password) is true.
     *
     * @see self::createKey()
     * @see self::load()
     * @param string|bool $password
     */
    public function withPassword($password = false)
    {
        return $this->call('withPassword', self::password($password));
    }

    /**
     * Returns the private key
     *
     * @param string $type
     * @param array $options optional
     * @return string
     */
    public function toString($type, array $options = [])
    {
        // phpseclib 4.0 doesn't have Raw::savePrivateKey()
        if (strtolower($type) == 'raw') {
            return Convert::toV3((function () {
                // like phpseclib 3.0, keys without primes (eg. from PublicKey::asPrivateKey()) are saved
                // as public keys
                if (empty($this->primes)) {
                    return ['e' => clone $this->exponent, 'n' => clone $this->modulus];
                }
                if ($this->hasPassword()) {
                    throw new UnsupportedFormatException('Raw private keys do not support encryption');
                }
                return [
                    'e' => clone $this->publicExponent,
                    'n' => clone $this->modulus,
                    'd' => clone $this->privateExponent,
                    'primes' => $this->primes,
                    'exponents' => $this->exponents,
                    'coefficients' => $this->coefficients
                ];
            })->call($this->v4));
        }
        return $this->call('toString', $type, $options);
    }
}
