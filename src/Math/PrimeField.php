<?php

/**
 * Prime Finite Fields
 *
 * Utilizes the factory design pattern
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2017 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math;

use phpseclib3\Compat\Convert;
use phpseclib4\Exception\InvalidArgumentException;

/**
 * Prime Finite Fields
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class PrimeField extends \phpseclib4\Math\PrimeField
{
    /**
     * Default constructor
     */
    public function __construct(\phpseclib4\Math\BigInteger $modulo)
    {
        try {
            parent::__construct($modulo);
        } catch (InvalidArgumentException $e) {
            throw Convert::exception($e, [InvalidArgumentException::class => \UnexpectedValueException::class]);
        }
    }
}
