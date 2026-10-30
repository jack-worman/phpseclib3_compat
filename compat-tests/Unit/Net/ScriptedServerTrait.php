<?php

/**
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\CompatTests\Unit\Net;

/**
 * Connections to a "server" that sends packets that the test scripted
 *
 * phpseclib 4.0 reads the packets the way it would read them from a real server
 */
trait ScriptedServerTrait
{
    /**
     * The server end of the connections the test created
     *
     * @var resource[]
     */
    private $servers = [];

    protected function tearDown(): void
    {
        foreach ($this->servers as $server) {
            fclose($server);
        }
        $this->servers = [];
    }

    /**
     * Returns the client end of a connection whose server sends the given payloads
     *
     * @param string[] $payloads
     * @return resource
     */
    private function scriptedSocket(array $payloads)
    {
        // Windows only has TCP socket pairs
        $domain = PHP_OS_FAMILY == 'Windows' ? STREAM_PF_INET : STREAM_PF_UNIX;
        [$client, $server] = stream_socket_pair($domain, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $this->servers[] = $server;
        foreach ($payloads as $payload) {
            // an unencrypted binary packet: packet_length, padding_length, payload and padding
            $padding = 8 - (5 + strlen($payload)) % 8;
            if ($padding < 4) {
                $padding += 8;
            }
            fwrite($server, pack('NC', 1 + strlen($payload) + $padding, $padding) . $payload . str_repeat("\0", $padding));
        }
        return $client;
    }
}
