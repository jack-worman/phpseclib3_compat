<?php

/**
 * OpenSSH Formatted EC Key Handler
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\EC\Formats\Keys;

use phpseclib3\Compat\BinaryOutputTrait;

/**
 * OpenSSH Formatted EC Key Handler
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class OpenSSH extends \phpseclib4\Crypt\EC\Formats\Keys\OpenSSH
{
    use BinaryOutputTrait;
}
