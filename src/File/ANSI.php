<?php

/**
 * Pure-PHP ANSI Decoder
 *
 * phpseclib 4.0's version works the same way, so this is an alias of \phpseclib4\File\ANSI
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\File;

class_alias(\phpseclib4\File\ANSI::class, ANSI::class);

// lets IDEs and static analysers see the alias
if (false) {
    class ANSI extends \phpseclib4\File\ANSI
    {
    }
}
