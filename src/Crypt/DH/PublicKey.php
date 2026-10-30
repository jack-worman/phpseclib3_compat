<?php

/**
 * DH Public Key
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2015 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt\DH;

use phpseclib3\Crypt\DH;

/**
 * DH Public Key
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
final class PublicKey extends DH
{
    /**
     * Returns the public key as a BigInteger
     *
     * @return \phpseclib3\Math\BigInteger
     */
    public function toBigInteger()
    {
        return $this->call('toBigInteger');
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
}
