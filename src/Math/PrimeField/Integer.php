<?php

/**
 * Prime Finite Fields
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\PrimeField\Integer
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\PrimeField;

class_alias(\phpseclib4\Math\PrimeField\Integer::class, Integer::class);

// lets IDEs and static analysers see the alias
if (false) {
    class Integer extends \phpseclib4\Math\PrimeField\Integer
    {
    }
}
