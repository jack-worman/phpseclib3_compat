<?php

/**
 * Binary Finite Fields
 *
 * In a binary finite field numbers are actually polynomial equations. If you
 * represent the number as a sequence of bits you get a sequence of 1's or 0's.
 * These 1's or 0's represent the coefficients of the x**n, where n is the
 * location of the given bit. When you add numbers over a binary finite field
 * the result should have a coefficient of 1 or 0 as well. Hence addition
 * and subtraction become the same operation as XOR.
 * eg. 1 + 1 + 1 == 3 % 2 == 1 or 0 - 1 == -1 % 2 == 1
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2017 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math;

use phpseclib3\Compat\Convert;
use phpseclib4\Exception\BaseException;
use phpseclib4\Exception\UnsupportedValueException;

/**
 * Binary Finite Fields
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class BinaryField extends \phpseclib4\Math\BinaryField
{
    /**
     * Default constructor
     *
     * @param int ...$indices
     */
    public function __construct(...$indices)
    {
        try {
            parent::__construct(...$indices);
        } catch (BaseException $e) {
            throw Convert::exception($e, [UnsupportedValueException::class => \InvalidArgumentException::class]);
        }
    }
}
