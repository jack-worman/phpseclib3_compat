<?php

/**
 * PKCS#8 Formatted RSA-PSS Key Handler
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\RSA\Formats\Keys\PSS
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\RSA\Formats\Keys;

class_alias(\phpseclib4\Crypt\RSA\Formats\Keys\PSS::class, PSS::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class PSS extends \phpseclib4\Crypt\RSA\Formats\Keys\PSS
    {
    }
}
