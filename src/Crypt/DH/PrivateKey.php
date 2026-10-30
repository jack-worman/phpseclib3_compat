<?php

/**
 * DH Private Key
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2015 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt\DH;

use phpseclib3\Crypt\DH;

/**
 * DH Private Key
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
final class PrivateKey extends DH
{
    /**
     * Returns the public key
     *
     * @return PublicKey
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
