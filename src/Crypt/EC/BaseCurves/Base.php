<?php

/**
 * Curve methods common to all curves
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\EC\BaseCurves\Base
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\BaseCurves;

class_alias(\phpseclib4\Crypt\EC\BaseCurves\Base::class, Base::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class Base extends \phpseclib4\Crypt\EC\BaseCurves\Base
    {
    }
}
