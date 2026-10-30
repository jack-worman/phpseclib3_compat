<?php

/**
 * RSA Public Key
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

/**
 * Raw RSA Key Handler
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
final class PublicKey extends RSA implements Common\PublicKey
{
    /**
     * Verifies a signature
     *
     * @see self::sign()
     * @param string $message
     * @param string $signature
     * @return bool
     */
    public function verify($message, $signature)
    {
        return $this->call('verify', $message, $signature);
    }

    /**
     * Encryption
     *
     * Both self::PADDING_OAEP and self::PADDING_PKCS1 both place limits on how long $plaintext can be.
     * If $plaintext exceeds those limits it will be broken up so that it does and the resultant ciphertext's will
     * be concatenated together.
     *
     * @see self::decrypt()
     * @param string $plaintext
     * @return bool|string
     * @throws \LengthException if the RSA modulus is too short
     */
    public function encrypt($plaintext)
    {
        return $this->call('encrypt', $plaintext);
    }

    /**
     * Converts a public key to a private key
     *
     * @return RSA
     */
    public function asPrivateKey()
    {
        return $this->call('asPrivateKey');
    }

    /**
     * Returns the public key's fingerprint
     *
     * The public key's fingerprint is returned, which is equivalent to running `ssh-keygen -lf rsa.pub`. If there is
     * no public key currently loaded, false is returned.
     * Example output (md5): "c1:b1:30:29:d7:b8:de:6c:97:77:10:d7:46:41:63:87" (as specified by RFC 4716)
     *
     * @param string $algorithm The hashing algorithm to be used. Valid options are 'md5' and 'sha256'. False is returned
     * for invalid values.
     * @return mixed
     */
    public function getFingerprint($algorithm = 'md5')
    {
        return $this->call('getFingerprint', $algorithm);
    }

    /**
     * Returns the public key
     *
     * The public key is only returned under two circumstances - if the private key had the public key embedded within it
     * or if the public key was set via setPublicKey().  If the currently loaded key is supposed to be the public key this
     * function won't return it since this library, for the most part, doesn't distinguish between public and private keys.
     *
     * @param string $type
     * @param array $options optional
     * @return mixed
     */
    public function toString($type, array $options = [])
    {
        // phpseclib 4.0 doesn't have Raw::savePublicKey()
        if (strtolower($type) == 'raw') {
            return Convert::toV3((function () {
                return ['e' => clone $this->publicExponent, 'n' => clone $this->modulus];
            })->call($this->v4));
        }
        return $this->call('toString', $type, $options);
    }
}
