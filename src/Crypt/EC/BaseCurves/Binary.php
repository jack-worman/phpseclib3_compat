<?php

/**
 * Curves over y^2 + x*y = x^3 + a*x^2 + b
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\EC\BaseCurves\Binary
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\BaseCurves;

class_alias(\phpseclib4\Crypt\EC\BaseCurves\Binary::class, Binary::class);

// lets IDEs and static analysers see the alias
if (false) {
    class Binary extends \phpseclib4\Crypt\EC\BaseCurves\Binary
    {
    }
}
