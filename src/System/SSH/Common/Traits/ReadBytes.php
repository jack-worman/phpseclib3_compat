<?php

/**
 * ReadBytes trait
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\System\SSH\Common\Traits\ReadBytes
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\System\SSH\Common\Traits;

class_alias(\phpseclib4\System\SSH\Common\Traits\ReadBytes::class, ReadBytes::class);

// lets IDEs and static analysers see the alias
if (false) {
    trait ReadBytes
    {
        use \phpseclib4\System\SSH\Common\Traits\ReadBytes;
    }
}
