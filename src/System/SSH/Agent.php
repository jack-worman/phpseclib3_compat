<?php

/**
 * Pure-PHP ssh-agent client.
 *
 * {@internal See http://api.libssh.org/rfc/PROTOCOL.agent}
 *
 * Here are some examples of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $agent = new \phpseclib3\System\SSH\Agent();
 *
 *    $ssh = new \phpseclib3\Net\SSH2('www.domain.tld');
 *    if (!$ssh->login('username', $agent)) {
 *        exit('Login Failed');
 *    }
 *
 *    echo $ssh->exec('pwd');
 *    echo $ssh->exec('ls -la');
 * ?>
 * </code>
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2014 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\System\SSH;

use phpseclib3\Compat\Convert;
use phpseclib3\Compat\Wrapper;
use phpseclib3\Compat\WrapperTrait;
use phpseclib4\Exception\BaseException;

/**
 * Pure-PHP ssh-agent client identity factory
 *
 * requestIdentities() method pumps out \phpseclib3\System\SSH\Agent\Identity objects
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class Agent implements Wrapper
{
    use WrapperTrait;

    const SSH_AGENTC_REQUEST_IDENTITIES = 11;

    const SSH_AGENT_IDENTITIES_ANSWER = 12;

    const SSH_AGENTC_SIGN_REQUEST = 13;

    const SSH_AGENT_SIGN_RESPONSE = 14;

    const FORWARD_NONE = 0;

    const FORWARD_REQUEST = 1;

    const FORWARD_ACTIVE = 2;

    /**
     * Unused
     */
    const SSH_AGENT_FAILURE = 5;

    /**
     * Default Constructor
     *
     * @return Agent
     * @throws BadConfigurationException if SSH_AUTH_SOCK cannot be found
     * @throws \RuntimeException on connection errors
     */
    public function __construct($address = null)
    {
        try {
            $this->v4 = new \phpseclib4\System\SSH\Agent($address);
        } catch (BaseException $e) {
            throw Convert::exception($e);
        }
    }

    /**
     * Request Identities
     *
     * See "2.5.2 Requesting a list of protocol 2 keys"
     * Returns an array containing zero or more \phpseclib3\System\SSH\Agent\Identity objects
     *
     * @return array
     * @throws \RuntimeException on receipt of unexpected packets
     */
    public function requestIdentities()
    {
        return $this->call('requestIdentities');
    }

    /**
     * Returns the SSH Agent identity matching a given public key or null if no identity is found
     *
     * @return ?Identity
     */
    public function findIdentityByPublicKey(\phpseclib3\Crypt\Common\PublicKey $key)
    {
        return $this->call('findIdentityByPublicKey', $key);
    }

    /**
     * Signal that agent forwarding should
     * be requested when a channel is opened
     *
     * @return void
     */
    public function startSSHForwarding()
    {
        $this->call('startSSHForwarding');
    }

    /**
     * On successful channel open
     *
     * This method is called upon successful channel
     * open to give the SSH Agent an opportunity
     * to take further action. i.e. request agent forwarding
     *
     * @param SSH2 $ssh
     */
    public function registerChannelOpen(\phpseclib3\Net\SSH2 $ssh)
    {
        $this->call('registerChannelOpen', $ssh);
    }

    /**
     * Forward data to SSH Agent and return data reply
     *
     * @param string $data
     * @return string Data from SSH Agent
     * @throws \RuntimeException on connection errors
     */
    public function forwardData($data)
    {
        return $this->call('forwardData', $data);
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
