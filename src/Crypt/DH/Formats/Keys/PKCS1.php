<?php

/**
 * "PKCS1" Formatted EC Key Handler
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\DH\Formats\Keys\PKCS1
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\DH\Formats\Keys;

class_alias(\phpseclib4\Crypt\DH\Formats\Keys\PKCS1::class, PKCS1::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class PKCS1 extends \phpseclib4\Crypt\DH\Formats\Keys\PKCS1
    {
    }
}
