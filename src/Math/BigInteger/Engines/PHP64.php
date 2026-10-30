<?php

/**
 * Pure-PHP 64-bit BigInteger Engine
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\BigInteger\Engines\PHP64
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\BigInteger\Engines;

class_alias(\phpseclib4\Math\BigInteger\Engines\PHP64::class, PHP64::class);

// lets IDEs and static analysers see the alias
if (false) {
    class PHP64 extends \phpseclib4\Math\BigInteger\Engines\PHP64
    {
    }
}
