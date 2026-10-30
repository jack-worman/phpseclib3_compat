<?php

/**
 * PHP Dynamic Barrett Modular Exponentiation Engine
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\BigInteger\Engines\PHP\Reductions\EvalBarrett
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\BigInteger\Engines\PHP\Reductions;

class_alias(\phpseclib4\Math\BigInteger\Engines\PHP\Reductions\EvalBarrett::class, EvalBarrett::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class EvalBarrett extends \phpseclib4\Math\BigInteger\Engines\PHP\Reductions\EvalBarrett
    {
    }
}
