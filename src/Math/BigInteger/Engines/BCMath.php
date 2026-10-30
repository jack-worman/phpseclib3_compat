<?php

/**
 * BCMath BigInteger Engine
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\BigInteger\Engines\BCMath
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\BigInteger\Engines;

class_alias(\phpseclib4\Math\BigInteger\Engines\BCMath::class, BCMath::class);

// lets IDEs and static analysers see the alias
if (false) {
    class BCMath extends \phpseclib4\Math\BigInteger\Engines\BCMath
    {
    }
}
