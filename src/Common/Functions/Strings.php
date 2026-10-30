<?php

/**
 * Common String Functions
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Common\Functions\Strings
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Common\Functions;

class_alias(\phpseclib4\Common\Functions\Strings::class, Strings::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class Strings extends \phpseclib4\Common\Functions\Strings
    {
    }
}
