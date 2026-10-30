<?php

/**
 * Pure-PHP 32-bit BigInteger Engine
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\BigInteger\Engines\PHP32
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\BigInteger\Engines;

class_alias(\phpseclib4\Math\BigInteger\Engines\PHP32::class, PHP32::class);

// lets IDEs and static analysers see the alias
if (false) {
    class PHP32 extends \phpseclib4\Math\BigInteger\Engines\PHP32
    {
    }
}
