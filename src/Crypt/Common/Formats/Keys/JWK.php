<?php

/**
 * JSON Web Key (RFC7517) Handler
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\Common\Formats\Keys\JWK
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\Common\Formats\Keys;

class_alias(\phpseclib4\Crypt\Common\Formats\Keys\JWK::class, JWK::class);

// lets IDEs and static analysers see the alias
if (false) {
    abstract class JWK extends \phpseclib4\Crypt\Common\Formats\Keys\JWK
    {
    }
}
