<?php

/**
 * PuTTY Formatted RSA Key Handler
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\RSA\Formats\Keys\PuTTY
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\RSA\Formats\Keys;

class_alias(\phpseclib4\Crypt\RSA\Formats\Keys\PuTTY::class, PuTTY::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class PuTTY extends \phpseclib4\Crypt\RSA\Formats\Keys\PuTTY
    {
    }
}
