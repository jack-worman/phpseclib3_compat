<?php

/**
 * Pure-PHP implementation of EC.
 *
 * Here's an example of how to create signatures and verify signatures with this library:
 * <code>
 * <?php
 * include 'vendor/autoload.php';
 *
 * $private = \phpseclib3\Crypt\EC::createKey('secp256k1');
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
 * Pure-PHP implementation of EC.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class EC extends AsymmetricKey
{
    /**
     * Algorithm Name
     *
     * @var string
     */
    const ALGORITHM = 'EC';

    /**
     * Create public / private key pair.
     *
     * @param string $curve
     * @return PrivateKey
     */
    public static function createKey($curve)
    {
        return self::callStatic('createKey', $curve);
    }

    /**
     * Returns the curve
     *
     * Returns a string if it's a named curve, an array if not
     *
     * @return string|array
     */
    public function getCurve()
    {
        return $this->call('getCurve');
    }

    /**
     * Returns the key size
     *
     * Quoting https://tools.ietf.org/html/rfc5656#section-2,
     *
     * "The size of a set of elliptic curve domain parameters on a prime
     *  curve is defined as the number of bits in the binary representation
     *  of the field order, commonly denoted by p.  Size on a
     *  characteristic-2 curve is defined as the number of bits in the binary
     *  representation of the field, commonly denoted by m.  A set of
     *  elliptic curve domain parameters defines a group of order n generated
     *  by a base point P"
     *
     * @return int
     */
    public function getLength()
    {
        return $this->call('getLength');
    }

    /**
     * Returns the public key coordinates as a string
     *
     * Used by ECDH
     *
     * @return string
     */
    public function getEncodedCoordinates()
    {
        return $this->call('getEncodedCoordinates');
    }

    /**
     * Convert point to public key
     *
     * For Weierstrass curves, if only the x coordinate is present (as is the case after doing a round of ECDH)
     * then we'll guess at the y coordinate. There are only two possible y values and, atleast in-so-far as
     * multiplication is concerned, neither value affects the resultant x value
     *
     * If $toPublicKey is set to false then a string will be returned - a kind of public key precursor
     *
     * @param string $curveName
     * @param string $secret
     * @param bool $toPublicKey optional
     * @return PublicKey|string
     */
    public static function convertPointToPublicKey($curveName, $secret, $toPublicKey = true)
    {
        return self::callStatic('convertPointToPublicKey', $curveName, $secret, $toPublicKey);
    }

    /**
     * Returns the parameters
     *
     * @see self::getPublicKey()
     * @param string $type optional
     * @return mixed
     */
    public function getParameters($type = 'PKCS1')
    {
        return $this->call('getParameters', $type);
    }

    /**
     * Determines the signature padding mode
     *
     * Valid values are: ASN1, IEEE, SSH2, Raw
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

    /**
     * Sets the context
     *
     * Used by Ed25519 / Ed448.
     *
     * @see self::sign()
     * @see self::verify()
     * @param string $context optional
     */
    public function withContext($context = null)
    {
        return $this->call('withContext', $context);
    }

    /**
     * Returns the signature format currently being used
     *
     */
    public function getContext()
    {
        return $this->call('getContext');
    }
}
