<?php

/**
 * prime239v3
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\EC\Curves\prime239v3
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\Curves;

class_alias(\phpseclib4\Crypt\EC\Curves\prime239v3::class, prime239v3::class);

// lets IDEs and static analysers see the alias
if (false) {
    class prime239v3 extends \phpseclib4\Crypt\EC\Curves\prime239v3
    {
    }
}
