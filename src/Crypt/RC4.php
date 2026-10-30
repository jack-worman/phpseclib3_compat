<?php

/**
 * Pure-PHP implementation of RC4.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * Useful resources are as follows:
 *
 *  - {@link http://www.mozilla.org/projects/security/pki/nss/draft-kaukonen-cipher-arcfour-03.txt ARCFOUR Algorithm}
 *  - {@link http://en.wikipedia.org/wiki/RC4 - Wikipedia: RC4}
 *
 * RC4 is also known as ARCFOUR or ARC4.  The reason is elaborated upon at Wikipedia.  This class is named RC4 and not
 * ARCFOUR or ARC4 because RC4 is how it is referred to in the SSH1 specification.
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $rc4 = new \phpseclib3\Crypt\RC4();
 *
 *    $rc4->setKey('abcdefgh');
 *
 *    $size = 10 * 1024;
 *    $plaintext = '';
 *    for ($i = 0; $i < $size; $i++) {
 *        $plaintext.= 'a';
 *    }
 *
 *    echo $rc4->decrypt($rc4->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt;

use phpseclib3\Crypt\Common\StreamCipher;

/**
 * Pure-PHP implementation of RC4.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class RC4 extends StreamCipher
{
    /**
     * @see RC4::_crypt()
     */
    const ENCRYPT = 0;

    /**
     * @see RC4::_crypt()
     */
    const DECRYPT = 1;
}
