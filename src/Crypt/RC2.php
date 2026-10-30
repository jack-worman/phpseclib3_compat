<?php

/**
 * Pure-PHP implementation of RC2.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * Useful resources are as follows:
 *
 *  - {@link http://tools.ietf.org/html/rfc2268}
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $rc2 = new \phpseclib3\Crypt\RC2('ctr');
 *
 *    $rc2->setKey('abcdefgh');
 *
 *    $plaintext = str_repeat('a', 1024);
 *
 *    echo $rc2->decrypt($rc2->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @author   Patrick Monnerat <pm@datasphere.ch>
 * @license  http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link     http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt;

use phpseclib3\Crypt\Common\BlockCipher;

/**
 * Pure-PHP implementation of RC2.
 *
 */
class RC2 extends BlockCipher
{
    /**
     * Sets the key.
     *
     * Keys can be of any length. RC2, itself, uses 8 to 1024 bit keys (eg.
     * strlen($key) <= 128), however, we only use the first 128 bytes if $key
     * has more then 128 bytes in it, and set $key to a single null byte if
     * it is empty.
     *
     * @see Common\SymmetricKey::setKey()
     * @param string $key
     * @param int|boolean $t1 optional Effective key length in bits.
     * @throws \LengthException if the key length isn't supported
     */
    public function setKey($key, $t1 = false)
    {
        $this->call('setKey', $key, $t1 === false ? null : (int) $t1);
    }
}
