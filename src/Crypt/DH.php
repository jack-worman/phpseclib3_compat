<?php

/**
 * Pure-PHP (EC)DH implementation
 *
 * Here's an example of how to compute a shared secret with this library:
 * <code>
 * <?php
 * include 'vendor/autoload.php';
 *
 * $ourPrivate = \phpseclib3\Crypt\DH::createKey();
 * $secret = DH::computeSecret($ourPrivate, $theirPublic);
 *
 * ?>
 * </code>
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2016 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt;

use phpseclib3\Crypt\Common\AsymmetricKey;

/**
 * Pure-PHP (EC)DH implementation
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class DH extends AsymmetricKey
{
    /**
     * Algorithm Name
     *
     * @var string
     */
    const ALGORITHM = 'DH';

    /**
     * Create DH parameters
     *
     * This method is a bit polymorphic. It can take any of the following:
     *  - two BigInteger's (prime and base)
     *  - an integer representing the size of the prime in bits (the base is assumed to be 2)
     *  - a string (eg. diffie-hellman-group14-sha1)
     *
     * @return Parameters
     */
    public static function createParameters(...$args)
    {
        return self::callStatic('createParameters', ...$args);
    }

    /**
     * Create public / private key pair.
     *
     * The rationale for the second parameter is described in http://tools.ietf.org/html/rfc4419#section-6.2 :
     *
     * "To increase the speed of the key exchange, both client and server may
     *  reduce the size of their private exponents.  It should be at least
     *  twice as long as the key material that is generated from the shared
     *  secret.  For more details, see the paper by van Oorschot and Wiener
     *  [VAN-OORSCHOT]."
     *
     * $length is in bits
     *
     * @param Parameters $params
     * @param int $length optional
     * @return PrivateKey
     */
    public static function createKey(\phpseclib3\Crypt\DH\Parameters $params, $length = 0)
    {
        return self::callStatic('createKey', $params, $length);
    }

    /**
     * Compute Shared Secret
     *
     * @param PrivateKey|EC $private
     * @param PublicKey|BigInteger|string $public
     * @return mixed
     */
    public static function computeSecret($private, $public)
    {
        return self::callStatic('computeSecret', $private, $public);
    }

    /**
     * Load the key
     *
     * @param string $key
     * @param string $password optional
     * @return AsymmetricKey
     */
    public static function load($key, $password = false)
    {
        return self::callStatic('load', $key, self::password($password));
    }

    /**
     * Returns the parameters
     *
     * A public / private key is only returned if the currently loaded "key" contains an x or y
     * value.
     *
     * @see self::getPublicKey()
     * @return mixed
     */
    public function getParameters()
    {
        return $this->call('getParameters');
    }
}
