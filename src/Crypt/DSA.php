<?php

/**
 * Pure-PHP FIPS 186-4 compliant implementation of DSA.
 *
 * Here's an example of how to create signatures and verify signatures with this library:
 * <code>
 * <?php
 * include 'vendor/autoload.php';
 *
 * $private = \phpseclib3\Crypt\DSA::createKey();
 * $public = $private->getPublicKey();
 *
 * $plaintext = 'terrafrost';
 *
 * $signature = $private->sign($plaintext);
 *
 * echo $public->verify($plaintext, $signature) ? 'verified' : 'unverified';
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
 * Pure-PHP FIPS 186-4 compliant implementation of DSA.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class DSA extends AsymmetricKey
{
    /**
     * Algorithm Name
     *
     * @var string
     */
    const ALGORITHM = 'DSA';

    /**
     * Create DSA parameters
     *
     * @param int $L
     * @param int $N
     * @return DSA|bool
     */
    public static function createParameters($L = 2048, $N = 224)
    {
        return self::callStatic('createParameters', $L, $N);
    }

    /**
     * Create public / private key pair.
     *
     * This method is a bit polymorphic. It can take a DSA/Parameters object, L / N as two distinct parameters or
     * no parameters (at which point L and N will be generated with this method)
     *
     * Returns the private key, from which the publickey can be extracted
     *
     * @param int[] ...$args
     * @return PrivateKey
     */
    public static function createKey(...$args)
    {
        return self::callStatic('createKey', ...$args);
    }

    /**
     * Returns the key size
     *
     * More specifically, this L (the length of DSA Prime P) and N (the length of DSA Group Order q)
     *
     * @return array
     */
    public function getLength()
    {
        return $this->call('getLength');
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

    /**
     * Determines the signature padding mode
     *
     * Valid values are: ASN1, SSH2, Raw
     *
     * @param string $format
     */
    public function withSignatureFormat($format)
    {
        return $this->call('withSignatureFormat', $format);
    }

    /**
     * Returns the signature format currently being used
     *
     */
    public function getSignatureFormat()
    {
        return $this->call('getSignatureFormat');
    }
}
