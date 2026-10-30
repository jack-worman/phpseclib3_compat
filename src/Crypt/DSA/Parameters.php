<?php

/**
 * DSA Parameters
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2015 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt\DSA;

use phpseclib3\Crypt\DSA;

/**
 * DSA Parameters
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
final class Parameters extends DSA
{
    /**
     * Returns the parameters
     *
     * @param string $type
     * @param array $options optional
     * @return string
     */
    public function toString($type = 'PKCS1', array $options = [])
    {
        return $this->call('toString', $type, $options);
    }
}
