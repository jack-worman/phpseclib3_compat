<?php

/**
 * Curves over a*x^2 + y^2 = 1 + d*x^2*y^2
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\EC\BaseCurves\TwistedEdwards
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\BaseCurves;

class_alias(\phpseclib4\Crypt\EC\BaseCurves\TwistedEdwards::class, TwistedEdwards::class);

// lets IDEs and static analysers see the alias
if (false) {
    class TwistedEdwards extends \phpseclib4\Crypt\EC\BaseCurves\TwistedEdwards
    {
    }
}
