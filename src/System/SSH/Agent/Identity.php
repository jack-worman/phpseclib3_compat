<?php

/**
 * Pure-PHP ssh-agent client.
 *
 * {@internal See http://api.libssh.org/rfc/PROTOCOL.agent}
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2009 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\System\SSH\Agent;

use phpseclib3\Compat\Convert;
use phpseclib3\Compat\Wrapper;
use phpseclib3\Compat\WrapperTrait;
use phpseclib3\Crypt\Common\PrivateKey;

/**
 * Pure-PHP ssh-agent client identity object
 *
 * Instantiation should only be performed by \phpseclib3\System\SSH\Agent class.
 * This could be thought of as implementing an interface that phpseclib3\Crypt\RSA
 * implements. ie. maybe a Net_SSH_Auth_PublicKey interface or something.
 * The methods in this interface would be getPublicKey and sign since those are the
 * methods phpseclib looks for to perform public key authentication.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @internal
 */
class Identity implements PrivateKey, Wrapper
{
    use WrapperTrait;

    const SSH_AGENT_RSA2_256 = 2;

    const SSH_AGENT_RSA2_512 = 4;

    /**
     * Default Constructor.
     *
     * @param resource $fsock
     */
    public function __construct($fsock)
    {
        $this->v4 = new \phpseclib4\System\SSH\Agent\Identity($fsock);
    }

    /**
     * Set Public Key
     *
     * Called by \phpseclib3\System\SSH\Agent::requestIdentities()
     *
     * @param PublicKey $key
     */
    public function withPublicKey(\phpseclib3\Crypt\Common\PublicKey $key)
    {
        return $this->call('withPublicKey', $key);
    }

    /**
     * Set Public Key
     *
     * Called by \phpseclib3\System\SSH\Agent::requestIdentities(). The key blob could be extracted from $this->key
     * but this saves a small amount of computation.
     *
     * @param string $key_blob
     */
    public function withPublicKeyBlob($key_blob)
    {
        return $this->call('withPublicKeyBlob', $key_blob);
    }

    /**
     * Get Public Key
     *
     * Wrapper for $this->key->getPublicKey()
     *
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->call('getPublicKey');
    }

    /**
     * Sets the hash
     *
     * @param string $hash
     */
    public function withHash($hash)
    {
        return $this->call('withHash', $hash);
    }

    /**
     * Sets the padding
     *
     * Only PKCS1 padding is supported
     *
     * @param string $padding
     */
    public function withPadding($padding)
    {
        return $this->call('withPadding', Convert::rsaPaddingToV4($padding));
    }

    /**
     * Determines the signature padding mode
     *
     * Valid values are: ASN1, SSH2, Raw
     *
     * @param string $format
     */
    public function withSignatureFormat($format)
    {
        return $this->call('withSignatureFormat', $format);
    }

    /**
     * Returns the curve
     *
     * Returns a string if it's a named curve, an array if not
     *
     * @return string|array
     */
    public function getCurve()
    {
        return $this->call('getCurve');
    }

    /**
     * Create a signature
     *
     * See "2.6.2 Protocol 2 private key signature request"
     *
     * @param string $message
     * @return string
     * @throws \RuntimeException on connection errors
     * @throws UnsupportedAlgorithmException if the algorithm is unsupported
     */
    public function sign($message)
    {
        return $this->call('sign', $message);
    }

    /**
     * Returns the private key
     *
     * @param string $type
     * @param array $options optional
     * @return string
     */
    public function toString($type, array $options = [])
    {
        return $this->call('toString', $type, $options);
    }

    /**
     * Sets the password
     *
     * @param string|bool $password
     * @return never
     */
    public function withPassword($password = false)
    {
        return $this->call('withPassword', $password === false ? null : $password);
    }

    /**
     * Sets the comment
     */
    public function withComment($comment = null)
    {
        return $this->call('withComment', $comment);
    }

    /**
     * Returns the comment
     *
     * @return null|string
     */
    public function getComment()
    {
        return $this->call('getComment');
    }

    /**
     * Read data
     *
     * @param int $length
     * @throws \RuntimeException on connection errors
     */
    public function readBytes($length)
    {
        return $this->call('readBytes', $length);
    }
}
