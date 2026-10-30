<?php

/**
 * IEEE P1363 Signature Handler
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\EC\Formats\Signature\IEEE
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\Formats\Signature;

class_alias(\phpseclib4\Crypt\EC\Formats\Signature\IEEE::class, IEEE::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class IEEE extends \phpseclib4\Crypt\EC\Formats\Signature\IEEE
    {
    }
}
