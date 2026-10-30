<?php

/**
 * PublicKeyLoader
 *
 * Returns a PublicKey or PrivateKey object.
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2009 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt;

use phpseclib3\Compat\Convert;
use phpseclib4\Exception\BaseException;

/**
 * PublicKeyLoader
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class PublicKeyLoader
{
    /**
     * Loads a public or private key
     *
     * @return AsymmetricKey
     * @param string|array $key
     * @param string $password optional
     * @throws NoKeyLoadedException if key is not valid
     */
    public static function load($key, $password = false)
    {
        return self::callStatic('load', $key, $password === false ? null : $password);
    }

    /**
     * Loads a private key
     *
     * @return PrivateKey
     * @param string|array $key
     * @param string $password optional
     */
    public static function loadPrivateKey($key, $password = false)
    {
        return self::callStatic('loadPrivateKey', $key, $password === false ? null : $password);
    }

    /**
     * Loads a public key
     *
     * @return PublicKey
     * @param string|array $key
     */
    public static function loadPublicKey($key)
    {
        return self::callStatic('loadPublicKey', $key);
    }

    /**
     * Loads parameters
     *
     * @return AsymmetricKey
     * @param string|array $key
     */
    public static function loadParameters($key)
    {
        return self::callStatic('loadParameters', $key);
    }

    /**
     * Calls a static method of the phpseclib 4.0 PublicKeyLoader
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    private static function callStatic($method, ...$args)
    {
        try {
            return Convert::toV3(\phpseclib4\Crypt\PublicKeyLoader::$method(...Convert::toV4($args)));
        } catch (BaseException $e) {
            throw Convert::exception($e);
        }
    }
}
