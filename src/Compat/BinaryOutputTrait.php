<?php

/**
 * phpseclib 3.0's way of toggling binary key output
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Compat;

/**
 * phpseclib 3.0's way of toggling binary key output
 *
 * phpseclib 4.0 replaced setBinaryOutput() with enableBinaryOutput() and disableBinaryOutput()
 *
 * @internal
 */
trait BinaryOutputTrait
{
    /**
     * Toggle between binary and printable keys
     *
     * @param bool $enabled
     */
    public static function setBinaryOutput($enabled)
    {
        if ($enabled) {
            static::enableBinaryOutput();
        } else {
            static::disableBinaryOutput();
        }
    }
}
