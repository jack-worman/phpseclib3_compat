<?php

/**
 * Montgomery Private Key Handler
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\EC\Formats\Keys\MontgomeryPrivate
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\Formats\Keys;

class_alias(\phpseclib4\Crypt\EC\Formats\Keys\MontgomeryPrivate::class, MontgomeryPrivate::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class MontgomeryPrivate extends \phpseclib4\Crypt\EC\Formats\Keys\MontgomeryPrivate
    {
    }
}
