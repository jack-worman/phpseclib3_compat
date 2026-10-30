<?php

/**
 * Curve25519
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\EC\Curves\Curve25519
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\Curves;

class_alias(\phpseclib4\Crypt\EC\Curves\Curve25519::class, Curve25519::class);

// lets IDEs and static analysers see the alias
if (false) {
    class Curve25519 extends \phpseclib4\Crypt\EC\Curves\Curve25519
    {
    }
}
