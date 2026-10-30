<?php

/**
 * Pure-PHP implementation of Salsa20.
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2019 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt;

use phpseclib3\Crypt\Common\StreamCipher;

/**
 * Pure-PHP implementation of Salsa20.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class Salsa20 extends StreamCipher
{
    /**
     * @see Salsa20::crypt()
     */
    const ENCRYPT = 0;

    /**
     * @see Salsa20::crypt()
     */
    const DECRYPT = 1;

    /**
     * Sets the counter.
     *
     * @param int $counter
     */
    public function setCounter($counter)
    {
        $this->call('setCounter', $counter);
    }
}
