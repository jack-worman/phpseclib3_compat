<?php

/**
 * Raw DSA Key Handler
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\DSA\Formats\Keys\Raw
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\DSA\Formats\Keys;

class_alias(\phpseclib4\Crypt\DSA\Formats\Keys\Raw::class, Raw::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class Raw extends \phpseclib4\Crypt\DSA\Formats\Keys\Raw
    {
    }
}
