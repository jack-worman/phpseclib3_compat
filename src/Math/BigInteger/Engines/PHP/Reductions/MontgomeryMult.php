<?php

/**
 * PHP Montgomery Modular Exponentiation Engine with interleaved multiplication
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\BigInteger\Engines\PHP\Reductions\MontgomeryMult
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\BigInteger\Engines\PHP\Reductions;

class_alias(\phpseclib4\Math\BigInteger\Engines\PHP\Reductions\MontgomeryMult::class, MontgomeryMult::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class MontgomeryMult extends \phpseclib4\Math\BigInteger\Engines\PHP\Reductions\MontgomeryMult
    {
    }
}
