<?php

/**
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\CompatTests\Functional\Net;

use phpseclib3\Exception\FileNotFoundException;
use phpseclib3\Net\SFTP;
use PHPUnit\Framework\TestCase;

/**
 * Tests the places where phpseclib 4.0 throws exceptions and phpseclib 3.0 didn't
 *
 * Runs against OpenSSH's sftp-server, over pipes
 */
class SFTPTest extends TestCase
{
    /** @var string */
    private $server;

    /** @var string */
    private $home;

    protected function setUp(): void
    {
        foreach (['/usr/libexec/sftp-server', '/usr/lib/openssh/sftp-server', '/usr/lib/sftp-server'] as $path) {
            if (is_executable($path)) {
                $this->server = $path;
                break;
            }
        }
        if (!isset($this->server)) {
            $this->markTestSkipped("OpenSSH's sftp-server wasn't found");
        }

        $this->home = sys_get_temp_dir() . '/phpseclib3_compat-' . bin2hex(random_bytes(8));
        mkdir($this->home);
        $this->home = realpath($this->home);
    }

    protected function tearDown(): void
    {
        if (!isset($this->home)) {
            return;
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->home, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->home);
    }

    /**
     * Returns an SFTP object that talks to sftp-server, without logging in
     *
     * @return SFTP
     */
    private function newSFTP()
    {
        return new class ($this->server, $this->home) extends SFTP {
            private $server;
            private $home;

            public function __construct($server, $home)
            {
                $this->server = $server;
                $this->home = $home;
                parent::__construct('localhost');
            }

            protected function createV4($host, $port, $timeout)
            {
                return new PipeSFTPEngine($this->server, $this->home);
            }
        };
    }

    /**
     * Returns an SFTP object that talks to sftp-server
     *
     * @return SFTP
     */
    private function sftp()
    {
        $sftp = $this->newSFTP();
        $this->assertTrue($sftp->login('username', 'password'));
        return $sftp;
    }

    public function testCallsBeforeLoginReturnFalse()
    {
        $sftp = $this->newSFTP();

        $this->assertFalse($sftp->pwd());
        $this->assertFalse($sftp->put('file.txt', 'data'));
    }

    public function testRefusedSubsystemReturnsFalse()
    {
        $sftp = $this->sftp();
        $sftp->getV4Object()->refuseSubsystem = true;

        $this->assertFalse($sftp->pwd());
        $this->assertFalse($sftp->put('file.txt', 'data'));
    }

    public function testTimedOutSubsystemReturnsFalse()
    {
        $sftp = $this->sftp();
        $sftp->getV4Object()->timeOutOnOpen = true;

        $this->assertFalse($sftp->pwd());
    }

    public function testUnsupportedSFTPVersionReturnsFalse()
    {
        $sftp = $this->sftp();
        $sftp->getV4Object()->sftpVersion = 7;

        $this->assertFalse($sftp->pwd());
    }

    public function testUnwritableLocalFileReturnsFalse()
    {
        $sftp = $this->sftp();
        $this->assertTrue($sftp->put('file.txt', 'data'));

        $this->assertFalse($sftp->get('file.txt', $this->home . '/does/not/exist'));
    }

    public function testMissingLocalFileThrows()
    {
        $sftp = $this->sftp();

        $this->expectException(FileNotFoundException::class);

        $sftp->put('file.txt', $this->home . '/does-not-exist', SFTP::SOURCE_LOCAL_FILE);
    }

    public function testNonCallableCallbackThrows()
    {
        $sftp = $this->sftp();

        $this->expectException(\BadFunctionCallException::class);

        $sftp->put('file.txt', 'not callable', SFTP::SOURCE_CALLBACK);
    }

    /**
     * phpseclib 3.0 passed the data to any callable, not just closures
     */
    public function testCallableLocalFile()
    {
        $sftp = $this->sftp();
        $this->assertTrue($sftp->put('file.txt', 'data'));

        $chunks = new \ArrayObject();
        $this->assertTrue($sftp->get('file.txt', [$chunks, 'append']));
        $this->assertSame(['data'], $chunks->getArrayCopy());
    }

    public function testQueueSizeConstants()
    {
        $this->newSFTP();

        $this->assertSame(32, NET_SFTP_QUEUE_SIZE);
        $this->assertSame(1024, NET_SFTP_UPLOAD_QUEUE_SIZE);
    }

    /**
     * phpseclib 3.0 ignored progress callbacks that weren't callable
     */
    public function testProgressCallbacksThatArentCallableAreIgnored()
    {
        $sftp = $this->sftp();

        $this->assertTrue($sftp->put('file.txt', 'data', SFTP::SOURCE_STRING, -1, -1, false));
        $this->assertSame('data', $sftp->get('file.txt', false, 0, -1, ''));
    }

    public function testMkdirOnExistingDirectory()
    {
        $sftp = $this->sftp();
        $this->assertTrue($sftp->mkdir('dir'));

        $this->assertFalse($sftp->mkdir('dir'));
        $this->assertSame('NET_SFTP_STATUS_FAILURE: Failure', $sftp->getLastSFTPError());
    }

    public function testTouchOnExistingFile()
    {
        $sftp = $this->sftp();
        $this->assertTrue($sftp->put('file.txt', 'data'));

        $this->assertTrue($sftp->touch('file.txt', 1000000000));
        $this->assertSame(1000000000, $sftp->filemtime('file.txt'));
    }

    public function testDeleteOfMissingDirectoryReturnsFalse()
    {
        $sftp = $this->sftp();

        $this->assertFalse($sftp->delete('does-not-exist'));
    }

    public function testFsockIsTheSocket()
    {
        $sftp = $this->sftp();

        $this->assertIsResource($sftp->fsock);
    }
}
