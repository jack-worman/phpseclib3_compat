<?php

/**
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\CompatTests\Unit\Net;

use phpseclib3\Compat\Net\SSH2Engine;
use phpseclib3\Exception\ConnectionClosedException;
use phpseclib3\Net\SSH2;
use phpseclib4\Common\Functions\Strings;
use phpseclib4\Net\SSH2\DisconnectReason;
use phpseclib4\Net\SSH2\MessageType;
use PHPUnit\Framework\TestCase;

class SSH2Test extends TestCase
{
    use ScriptedServerTrait;

    public function testFsock()
    {
        $socket = fopen('php://memory', 'r+');
        $ssh = new SSH2($socket);

        $this->assertTrue(isset($ssh->fsock));
        $this->assertSame($socket, $ssh->fsock);

        $other = fopen('php://memory', 'r+');
        $ssh->fsock = $other;
        $this->assertSame($other, $ssh->getV4Object()->fsock);
    }

    public function testProtectedPropertiesAreStillProtected()
    {
        $ssh = new SSH2(fopen('php://memory', 'r+'));

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot access protected property');

        $ssh->errors;
    }

    public function testDisconnectRemovesTheConnection()
    {
        $ssh = new SSH2(fopen('php://memory', 'r+'));
        $id = $ssh->getResourceId();
        $this->assertSame($ssh, SSH2::getConnectionByResourceId($id));

        $ssh->disconnect();

        $this->assertFalse(SSH2::getConnectionByResourceId($id));
        $this->assertArrayNotHasKey($id, SSH2::getConnections());
    }

    /**
     * phpseclib 3.0's destructor disconnected, and subclasses' destructors could call it
     */
    public function testDestructor()
    {
        $ssh = new class (fopen('php://memory', 'r+')) extends SSH2 {
            public function __destruct()
            {
                parent::__destruct();
            }
        };
        $id = $ssh->getResourceId();

        $ssh->__destruct();

        $this->assertFalse(SSH2::getConnectionByResourceId($id));
    }

    /**
     * Mocks are created without calling the constructor
     */
    public function testDestructorWithoutConstructor()
    {
        $ssh = (new \ReflectionClass(SSH2::class))->newInstanceWithoutConstructor();

        $ssh->__destruct();

        $this->addToAssertionCount(1);
    }

    public function testProtectedPropertiesAreStillProtectedFromWrites()
    {
        $ssh = new SSH2(fopen('php://memory', 'r+'));

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot access protected property');

        $ssh->errors = [];
    }

    /**
     * phpseclib 3.0 accepted null timeouts, which mean no timeout
     */
    public function testNullTimeout()
    {
        set_error_handler(function ($errno, $errstr) {
            throw new \ErrorException($errstr, 0, $errno);
        });
        try {
            $ssh = new SSH2(fopen('php://memory', 'r+'), 22, null);
            $ssh->setTimeout(null);
        } finally {
            restore_error_handler();
        }

        $this->assertNull($ssh->getTimeout());
    }

    /**
     * Returns a logged in SSH2 object whose server opens the exec channel and then answers the next request on
     * it with the given packet
     *
     * @param string $payload
     * @return SSH2
     */
    private function ssh2WithAnswer($payload)
    {
        return $this->ssh2WithPackets([$this->openConfirmation(), $payload]);
    }

    /**
     * Returns the server's confirmation that the exec channel is open
     *
     * @return string
     */
    private function openConfirmation()
    {
        return pack('CNNNN', MessageType::CHANNEL_OPEN_CONFIRMATION, SSH2::CHANNEL_EXEC, 0, 0x7FFFFFFF, 0x4000);
    }

    /**
     * Returns a logged in SSH2 object whose server sends the given packets
     *
     * @param string[] $payloads
     * @return SSH2
     */
    private function ssh2WithPackets(array $payloads)
    {
        $client = $this->scriptedSocket($payloads);

        $ssh = new class ($client) extends SSH2 {
            protected function createV4($host, $port, $timeout)
            {
                return new class ($host) extends SSH2Engine {
                    public function __construct($socket)
                    {
                        parent::__construct($socket);
                        $this->bitmap = self::MASK_CONSTRUCTOR | self::MASK_CONNECTED | self::MASK_LOGIN;
                    }
                };
            }
        };
        $ssh->setTimeout(5);
        return $ssh;
    }

    /**
     * phpseclib 3.0 returned false and stayed connected when the server refused to run the command
     */
    public function testExecWhenTheServerRefuses()
    {
        $ssh = $this->ssh2WithAnswer(pack('CN', MessageType::CHANNEL_FAILURE, SSH2::CHANNEL_EXEC));

        $this->assertFalse($ssh->exec('ls'));
        $this->assertTrue($ssh->isAuthenticated());
    }

    public function testExecWithoutWaitingWhenTheServerRefuses()
    {
        $ssh = $this->ssh2WithAnswer(pack('CN', MessageType::CHANNEL_FAILURE, SSH2::CHANNEL_EXEC));

        $this->assertFalse($ssh->exec('ls', false));
        $this->assertTrue($ssh->isAuthenticated());
    }

    /**
     * Like exec() with a pseudo-terminal, exec() without waiting makes the channel the interactive one
     */
    public function testExecWithoutWaitingWithAPseudoTerminal()
    {
        $ssh = $this->ssh2WithPackets([
            $this->openConfirmation(),
            pack('CN', MessageType::CHANNEL_SUCCESS, SSH2::CHANNEL_EXEC),
            pack('CN', MessageType::CHANNEL_SUCCESS, SSH2::CHANNEL_EXEC),
        ]);
        $ssh->enablePTY();

        $this->assertTrue($ssh->exec('ls', false));
        $this->assertSame(SSH2::CHANNEL_EXEC, $ssh->getInteractiveChannelId());
    }

    /**
     * phpseclib 3.0's error for a command that was killed by a signal included the server's message
     */
    public function testExecKilledBySignal()
    {
        $ssh = $this->ssh2WithPackets([
            $this->openConfirmation(),
            pack('CN', MessageType::CHANNEL_SUCCESS, SSH2::CHANNEL_EXEC),
            Strings::packSSH2('CNs', MessageType::CHANNEL_DATA, SSH2::CHANNEL_EXEC, 'output'),
            Strings::packSSH2('CNsbsbss', MessageType::CHANNEL_REQUEST, SSH2::CHANNEL_EXEC, 'exit-signal', false, 'KILL', false, 'Killed', ''),
        ]);

        $this->assertSame('output', $ssh->exec('ls'));
        $this->assertSame("SSH_MSG_CHANNEL_REQUEST (exit-signal): KILL\r\nKilled", $ssh->getLastError());
    }

    /**
     * The errors are worded the way phpseclib 3.0 worded them
     */
    public function testErrorsFromTheServer()
    {
        $ssh = $this->ssh2WithPackets([
            Strings::packSSH2('Cbss', MessageType::DEBUG, true, 'hello', ''),
            Strings::packSSH2('Csb', MessageType::GLOBAL_REQUEST, 'keepalive@openssh.com', true),
            Strings::packSSH2('CNss', MessageType::DISCONNECT, DisconnectReason::BY_APPLICATION, 'bye', ''),
        ]);

        try {
            $ssh->exec('ls');
            $this->fail('exec() should have thrown an exception');
        } catch (ConnectionClosedException $e) {
        }
        $this->assertSame([
            'SSH_MSG_DEBUG: hello',
            'SSH_MSG_GLOBAL_REQUEST: keepalive@openssh.com',
            "SSH_MSG_DISCONNECT: NET_SSH2_DISCONNECT_BY_APPLICATION\r\nbye",
        ], $ssh->getErrors());
    }

    /**
     * phpseclib 3.0 disconnected and threw an exception when the server refused the pseudo-terminal
     */
    public function testExecWhenTheServerRefusesThePseudoTerminal()
    {
        $ssh = $this->ssh2WithAnswer(pack('CN', MessageType::CHANNEL_FAILURE, SSH2::CHANNEL_EXEC));
        $ssh->enablePTY();

        try {
            $ssh->exec('ls');
            $this->fail('exec() should have thrown an exception');
        } catch (\UnexpectedValueException $e) {
            $this->assertSame('Unable to request pseudo-terminal', $e->getMessage());
        }
        $this->assertFalse($ssh->isAuthenticated());
    }

    /**
     * An unexpected answer to the exec request is a protocol error, which still disconnects
     */
    public function testExecWhenTheServerAnswersUnexpectedly()
    {
        $ssh = $this->ssh2WithAnswer($this->openConfirmation());

        try {
            $ssh->exec('ls');
            $this->fail('exec() should have thrown an exception');
        } catch (\RuntimeException $e) {
        }
        $this->assertFalse($ssh->isAuthenticated());
    }
}
