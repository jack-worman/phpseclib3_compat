<?php

/**
 * phpseclib 4.0's SFTP class, as used by phpseclib3\Net\SFTP
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Compat\Net;

use phpseclib4\Common\Functions\Strings;
use phpseclib4\Net\SFTP\Attribute;

/**
 * phpseclib 4.0's SFTP class, as used by phpseclib3\Net\SFTP
 *
 * @internal
 */
class SFTPEngine extends \phpseclib4\Net\SFTP
{
    use EngineTrait;

    /**
     * Sets the access and modification times of an existing file
     *
     * phpseclib 4.0's touch() fails when the file already exists, whereas phpseclib 3.0's set the times.
     * There's no public method for this so phpseclib 4.0's private setstat() is used, after checking that it's
     * still there. This can go once phpseclib 4.0's touch() works with existing files.
     *
     * @return bool false if setstat() isn't there any more
     */
    public function setTimes(string $filename, ?int $time = null, ?int $atime = null): bool
    {
        $class = \phpseclib4\Net\SFTP::class;
        if (!method_exists($class, 'setstat') || (new \ReflectionMethod($class, 'setstat'))->getNumberOfParameters() != 3) {
            return false;
        }

        $time = $time ?? time();
        $atime = $atime ?? $time;
        $attr = $this->getNegotiatedVersion() < 4 ?
            pack('N3', Attribute::ACCESSTIME, $atime, $time) :
            Strings::packSSH2('NQ2', Attribute::ACCESSTIME | Attribute::MODIFYTIME, $atime, $time);
        $filename = $this->realpath($filename);

        \Closure::bind(function () use ($filename, $attr) {
            $this->setstat($filename, $attr, false);
        }, $this, $class)();
        return true;
    }
}
