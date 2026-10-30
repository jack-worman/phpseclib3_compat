<?php

/**
 * Finite Field Integer Base Class
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\Common\FiniteField\Integer
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\Common\FiniteField;

class_alias(\phpseclib4\Math\Common\FiniteField\Integer::class, Integer::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class Integer extends \phpseclib4\Math\Common\FiniteField\Integer
    {
    }
}
