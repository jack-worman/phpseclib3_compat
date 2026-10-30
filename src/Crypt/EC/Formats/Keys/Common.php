<?php

/**
 * Generic EC Key Parsing Helper functions
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\EC\Formats\Keys\Common
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\Formats\Keys;

class_alias(\phpseclib4\Crypt\EC\Formats\Keys\Common::class, Common::class);

// lets IDEs and static analysers see the alias
if (false) {
    trait Common
    {
        use \phpseclib4\Crypt\EC\Formats\Keys\Common;
    }
}
