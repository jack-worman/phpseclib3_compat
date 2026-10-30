<?php

/**
 * Generalized Koblitz Curves over y^2 = x^3 + b
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\EC\BaseCurves\KoblitzPrime
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\BaseCurves;

class_alias(\phpseclib4\Crypt\EC\BaseCurves\KoblitzPrime::class, KoblitzPrime::class);

// lets IDEs and static analysers see the alias
if (false) {
    class KoblitzPrime extends \phpseclib4\Crypt\EC\BaseCurves\KoblitzPrime
    {
    }
}
