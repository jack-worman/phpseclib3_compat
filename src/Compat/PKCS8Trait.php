<?php

/**
 * PKCS#8 key format methods that return phpseclib 3.0 values
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Compat;

use phpseclib4\Exception\BaseException;

/**
 * PKCS#8 key format methods that return phpseclib 3.0 values
 *
 * phpseclib 4.0 returns ASN.1 objects where phpseclib 3.0 returned strings
 *
 * @internal
 */
trait PKCS8Trait
{
    /**
     * Break a public or private key down into its constituent components
     *
     * @param string|array $key
     * @param string|false $password optional
     * @return array
     */
    public static function load($key, $password = ''): array
    {
        try {
            $result = parent::load($key, $password === false ? null : $password);
        } catch (BaseException $e) {
            throw Convert::exception($e);
        }
        if (isset($result['meta'])) {
            $result['meta'] = Convert::asn1ToV3($result['meta']);
        }
        return Convert::toV3($result);
    }

    /**
     * Returns the encryption parameters used by the key
     *
     * @param string $key
     * @return array
     */
    public static function extractEncryptionAlgorithm($key): array
    {
        try {
            return Convert::asn1ToV3(parent::extractEncryptionAlgorithm($key));
        } catch (BaseException $e) {
            throw Convert::exception($e);
        }
    }
}
