<?php

/**
 * Finite Fields Base Class
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\Common\FiniteField
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\Common;

class_alias(\phpseclib4\Math\Common\FiniteField::class, FiniteField::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class FiniteField extends \phpseclib4\Math\Common\FiniteField
    {
    }
}
