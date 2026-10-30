<?php

/**
 * Pure-PHP arbitrary precision integer arithmetic library.
 *
 * Supports base-2, base-10, base-16, and base-256 numbers.  Uses the GMP or BCMath extensions, if available,
 * and an internal implementation, otherwise.
 *
 * Here's an example of how to use this library:
 * <code>
 * <?php
 *    $a = new \phpseclib3\Math\BigInteger(2);
 *    $b = new \phpseclib3\Math\BigInteger(3);
 *
 *    $c = $a->add($b);
 *
 *    echo $c->toString(); // outputs 5
 * ?>
 * </code>
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2017 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math;

/**
 * Pure-PHP arbitrary precision integer arithmetic library. Supports base-2, base-10, base-16, and base-256
 * numbers.
 *
 * phpseclib 4.0's BigInteger creates its results with "new static", so extending it is enough for
 * every operation to return \phpseclib3\Math\BigInteger objects. Those objects can also be passed to
 * phpseclib 4.0 as-is.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class BigInteger extends \phpseclib4\Math\BigInteger
{
    /**
     * Converts base-2, base-10, base-16, and binary strings (base-256) to BigIntegers.
     *
     * If the second parameter - $base - is negative, then it will be assumed that the number's are encoded using
     * two's compliment.  The sole exception to this is -10, which is treated the same as 10 is.
     *
     * @param int|string|float|bool|null|\phpseclib4\Math\BigInteger\Engines\Engine $x Base-10 number or base-$base number if $base set.
     * @param int $base
     */
    public function __construct($x = 0, $base = 10)
    {
        if ($x === null || $x === false || $x === true) {
            $x = (int) $x;
        } elseif (is_float($x)) {
            $x = (string) $x;
        }

        parent::__construct($x, (int) $base);
    }

    /**
     * Calculates modular inverses.
     *
     * Returns 0 if no modular inverse exists, as phpseclib 3.0 did.
     */
    public function modInverse(\phpseclib4\Math\BigInteger $n): BigInteger
    {
        return parent::modInverse($n) ?? new static();
    }

    /**
     * Checks a numer to see if it's prime
     *
     * @param int|bool $t
     */
    public function isPrime($t = false): bool
    {
        return parent::isPrime($t === false ? null : (int) $t);
    }

    /**
     * Generate a random prime number between a range
     *
     * Returns 0 if no prime number exists in the range, as phpseclib 3.0 did.
     */
    public static function randomRangePrime(\phpseclib4\Math\BigInteger $min, \phpseclib4\Math\BigInteger $max): BigInteger
    {
        return parent::randomRangePrime($min, $max) ?? new static();
    }
}
