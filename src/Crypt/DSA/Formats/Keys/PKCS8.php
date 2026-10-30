<?php

/**
 * PKCS#8 Formatted DSA Key Handler
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2015 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\DSA\Formats\Keys;

use phpseclib3\Compat\BinaryOutputTrait;
use phpseclib3\Compat\PKCS8Trait;

/**
 * PKCS#8 Formatted DSA Key Handler
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class PKCS8 extends \phpseclib4\Crypt\DSA\Formats\Keys\PKCS8
{
    use BinaryOutputTrait;
    use PKCS8Trait;
}
