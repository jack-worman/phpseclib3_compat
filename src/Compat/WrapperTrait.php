<?php

/**
 * Forwards calls from a phpseclib 3.0 object to the phpseclib 4.0 object it wraps
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Compat;

use phpseclib4\Exception\BaseException;

/**
 * Forwards calls from a phpseclib 3.0 object to the phpseclib 4.0 object it wraps
 *
 * @internal
 */
trait WrapperTrait
{
    /**
     * The phpseclib 4.0 object
     *
     * @var object
     */
    protected $v4;

    /**
     * Returns the phpseclib 4.0 object that this phpseclib 3.0 object uses
     *
     * Lets code that's being migrated to phpseclib 4.0 use phpseclib 4.0 objects
     *
     * @return object
     */
    public function getV4Object()
    {
        return $this->v4;
    }

    /**
     * Calls a method of the phpseclib 4.0 object
     *
     * Arguments are converted to phpseclib 4.0 values, the return value is converted to a phpseclib 3.0
     * value and phpseclib 4.0 exceptions are converted to the exceptions phpseclib 3.0 would have thrown
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    protected function call($method, ...$args)
    {
        try {
            return Convert::toV3($this->v4->$method(...Convert::toV4($args)));
        } catch (BaseException $e) {
            throw Convert::exception($e);
        }
    }

    /**
     * Clones the phpseclib 4.0 object along with this one
     */
    public function __clone()
    {
        $this->v4 = clone $this->v4;
    }
}
