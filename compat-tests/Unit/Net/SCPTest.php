<?php

/**
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\CompatTests\Unit\Net;

use phpseclib3\Compat\Net\SCPEngine;
use phpseclib3\Exception\ConnectionClosedException;
use phpseclib3\Net\SCP;
use phpseclib3\Net\SSH2;
use phpseclib4\Net\SSH2\MessageType;
use PHPUnit\Framework\TestCase;

class SCPTest extends TestCase
{
    use ScriptedServerTrait;

    /**
     * Returns a logged in SCP object whose server opens the exec channel and then sends the given packets
     *
     * @param string ...$payloads
     * @return SCP
     */
    private function scpWithAnswer(...$payloads)
    {
        $client = $this->scriptedSocket(array_merge(
            [pack('CNNNN', MessageType::CHANNEL_OPEN_CONFIRMATION, SSH2::CHANNEL_EXEC, 0, 0x7FFFFFFF, 0x4000)],
            $payloads
        ));

        $scp = new class ($client) extends SCP {
            protected function createV4($host, $port, $timeout)
            {
                return new class ($host) extends SCPEngine {
                    public function __construct($socket)
                    {
                        parent::__construct($socket);
                        $this->bitmap = self::MASK_CONSTRUCTOR | self::MASK_CONNECTED | self::MASK_LOGIN;
                    }
                };
            }
        };
        $scp->setTimeout(5);
        return $scp;
    }

    /**
     * Like phpseclib 3.0, a server that won't run scp is a failed upload, not an exception
     */
    public function testRefusedCommandReturnsFalse()
    {
        $scp = $this->scpWithAnswer(pack('CN', MessageType::CHANNEL_FAILURE, SSH2::CHANNEL_EXEC));

        $this->assertFalse($scp->put('file.txt', 'data'));
        $this->assertSame('Unable to execute command', $scp->getLastSCPError());
    }

    /**
     * Like phpseclib 3.0, a connection that's closed is an exception
     */
    public function testClosedConnectionThrows()
    {
        // SSH_MSG_DISCONNECT with SSH_DISCONNECT_BY_APPLICATION
        $scp = $this->scpWithAnswer(pack('CNNa3N', MessageType::DISCONNECT, 11, 3, 'bye', 0));

        $this->expectException(ConnectionClosedException::class);

        $scp->put('file.txt', 'data');
    }

    /**
     * Like phpseclib 3.0, errors that phpseclib disconnects for are exceptions
     */
    public function testUnexpectedMessageThrows()
    {
        $scp = $this->scpWithAnswer(
            pack('CN', MessageType::CHANNEL_SUCCESS, SSH2::CHANNEL_EXEC),
            // not a valid answer once the command is running
            pack('CN', MessageType::CHANNEL_FAILURE, SSH2::CHANNEL_EXEC)
        );

        try {
            $scp->put('file.txt', 'data');
            $this->fail('put() should have thrown an exception');
        } catch (\RuntimeException $e) {
            $this->assertStringStartsWith('Error reading channel data', $e->getMessage());
        }
        $this->assertFalse($scp->isConnected());
    }

    public function testCallbacksThatArentCallableAreIgnored()
    {
        $scp = $this->scpWithAnswer(pack('CN', MessageType::CHANNEL_FAILURE, SSH2::CHANNEL_EXEC));

        // phpseclib 3.0 ignored the callback rather than throwing a TypeError
        $this->assertFalse($scp->put('file.txt', 'data', SCP::SOURCE_STRING, false));
    }
}
