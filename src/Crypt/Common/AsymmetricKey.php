<?php

/**
 * Base Class for all asymmetric key ciphers
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2016 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt\Common;

use phpseclib3\Compat\Convert;
use phpseclib3\Compat\Wrapper;
use phpseclib3\Compat\WrapperTrait;
use phpseclib4\Exception\BaseException;

/**
 * Base Class for all asymmetric cipher classes
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class AsymmetricKey implements Wrapper
{
    use WrapperTrait;

    /**
     * Keys are created with load(), createKey() and the like
     */
    protected function __construct()
    {
    }

    /**
     * @param string $type
     * @return array|string
     */
    public function toString($type, array $options = [])
    {
        return $this->call('toString', $type, $options);
    }

    /**
     * Load the key
     *
     * @param string $key
     * @param string $password optional
     * @return PublicKey|PrivateKey
     */
    public static function load($key, $password = false)
    {
        return self::callStatic('load', $key, self::password($password));
    }

    /**
     * Loads a private key
     *
     * @return PrivateKey
     * @param string|array $key
     * @param string $password optional
     */
    public static function loadPrivateKey($key, $password = '')
    {
        return self::callStatic('loadPrivateKey', $key, self::password($password));
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
     * Load the key, assuming a specific format
     *
     * @param string $type
     * @param string $key
     * @param string $password optional
     * @return static
     */
    public static function loadFormat($type, $key, $password = false)
    {
        return self::callStatic('loadFormat', $type, $key, self::password($password));
    }

    /**
     * Loads a private key
     *
     * @return PrivateKey
     * @param string $type
     * @param string $key
     * @param string $password optional
     */
    public static function loadPrivateKeyFormat($type, $key, $password = false)
    {
        return self::callStatic('loadPrivateKeyFormat', $type, $key, self::password($password));
    }

    /**
     * Loads a public key
     *
     * @return PublicKey
     * @param string $type
     * @param string $key
     */
    public static function loadPublicKeyFormat($type, $key)
    {
        return self::callStatic('loadPublicKeyFormat', $type, $key);
    }

    /**
     * Loads parameters
     *
     * @return AsymmetricKey
     * @param string $type
     * @param string|array $key
     */
    public static function loadParametersFormat($type, $key)
    {
        return self::callStatic('loadParametersFormat', $type, $key);
    }

    /**
     * Returns a list of supported formats.
     *
     * @return array
     */
    public static function getSupportedKeyFormats()
    {
        // phpseclib 3.0 returned the names of the phpseclib3 format classes. formats that only phpseclib 4.0
        // has keep their phpseclib 4.0 names
        return array_map(function ($class) {
            $v3 = 'phpseclib3' . substr($class, 10);
            return strpos($class, 'phpseclib4\\') === 0 && class_exists($v3) ? $v3 : $class;
        }, self::callStatic('getSupportedKeyFormats'));
    }

    /**
     * Sets the OpenSSL config file path
     *
     * Set to the empty string to use the default config file
     *
     * @param string $val
     */
    public static function setOpenSSLConfigPath($val)
    {
        self::callStatic('setOpenSSLConfigPath', $val);
    }

    /**
     * Add a fileformat plugin
     *
     * The plugin needs to either already be loaded or be auto-loadable.
     * Loading a plugin whose shortname overwrite an existing shortname will overwrite the old plugin.
     *
     * @see self::load()
     * @param string $fullname
     * @return bool
     */
    public static function addFileFormat($fullname)
    {
        self::callStatic('addFileFormat', $fullname);
    }

    /**
     * Returns the format of the loaded key.
     *
     * If the key that was loaded wasn't in a valid or if the key was auto-generated
     * with RSA::createKey() then this will throw an exception.
     *
     * @see self::load()
     * @return mixed
     */
    public function getLoadedFormat()
    {
        return $this->call('getLoadedFormat');
    }

    /**
     * Returns the key's comment
     *
     * Not all key formats support comments. If you want to set a comment use toString()
     *
     * @return null|string
     */
    public function getComment()
    {
        return $this->call('getComment');
    }

    /**
     * Force engine (useful for unit testing)
     */
    public static function forceEngine($engine = null)
    {
        self::callStatic('forceEngine', $engine);
    }

    public static function getForcedEngine()
    {
        return self::callStatic('getForcedEngine');
    }

    /**
     * __toString() magic method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->call('__toString');
    }

    /**
     * Calls a static method of the phpseclib 4.0 class
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    protected static function callStatic($method, ...$args)
    {
        $class = Convert::v4Class(static::class);
        try {
            return Convert::toV3($class::$method(...Convert::toV4($args)));
        } catch (BaseException $e) {
            throw Convert::exception($e);
        }
    }

    /**
     * Converts a phpseclib 3.0 password to a phpseclib 4.0 password
     *
     * phpseclib 3.0 uses false for "no password" whereas phpseclib 4.0 uses null
     *
     * @param string|bool|null $password
     * @return string|null
     */
    protected static function password($password)
    {
        return $password === false || $password === null ? null : (string) $password;
    }

    /**
     * Determines which hashing function should be used
     *
     * @param string $hash
     */
    public function withHash($hash)
    {
        return $this->call('withHash', $hash);
    }

    /**
     * Returns the hash algorithm currently being used
     *
     */
    public function getHash()
    {
        return $this->call('getHash');
    }
}
