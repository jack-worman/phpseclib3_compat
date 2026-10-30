<?php

/**
 * Pure-PHP implementation of SSHv2.
 *
 * Here are some examples of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $ssh = new \phpseclib3\Net\SSH2('www.domain.tld');
 *    if (!$ssh->login('username', 'password')) {
 *        exit('Login Failed');
 *    }
 *
 *    echo $ssh->exec('pwd');
 *    echo $ssh->exec('ls -la');
 * ?>
 * </code>
 *
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $key = \phpseclib3\Crypt\PublicKeyLoader::load('...', '(optional) password');
 *
 *    $ssh = new \phpseclib3\Net\SSH2('www.domain.tld');
 *    if (!$ssh->login('username', $key)) {
 *        exit('Login Failed');
 *    }
 *
 *    echo $ssh->read('username@username:~$');
 *    $ssh->write("ls -la\n");
 *    echo $ssh->read('username@username:~$');
 * ?>
 * </code>
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Net;

use phpseclib3\Compat\Convert;
use phpseclib3\Compat\Net\Constants;
use phpseclib3\Compat\Net\SSH2Engine;
use phpseclib3\Compat\Wrapper;
use phpseclib3\Compat\WrapperTrait;
use phpseclib4\Exception\BaseException;
use phpseclib4\Exception\SSHChannelExitSignalException;
use phpseclib4\Exception\UnexpectedValueException;

/**
 * Pure-PHP implementation of SSHv2.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class SSH2 implements Wrapper
{
    use WrapperTrait;

    /**
     * Errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * How many of phpseclib 4.0's debug messages are in $errors
     *
     * @var int
     */
    private $debugMessagesSeen = 0;

    /**
     * Timeout
     *
     * phpseclib 4.0 only supports integer timeouts
     *
     * @var int|float
     */
    protected $timeout;

    /**
     * Connections
     *
     * @var array<string, \WeakReference<SSH2>>
     */
    private static $connections = [];

    /**
     * No compression
     */
    const NET_SSH2_COMPRESSION_NONE = 1;

    /**
     * zlib compression
     */
    const NET_SSH2_COMPRESSION_ZLIB = 2;

    /**
     * zlib@openssh.com
     */
    const NET_SSH2_COMPRESSION_ZLIB_AT_OPENSSH = 3;

    const MASK_CONSTRUCTOR = 1;

    const MASK_CONNECTED = 2;

    const MASK_LOGIN_REQ = 4;

    const MASK_LOGIN = 8;

    const MASK_SHELL = 16;

    const MASK_DISCONNECT = 32;

    const CHANNEL_EXEC = 1;

    const CHANNEL_SHELL = 2;

    const CHANNEL_SUBSYSTEM = 3;

    const CHANNEL_AGENT_FORWARD = 4;

    const CHANNEL_KEEP_ALIVE = 5;

    /**
     * Returns the message numbers
     *
     * @see SSH2::getLog()
     */
    const LOG_SIMPLE = 1;

    /**
     * Returns the message content
     *
     * @see SSH2::getLog()
     */
    const LOG_COMPLEX = 2;

    /**
     * Outputs the content real-time
     */
    const LOG_REALTIME = 3;

    /**
     * Dumps the content real-time to a file
     */
    const LOG_REALTIME_FILE = 4;

    /**
     * Outputs the message numbers real-time
     */
    const LOG_SIMPLE_REALTIME = 5;

    const LOG_REALTIME_SIMPLE = 5;

    /**
     * Make sure that the log never gets larger than this
     *
     * @see SSH2::getLog()
     */
    const LOG_MAX_SIZE = 1048576;

    /**
     * Returns when a string matching $expect exactly is found
     *
     * @see SSH2::read()
     */
    const READ_SIMPLE = 1;

    /**
     * Returns when a string matching the regular expression $expect is found
     *
     * @see SSH2::read()
     */
    const READ_REGEX = 2;

    /**
     * Returns whenever a data packet is received.
     *
     * Some data packets may only contain a single character so it may be necessary
     * to call read() multiple times when using this option
     *
     * @see SSH2::read()
     */
    const READ_NEXT = 3;

    /**
     * Default Constructor.
     *
     * $host can either be a string, representing the host, or a stream resource.
     * If $host is a stream resource then $port doesn't do anything, altho $timeout
     * still will be used
     *
     * @param mixed $host
     * @param int $port
     * @param int $timeout
     * @see self::login()
     */
    public function __construct($host, $port = 22, $timeout = 10)
    {
        Constants::define();

        try {
            $this->v4 = $this->createV4($host, $port, $timeout);
        } catch (BaseException $e) {
            throw Convert::exception($e);
        }
        $this->timeout = $timeout;
        self::$connections[$this->getResourceId()] = \WeakReference::create($this);
    }

    /**
     * Set Crypto Engine Mode
     *
     * Possible $engine values:
     * OpenSSL, mcrypt, Eval, PHP
     *
     * @param int $engine
     */
    public static function setCryptoEngine($engine)
    {
        SSH2Engine::setCryptoEngine($engine);
    }

    /**
     * Send Identification String First
     *
     * https://tools.ietf.org/html/rfc4253#section-4.2 says "when the connection has been established,
     * both sides MUST send an identification string". It does not say which side sends it first. In
     * theory it shouldn't matter but it is a fact of life that some SSH servers are simply buggy
     *
     */
    public function sendIdentificationStringFirst()
    {
        $this->call('sendIdentificationStringFirst');
    }

    /**
     * Send Identification String Last
     *
     * https://tools.ietf.org/html/rfc4253#section-4.2 says "when the connection has been established,
     * both sides MUST send an identification string". It does not say which side sends it first. In
     * theory it shouldn't matter but it is a fact of life that some SSH servers are simply buggy
     *
     */
    public function sendIdentificationStringLast()
    {
        $this->call('sendIdentificationStringLast');
    }

    /**
     * Send SSH_MSG_KEXINIT First
     *
     * https://tools.ietf.org/html/rfc4253#section-7.1 says "key exchange begins by each sending
     * sending the [SSH_MSG_KEXINIT] packet". It does not say which side sends it first. In theory
     * it shouldn't matter but it is a fact of life that some SSH servers are simply buggy
     *
     */
    public function sendKEXINITFirst()
    {
        $this->call('sendKEXINITFirst');
    }

    /**
     * Send SSH_MSG_KEXINIT Last
     *
     * https://tools.ietf.org/html/rfc4253#section-7.1 says "key exchange begins by each sending
     * sending the [SSH_MSG_KEXINIT] packet". It does not say which side sends it first. In theory
     * it shouldn't matter but it is a fact of life that some SSH servers are simply buggy
     *
     */
    public function sendKEXINITLast()
    {
        $this->call('sendKEXINITLast');
    }

    /**
     * Login
     *
     * The $password parameter can be a plaintext password, a \phpseclib3\Crypt\RSA|EC|DSA object, a \phpseclib3\System\SSH\Agent object or an array
     *
     * @param string $username
     * @param string|PrivateKey|array[]|Agent|null ...$args
     * @return bool
     * @see self::_login()
     */
    public function login($username, ...$args)
    {
        if ($this->call('login', $username, ...$args)) {
            return true;
        }
        $this->errors[] = 'SSH_MSG_USERAUTH_FAILURE';
        return false;
    }

    /**
     * Return the currently configured timeout
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set Timeout
     *
     * $ssh->exec('ping 127.0.0.1'); on a Linux host will never return and will run indefinitely.  setTimeout() makes it so it'll timeout.
     * Setting $timeout to false or 0 will revert to the default socket timeout.
     *
     * @param mixed $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        $this->call('setTimeout', self::intTimeout($timeout));
    }

    /**
     * Set Keep Alive
     *
     * Sends an SSH2_MSG_IGNORE message every x seconds, if x is a positive non-zero number.
     *
     * @param int $interval
     */
    public function setKeepAlive($interval)
    {
        $this->call('setKeepAlive', self::intTimeout($interval));
    }

    /**
     * Get the output from stdError
     *
     */
    public function getStdError()
    {
        return $this->call('getStdError');
    }

    /**
     * Execute Command
     *
     * If $callback is set to false then \phpseclib3\Net\SSH2::get_channel_packet(self::CHANNEL_EXEC) will need to be called manually.
     * In all likelihood, this is not a feature you want to be taking advantage of.
     *
     * @param string $command
     * @param callable $callback
     * @return string|bool
     * @psalm-return ($callback is callable ? bool : string|bool)
     * @throws \RuntimeException on connection error
     */
    public function exec($command, $callback = null)
    {
        if (!$this->v4->isAuthenticated()) {
            return false;
        }
        try {
            if ($callback === false) {
                $this->v4->startExec($command);
                return true;
            }
            $result = $this->v4->exec($command, is_callable($callback) ? \Closure::fromCallable($callback) : null);
        } catch (SSHChannelExitSignalException $e) {
            $this->exitSignalError($e);
            $result = is_callable($callback) ? null : $e->partialOutput;
        } catch (UnexpectedValueException $e) {
            // phpseclib 3.0 returned false when the server refused to run the command
            if ($e->getMessage() == 'Unable to execute command') {
                return false;
            }
            throw Convert::exception($e);
        } catch (BaseException $e) {
            throw Convert::exception($e);
        } finally {
            $this->collectErrors();
        }
        return $result ?? true;
    }

    /**
     * How many channels are currently open?
     *
     * @return int
     */
    public function getOpenChannelCount()
    {
        return $this->call('getOpenChannelCount');
    }

    /**
     * Creates an interactive shell
     *
     * Returns bool(true) if the shell was opened.
     * Returns bool(false) if the shell was already open.
     *
     * @see self::isShellOpen()
     * @see self::read()
     * @see self::write()
     * @return bool
     * @throws InsufficientSetupException if not authenticated
     * @throws \UnexpectedValueException on receipt of unexpected packets
     * @throws \RuntimeException on other errors
     */
    public function openShell()
    {
        $this->call('openShell');
        return true;
    }

    /**
     * Request agent forwarding of remote server
     *
     * @return bool
     */
    public function requestAgentForwarding()
    {
        return $this->call('requestAgentForwarding');
    }

    /**
     * Returns the output of an interactive shell
     *
     * Returns when there's a match for $expect, which can take the form of a string literal or,
     * if $mode == self::READ_REGEX, a regular expression.
     *
     * If not specifying a channel, an open interactive channel will be selected, or, if there are
     * no open channels, an interactive shell will be created. If there are multiple open
     * interactive channels, a legacy behavior will apply in which channel selection prioritizes
     * an active subsystem, the exec pty, and, lastly, the shell. If using multiple interactive
     * channels, callers are discouraged from relying on this legacy behavior and should specify
     * the intended channel.
     *
     * @see self::write()
     * @param string $expect
     * @param int $mode One of the self::READ_* constants
     * @param int|null $channel Channel id returned by self::getInteractiveChannelId()
     * @return string|bool|null
     * @throws \RuntimeException on connection error
     * @throws InsufficientSetupException on unexpected channel status, possibly due to closure
     */
    public function read($expect = '', $mode = self::READ_SIMPLE, $channel = null)
    {
        try {
            return $this->v4->read($expect, $mode, $channel);
        } catch (SSHChannelExitSignalException $e) {
            $this->exitSignalError($e);
            return $e->partialOutput;
        } catch (BaseException $e) {
            throw Convert::exception($e);
        } finally {
            $this->collectErrors();
        }
    }

    /**
     * Inputs a command into an interactive shell.
     *
     * If not specifying a channel, an open interactive channel will be selected, or, if there are
     * no open channels, an interactive shell will be created. If there are multiple open
     * interactive channels, a legacy behavior will apply in which channel selection prioritizes
     * an active subsystem, the exec pty, and, lastly, the shell. If using multiple interactive
     * channels, callers are discouraged from relying on this legacy behavior and should specify
     * the intended channel.
     *
     * @see SSH2::read()
     * @param string $cmd
     * @param int|null $channel Channel id returned by self::getInteractiveChannelId()
     * @return void
     * @throws \RuntimeException on connection error
     * @throws InsufficientSetupException on unexpected channel status, possibly due to closure
     * @throws TimeoutException if the write could not be completed within the requested self::setTimeout()
     */
    public function write($cmd, $channel = null)
    {
        $this->call('write', $cmd, $channel);
    }

    /**
     * Start a subsystem.
     *
     * Right now only one subsystem at a time is supported. To support multiple subsystem's stopSubsystem() could accept
     * a string that contained the name of the subsystem, but at that point, only one subsystem of each type could be opened.
     * To support multiple subsystem's of the same name maybe it'd be best if startSubsystem() generated a new channel id and
     * returns that and then that that was passed into stopSubsystem() but that'll be saved for a future date and implemented
     * if there's sufficient demand for such a feature.
     *
     * @see self::stopSubsystem()
     * @param string $subsystem
     * @return bool
     */
    public function startSubsystem($subsystem)
    {
        return $this->call('startSubsystem', $subsystem);
    }

    /**
     * Stops a subsystem.
     *
     * @see self::startSubsystem()
     * @return bool
     */
    public function stopSubsystem()
    {
        $this->call('stopSubsystem');
        return true;
    }

    /**
     * Closes a channel
     *
     * If read() timed out you might want to just close the channel and have it auto-restart on the next read() call
     *
     * If not specifying a channel, an open interactive channel will be selected. If there are
     * multiple open interactive channels, a legacy behavior will apply in which channel selection
     * prioritizes an active subsystem, the exec pty, and, lastly, the shell. If using multiple
     * interactive channels, callers are discouraged from relying on this legacy behavior and
     * should specify the intended channel.
     *
     * @param int|null $channel Channel id returned by self::getInteractiveChannelId()
     * @return void
     */
    public function reset($channel = null)
    {
        $this->call('reset', $channel);
    }

    /**
     * Send EOF on a channel
     *
     * Sends an EOF to the stream; this is typically used to close standard
     * input, while keeping output and error alive.
     *
     * @param int|null $channel Channel id returned by self::getInteractiveChannelId()
     * @return void
     */
    public function sendEOF($channel = null)
    {
        $this->call('sendEOF', $channel);
    }

    /**
     * Is timeout?
     *
     * Did exec() or read() return because they timed out or because they encountered the end?
     *
     */
    public function isTimeout()
    {
        return $this->call('isTimeout');
    }

    /**
     * Disconnect
     *
     */
    public function disconnect()
    {
        $this->call('disconnect');
        unset(self::$connections[$this->getResourceId()]);
    }

    /**
     * Destructor.
     *
     * Disconnects, like phpseclib 3.0's destructor, which subclasses' destructors could call
     */
    public function __destruct()
    {
        // objects that were created without calling the constructor, eg. PHPUnit's mocks, aren't connected
        if (isset($this->v4)) {
            $this->disconnect();
        }
    }

    /**
     * Is the connection still active?
     *
     * $level has 3x possible values:
     * 0 (default): phpseclib takes a passive approach to see if the connection is still active by calling feof()
     *    on the socket
     * 1: phpseclib takes an active approach to see if the connection is still active by sending an SSH_MSG_IGNORE
     *    packet that doesn't require a response
     * 2: phpseclib takes an active approach to see if the connection is still active by sending an SSH_MSG_CHANNEL_OPEN
     *    packet and imediately trying to close that channel. some routers, in particular, however, will only let you
     *    open one channel, so this approach could yield false positives
     *
     * @param int $level
     * @return bool
     */
    public function isConnected($level = 0)
    {
        return $this->call('isConnected', $level);
    }

    /**
     * Have you successfully been logged in?
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->call('isAuthenticated');
    }

    /**
     * Is the interactive shell active?
     *
     * @return bool
     */
    public function isShellOpen()
    {
        return $this->call('isShellOpen');
    }

    /**
     * Is the exec pty active?
     *
     * @return bool
     */
    public function isPTYOpen()
    {
        return $this->call('isPTYOpen');
    }

    /**
     * Is the given interactive channel active?
     *
     * @param int $channel Channel id returned by self::getInteractiveChannelId()
     * @return bool
     */
    public function isInteractiveChannelOpen($channel)
    {
        return $this->call('isInteractiveChannelOpen', $channel);
    }

    /**
     * Returns a channel identifier, presently of the last interactive channel opened, regardless of current status.
     * Returns 0 if no interactive channel has been opened.
     *
     * @see self::isInteractiveChannelOpen()
     * @return int
     */
    public function getInteractiveChannelId()
    {
        return $this->call('getInteractiveChannelId');
    }

    /**
     * Pings a server connection, or tries to reconnect if the connection has gone down
     *
     * Inspired by http://php.net/manual/en/mysqli.ping.php
     *
     * @return bool
     */
    public function ping()
    {
        return $this->call('ping');
    }

    /**
     * Enable Quiet Mode
     *
     * Suppress stderr from output
     *
     */
    public function enableQuietMode()
    {
        $this->call('enableQuietMode');
    }

    /**
     * Disable Quiet Mode
     *
     * Show stderr in output
     *
     */
    public function disableQuietMode()
    {
        $this->call('disableQuietMode');
    }

    /**
     * Returns whether Quiet Mode is enabled or not
     *
     * @see self::enableQuietMode()
     * @see self::disableQuietMode()
     * @return bool
     */
    public function isQuietModeEnabled()
    {
        return $this->call('isQuietModeEnabled');
    }

    /**
     * Enable request-pty when using exec()
     *
     */
    public function enablePTY()
    {
        $this->call('enablePTY');
    }

    /**
     * Disable request-pty when using exec()
     *
     */
    public function disablePTY()
    {
        $this->call('disablePTY');
    }

    /**
     * Returns whether request-pty is enabled or not
     *
     * @see self::enablePTY()
     * @see self::disablePTY()
     * @return bool
     */
    public function isPTYEnabled()
    {
        return $this->call('isPTYEnabled');
    }

    /**
     * Returns a log of the packets that have been sent and received.
     *
     * Returns a string if NET_SSH2_LOGGING == self::LOG_COMPLEX, an array if NET_SSH2_LOGGING == self::LOG_SIMPLE and false if !defined('NET_SSH2_LOGGING')
     *
     * @return array|false|string
     */
    public function getLog()
    {
        return $this->call('getLog') ?? false;
    }

    /**
     * Returns all errors / debug messages on the SSH layer
     *
     * If you are looking for messages from the SFTP layer, please see SFTP::getSFTPErrors()
     *
     * @return string[]
     * @removed in phpseclib 4.0.0
     */
    public function getErrors()
    {
        $this->collectErrors();
        return $this->errors;
    }

    /**
     * Returns the last error received on the SSH layer
     *
     * If you are looking for messages from the SFTP layer, please see SFTP::getLastSFTPError()
     *
     * @return string
     * @removed in phpseclib 4.0.0
     */
    public function getLastError()
    {
        $this->collectErrors();
        $count = count($this->errors);

        if ($count > 0) {
            return $this->errors[$count - 1];
        }
    }

    /**
     * Return the server identification.
     *
     * @return string|false
     */
    public function getServerIdentification()
    {
        return $this->call('getServerIdentification');
    }

    /**
     * Returns a list of algorithms the server supports
     *
     * @return array
     */
    public function getServerAlgorithms()
    {
        return $this->call('getServerAlgorithms');
    }

    /**
     * Returns a list of KEX algorithms that phpseclib supports
     *
     * @return array
     */
    public static function getSupportedKEXAlgorithms()
    {
        return SSH2Engine::getSupportedKEXAlgorithms();
    }

    /**
     * Returns a list of host key algorithms that phpseclib supports
     *
     * @return array
     */
    public static function getSupportedHostKeyAlgorithms()
    {
        return SSH2Engine::getSupportedHostKeyAlgorithms();
    }

    /**
     * Returns a list of symmetric key algorithms that phpseclib supports
     *
     * @return array
     */
    public static function getSupportedEncryptionAlgorithms()
    {
        return SSH2Engine::getSupportedEncryptionAlgorithms();
    }

    /**
     * Returns a list of MAC algorithms that phpseclib supports
     *
     * @return array
     */
    public static function getSupportedMACAlgorithms()
    {
        return SSH2Engine::getSupportedMACAlgorithms();
    }

    /**
     * Returns a list of compression algorithms that phpseclib supports
     *
     * @return array
     */
    public static function getSupportedCompressionAlgorithms()
    {
        return SSH2Engine::getSupportedCompressionAlgorithms();
    }

    /**
     * Return list of negotiated algorithms
     *
     * Uses the same format as https://www.php.net/ssh2-methods-negotiated
     *
     * @return array
     */
    public function getAlgorithmsNegotiated()
    {
        return $this->call('getAlgorithmsNegotiated');
    }

    /**
     * Force multiple channels (even if phpseclib has decided to disable them)
     */
    public function forceMultipleChannels()
    {
        $this->call('forceMultipleChannels');
    }

    /**
     * Allows you to set the terminal
     *
     * @param string $term
     */
    public function setTerminal($term)
    {
        $this->call('setTerminal', $term);
    }

    /**
     * Accepts an associative array with up to four parameters as described at
     * <https://www.php.net/manual/en/function.ssh2-connect.php>
     *
     * @param array $methods
     */
    public function setPreferredAlgorithms(array $methods)
    {
        $this->call('setPreferredAlgorithms', $methods);
    }

    /**
     * Returns the banner message.
     *
     * Quoting from the RFC, "in some jurisdictions, sending a warning message before
     * authentication may be relevant for getting legal protection."
     *
     * @return string
     */
    public function getBannerMessage()
    {
        return $this->call('getBannerMessage');
    }

    /**
     * Returns the server public host key.
     *
     * Caching this the first time you connect to a server and checking the result on subsequent connections
     * is recommended.  Returns false if the server signature is not signed correctly with the public host key.
     *
     * @return string|false
     * @throws \RuntimeException on badly formatted keys
     * @throws NoSupportedAlgorithmsException when the key isn't in a supported format
     */
    public function getServerPublicHostKey()
    {
        return $this->call('getServerPublicHostKey') ?? false;
    }

    /**
     * Returns the exit status of an SSH command or false.
     *
     * @return false|int
     */
    public function getExitStatus()
    {
        return $this->call('getExitStatus') ?? false;
    }

    /**
     * Returns the number of columns for the terminal window size.
     *
     * @return int
     */
    public function getWindowColumns()
    {
        return $this->call('getWindowColumns');
    }

    /**
     * Returns the number of rows for the terminal window size.
     *
     * @return int
     */
    public function getWindowRows()
    {
        return $this->call('getWindowRows');
    }

    /**
     * Sets the number of columns for the terminal window size.
     *
     * @param int $value
     */
    public function setWindowColumns($value)
    {
        $this->call('setWindowColumns', $value);
    }

    /**
     * Sets the number of rows for the terminal window size.
     *
     * @param int $value
     */
    public function setWindowRows($value)
    {
        $this->call('setWindowRows', $value);
    }

    /**
     * Sets the number of columns and rows for the terminal window size.
     *
     * @param int $columns
     * @param int $rows
     */
    public function setWindowSize($columns = 80, $rows = 24)
    {
        $this->call('setWindowSize', $columns, $rows);
    }

    /**
     * To String Magic Method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getResourceId();
    }

    /**
     * Get Resource ID
     *
     * We use {} because that symbols should not be in URL according to
     * {@link http://tools.ietf.org/html/rfc3986#section-2 RFC}.
     * It will safe us from any conflicts, because otherwise regexp will
     * match all alphanumeric domains.
     *
     * @return string
     */
    public function getResourceId()
    {
        return '{' . spl_object_hash($this) . '}';
    }

    /**
     * Return existing connection
     *
     * @param string $id
     *
     * @return bool|SSH2 will return false if no such connection
     */
    public static function getConnectionByResourceId($id)
    {
        if (isset(self::$connections[$id])) {
            $connection = self::$connections[$id]->get();
            if (isset($connection)) {
                return $connection;
            }
        }
        return false;
    }

    /**
     * Return all excising connections
     *
     * @return array<string, SSH2>
     */
    public static function getConnections()
    {
        $temp = [];
        foreach (self::$connections as $key => $ref) {
            $connection = $ref->get();
            if (isset($connection)) {
                $temp[$key] = $connection;
            }
        }
        return $temp;
    }

    /**
     * Return the list of authentication methods that may productively continue authentication.
     *
     * @see https://tools.ietf.org/html/rfc4252#section-5.1
     * @return array|null
     */
    public function getAuthMethodsToContinue()
    {
        return $this->call('getAuthMethodsToContinue');
    }

    /**
     * Enables "smart" multi-factor authentication (MFA)
     */
    public function enableSmartMFA()
    {
        $this->call('enableSmartMFA');
    }

    /**
     * Disables "smart" multi-factor authentication (MFA)
     */
    public function disableSmartMFA()
    {
        $this->call('disableSmartMFA');
    }

    /**
     * How many bytes until the next key re-exchange?
     *
     * @param int $bytes
     */
    public function bytesUntilKeyReexchange($bytes)
    {
        $this->call('setBytesUntilKeyReexchange', $bytes);
    }

    /**
     * Returns the socket, which phpseclib 3.0 made public as $fsock
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($name == 'fsock') {
            return $this->v4->fsock;
        }

        self::checkPropertyAccess($name);
        trigger_error('Undefined property: ' . static::class . '::$' . $name, E_USER_WARNING);
        return null;
    }

    /**
     * Sets the socket, which phpseclib 3.0 made public as $fsock
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if ($name == 'fsock') {
            $this->v4->fsock = $value;
            return;
        }

        self::checkPropertyAccess($name);
        $this->$name = $value;
    }

    /**
     * Throws the Error that PHP throws for protected and private properties
     *
     * __get() and __set() are also called for those when they're accessed from outside the class
     *
     * @param string $name
     */
    private static function checkPropertyAccess($name)
    {
        for ($class = static::class; $class !== false; $class = get_parent_class($class)) {
            if (property_exists($class, $name)) {
                $visibility = (new \ReflectionProperty($class, $name))->isPrivate() ? 'private' : 'protected';
                throw new \Error("Cannot access $visibility property " . static::class . '::$' . $name);
            }
        }
    }

    /**
     * Checks whether the socket, which phpseclib 3.0 made public as $fsock, is set
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $name == 'fsock' && isset($this->v4->fsock);
    }

    /**
     * Creates the phpseclib 4.0 object
     *
     * @param mixed $host
     * @param int $port
     * @param int|float $timeout
     * @return SSH2Engine
     */
    protected function createV4($host, $port, $timeout)
    {
        return new SSH2Engine($host, (int) $port, self::intTimeout($timeout));
    }

    /**
     * Converts a timeout to an integer
     *
     * phpseclib 4.0 only supports integer timeouts so fractional timeouts are rounded up. Like false, null
     * means no timeout
     *
     * @param int|float|bool|null $timeout
     * @return int
     */
    protected static function intTimeout($timeout)
    {
        return (int) ceil((float) $timeout);
    }

    /**
     * Notes that the command was killed by a signal, as phpseclib 3.0 did
     *
     * @param SSHChannelExitSignalException $e
     */
    private function exitSignalError(SSHChannelExitSignalException $e)
    {
        $error = 'SSH_MSG_CHANNEL_REQUEST (exit-signal): ' . $e->signalName;
        if (strlen($e->errorMessage)) {
            $error .= "\r\n" . $e->errorMessage;
        }
        $this->errors[] = $error;
    }

    /**
     * Adds phpseclib 4.0's debug messages to the error log, as phpseclib 3.0 did
     *
     * The messages are worded the way phpseclib 3.0 worded them
     */
    protected function collectErrors()
    {
        $messages = $this->v4->getDebugMessages();
        foreach (array_slice($messages, $this->debugMessagesSeen) as $message) {
            $this->errors[] = preg_replace(
                [
                    '~^DISCONNECT: (?=\w)~',
                    '~^DISCONNECT: ~',
                    '~^DEBUG: ~',
                    '~^GLOBAL_REQUEST \((.*)\) made by the server and REQUEST_FAILURE sent in response$~s',
                ],
                [
                    'SSH_MSG_DISCONNECT: NET_SSH2_DISCONNECT_',
                    'SSH_MSG_DISCONNECT: ',
                    'SSH_MSG_DEBUG: ',
                    'SSH_MSG_GLOBAL_REQUEST: $1',
                ],
                $message
            );
        }
        $this->debugMessagesSeen = count($messages);
    }
}
