<?php

/**
 * Password Protected Trait for Private Keys
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\Crypt\Common\Traits\PasswordProtected
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\Common\Traits;

class_alias(\phpseclib4\Crypt\Common\Traits\PasswordProtected::class, PasswordProtected::class);

// lets IDEs and static analysers see the alias
if (false) {
    trait PasswordProtected
    {
        use \phpseclib4\Crypt\Common\Traits\PasswordProtected;
    }
}
