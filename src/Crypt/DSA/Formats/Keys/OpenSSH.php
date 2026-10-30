<?php

/**
 * OpenSSH Formatted DSA Key Handler
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\DSA\Formats\Keys;

use phpseclib3\Compat\BinaryOutputTrait;

/**
 * OpenSSH Formatted DSA Key Handler
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class OpenSSH extends \phpseclib4\Crypt\DSA\Formats\Keys\OpenSSH
{
    use BinaryOutputTrait;
}
