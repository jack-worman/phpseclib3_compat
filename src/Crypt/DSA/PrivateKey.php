<?php

/**
 * DSA Private Key
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2015 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt\DSA;

use phpseclib3\Crypt\Common;
use phpseclib3\Crypt\DSA;
use phpseclib3\Compat\Convert;

/**
 * DSA Private Key
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
final class PrivateKey extends DSA implements Common\PrivateKey
{
    /**
     * Returns the public key
     *
     * If you do "openssl rsa -in private.rsa -pubout -outform PEM" you get a PKCS8 formatted key
     * that contains a publicKeyAlgorithm AlgorithmIdentifier and a publicKey BIT STRING.
     * An AlgorithmIdentifier contains an OID and a parameters field. With RSA public keys this
     * parameters field is NULL. With DSA PKCS8 public keys it is not - it contains the p, q and g
     * variables. The publicKey BIT STRING contains, simply, the y variable. This can be verified
     * by getting a DSA PKCS8 public key:
     *
     * "openssl dsa -in private.dsa -pubout -outform PEM"
     *
     * ie. just swap out rsa with dsa in the rsa command above.
     *
     * A PKCS1 public key corresponds to the publicKey portion of the PKCS8 key. In the case of RSA
     * the publicKey portion /is/ the key. In the case of DSA it is not. You cannot verify a signature
     * without the parameters and the PKCS1 DSA public key format does not include the parameters.
     *
     * @see self::getPrivateKey()
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->call('getPublicKey');
    }

    /**
     * Create a signature
     *
     * @see self::verify()
     * @param string $message
     * @return mixed
     */
    public function sign($message)
    {
        return $this->call('sign', $message);
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
                return ['p' => $this->p, 'q' => $this->q, 'g' => $this->g, 'y' => $this->y, 'x' => $this->x];
            })->call($this->v4));
        }
        return $this->call('toString', $type, $options);
    }
}
