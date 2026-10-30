<?php

/**
 * Binary Finite Fields
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Math\BinaryField\Integer
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Math\BinaryField;

class_alias(\phpseclib4\Math\BinaryField\Integer::class, Integer::class);

// lets IDEs and static analysers see the alias
if (false) {
    class Integer extends \phpseclib4\Math\BinaryField\Integer
    {
    }
}
