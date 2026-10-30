<?php

/**
 * PKCS#8 Formatted Key Handler
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Crypt\Common\Formats\Keys;

use phpseclib3\Compat\BinaryOutputTrait;

/**
 * PKCS#8 Formatted Key Handler
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class PKCS8 extends \phpseclib4\Crypt\Common\Formats\Keys\PKCS8
{
    use BinaryOutputTrait;
}
