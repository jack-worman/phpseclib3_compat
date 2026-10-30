<?php

/**
 * ASN1 Signature Handler
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\DSA\Formats\Signature\ASN1
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\DSA\Formats\Signature;

class_alias(\phpseclib4\Crypt\DSA\Formats\Signature\ASN1::class, ASN1::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class ASN1 extends \phpseclib4\Crypt\DSA\Formats\Signature\ASN1
    {
    }
}
