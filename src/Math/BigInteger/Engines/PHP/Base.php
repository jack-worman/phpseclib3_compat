<?php

/**
 * PHP Modular Exponentiation Engine
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\BigInteger\Engines\PHP\Base
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\BigInteger\Engines\PHP;

class_alias(\phpseclib4\Math\BigInteger\Engines\PHP\Base::class, Base::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class Base extends \phpseclib4\Math\BigInteger\Engines\PHP\Base
    {
    }
}
