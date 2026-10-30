<?php

/**
 * BCMath Barrett Modular Exponentiation Engine
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\BigInteger\Engines\BCMath\Reductions\Barrett
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\BigInteger\Engines\BCMath\Reductions;

class_alias(\phpseclib4\Math\BigInteger\Engines\BCMath\Reductions\Barrett::class, Barrett::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class Barrett extends \phpseclib4\Math\BigInteger\Engines\BCMath\Reductions\Barrett
    {
    }
}
