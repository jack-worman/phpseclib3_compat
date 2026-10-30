<?php

/**
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\CompatTests\Functional\Net;

use phpseclib3\Compat\Net\SFTPEngine;
use phpseclib4\Crypt\Common\PrivateKey;
use phpseclib4\Net\SSH2\MessageType;
use phpseclib4\System\SSH\Agent;

/**
 * Talks to OpenSSH's sftp-server over pipes instead of over SSH
 *
 * Lets the SFTP tests run without an SSH server
 */
class PipeSFTPEngine extends SFTPEngine
{
    /**
     * Whether the SFTP subsystem request should be refused
     *
     * @var bool
     */
    public $refuseSubsystem = false;

    /**
     * Whether opening the SFTP channel should time out
     *
     * @var bool
     */
    public $timeOutOnOpen = false;

    /**
     * The SFTP version to claim the server supports instead of the version it does support
     *
     * @var int|null
     */
    public $sftpVersion = null;

    /** @var resource */
    private $process;
    /** @var resource */
    private $stdin;
    /** @var resource */
    private $stdout;

    public function __construct(string $server, string $home)
    {
        parent::__construct(fopen('php://memory', 'r+'));
        $this->server_identifier = 'SSH-2.0-OpenSSH_9.9';
        $this->process = proc_open(
            [$server],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
            $home
        );
        [$this->stdin, $this->stdout] = $pipes;
    }

    public function __destruct()
    {
        parent::__destruct();
        fclose($this->stdin);
        fclose($this->stdout);
        proc_close($this->process);
    }

    public function login(string $username, string|PrivateKey|array|Agent ...$args): bool
    {
        $this->bitmap = self::MASK_CONSTRUCTOR | self::MASK_CONNECTED | self::MASK_LOGIN;
        return true;
    }

    protected function open_channel(int $channel, bool $skip_extended = false): bool
    {
        $this->server_channels[$channel] = $channel;
        $this->channel_status[$channel] = MessageType::CHANNEL_OPEN;
        $this->is_timeout = $this->timeOutOnOpen;
        return true;
    }

    protected function send_binary_packet(string $data, ?string $logged = null): void
    {
    }

    protected function send_channel_packet(int $client_channel, string $data): void
    {
        fwrite($this->stdin, $data);
    }

    protected function get_channel_packet(int $client_channel, bool $skip_extended = false): string|bool
    {
        switch ($this->channel_status[$client_channel]) {
            case MessageType::CHANNEL_OPEN:
                return true;
            case MessageType::CHANNEL_REQUEST:
                return !$this->refuseSubsystem;
        }
        $read = [$this->stdout];
        $write = $except = null;
        if (!stream_select($read, $write, $except, 10)) {
            $this->is_timeout = true;
            return true;
        }
        $data = fread($this->stdout, 65536);
        if (isset($this->sftpVersion)) {
            // the first reply is SSH_FXP_VERSION: a uint32 length, a byte for the type and then the version
            $data = substr_replace($data, pack('N', $this->sftpVersion), 5, 4);
            $this->sftpVersion = null;
        }
        return $data;
    }
}
