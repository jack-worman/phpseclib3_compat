<?php

/**
 * phpseclib 3.0 object that's backed by a phpseclib 4.0 object
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Compat;

/**
 * phpseclib 3.0 object that's backed by a phpseclib 4.0 object
 */
interface Wrapper
{
    /**
     * Returns the phpseclib 4.0 object that this phpseclib 3.0 object uses
     *
     * Lets code that's being migrated to phpseclib 4.0 use phpseclib 4.0 objects
     *
     * @return object
     */
    public function getV4Object();
}
