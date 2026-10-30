<?php

/**
 * EC Private Key
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2015 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt\EC;

use phpseclib3\Crypt\Common;
use phpseclib3\Crypt\EC;

/**
 * EC Private Key
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
final class PrivateKey extends EC implements Common\PrivateKey
{
    /**
     * Multiplies an encoded point by the private key
     *
     * Used by ECDH
     *
     * @param string $coordinates
     * @return string
     */
    public function multiply($coordinates)
    {
        return $this->call('multiply', $coordinates);
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
     * Returns the public key
     *
     * @see self::getPrivateKey()
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
}
