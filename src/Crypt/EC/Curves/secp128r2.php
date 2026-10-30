<?php

/**
 * secp128r2
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\EC\Curves\secp128r2
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\Curves;

class_alias(\phpseclib4\Crypt\EC\Curves\secp128r2::class, secp128r2::class);

// lets IDEs and static analysers see the alias
if (false) {
    class secp128r2 extends \phpseclib4\Crypt\EC\Curves\secp128r2
    {
    }
}
