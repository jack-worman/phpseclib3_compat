<?php

/**
 * PKCS Formatted Key Handler
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\Common\Formats\Keys\PKCS
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\Common\Formats\Keys;

class_alias(\phpseclib4\Crypt\Common\Formats\Keys\PKCS::class, PKCS::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class PKCS extends \phpseclib4\Crypt\Common\Formats\Keys\PKCS
    {
    }
}
