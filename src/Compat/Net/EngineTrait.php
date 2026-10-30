<?php

/**
 * Access to the parts of phpseclib 4.0's SSH2 class that phpseclib 3.0's API needs
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Compat\Net;

use phpseclib4\Common\Functions\Strings;
use phpseclib4\Net\SSH2\DisconnectReason;
use phpseclib4\Net\SSH2\MessageType;

/**
 * Access to the parts of phpseclib 4.0's SSH2 class that phpseclib 3.0's API needs
 *
 * @internal
 */
trait EngineTrait
{
    /**
     * The type of the last SSH_MSG_CHANNEL_REQUEST that was sent, eg. "exec"
     *
     * @var string|null
     */
    private $lastChannelRequest = null;

    /**
     * Whether a command is being started
     *
     * @var bool
     */
    private $startingCommand = false;

    /**
     * How many get_channel_packet() calls are in progress
     *
     * @var int
     */
    private $readingChannelPackets = 0;

    /**
     * Runs a command without waiting for its output
     *
     * phpseclib 3.0 did this when exec()'s callback was false. The output is then read with read().
     */
    public function startExec(string $command): void
    {
        // with a pseudo-terminal, exec() doesn't wait for the output either, and it makes the channel the
        // interactive one, as phpseclib 3.0 did
        if ($this->isPTYEnabled()) {
            $this->exec($command);
            return;
        }
        $this->initExec($command);
    }

    /**
     * Starts a command
     */
    protected function initExec(string $command): void
    {
        $this->startingCommand = true;
        $this->lastChannelRequest = null;
        try {
            parent::initExec($command);
        } finally {
            $this->startingCommand = false;
        }
    }

    /**
     * Sends a packet, noting the type of the channel requests that were sent
     */
    protected function send_binary_packet(#[\SensitiveParameter] string $data, ?string $logged = null): void
    {
        parent::send_binary_packet($data, $logged);
        if (strlen($data) && ord($data[0]) == MessageType::CHANNEL_REQUEST) {
            [, , $this->lastChannelRequest] = Strings::unpackSSH2('CNs', $data);
        }
    }

    /**
     * Reads a channel packet, noting that it's being read
     */
    protected function get_channel_packet(int $client_channel, bool $skip_extended = false): string|bool
    {
        $this->readingChannelPackets++;
        try {
            return parent::get_channel_packet($client_channel, $skip_extended);
        } finally {
            $this->readingChannelPackets--;
        }
    }

    /**
     * Disconnects, except when the server refused to run a command
     *
     * phpseclib 4.0 disconnects when the server answers an exec request with a failure whereas phpseclib 3.0
     * kept the connection, which could still be used for SFTP, a shell, etc. Only that disconnect is skipped:
     * initExec() makes it after sending the exec request and reading the failure. The disconnects for errors
     * while sending the request or reading the answer happen inside send_binary_packet() and
     * get_channel_packet(), and a refused pseudo-terminal request disconnected in phpseclib 3.0, too.
     */
    protected function disconnect_helper(int $reason): bool
    {
        if (
            $this->startingCommand &&
            !$this->readingChannelPackets &&
            $reason == DisconnectReason::BY_APPLICATION &&
            $this->lastChannelRequest === 'exec'
        ) {
            return false;
        }
        return parent::disconnect_helper($reason);
    }
}
