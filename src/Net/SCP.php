<?php

/**
 * Pure-PHP implementation of SCP.
 *
 * The API for this library is modeled after the API from PHP's {@link http://php.net/book.ftp FTP extension}.
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $scp = new \phpseclib3\Net\SCP('www.domain.tld');
 *    if (!$scp->login('username', 'password')) {
 *        exit('Login Failed');
 *    }
 *
 *    echo $scp->exec('pwd') . "\r\n";
 *    $scp->put('filename.ext', 'hello, world!');
 *    echo $scp->exec('ls -latr');
 * ?>
 * </code>
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2009 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Net;

use phpseclib3\Compat\Convert;
use phpseclib3\Compat\Net\SCPEngine;
use phpseclib4\Exception\BaseException;
use phpseclib4\Exception\ConnectionClosedException;
use phpseclib4\Exception\TimeoutException;

/**
 * Pure-PHP implementations of SCP.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class SCP extends SSH2
{
    /**
     * Error information
     *
     * @var array
     */
    private $scp_errors = [];

    /**
     * Reads data from a local file.
     *
     * @see SCP::put()
     */
    const SOURCE_LOCAL_FILE = 1;

    /**
     * Reads data from a string.
     *
     * @see SCP::put()
     */
    const SOURCE_STRING = 2;

    /**
     * Uploads a file to the SCP server.
     *
     * By default, \phpseclib\Net\SCP::put() does not read from the local filesystem.  $data is dumped directly into $remote_file.
     * So, for example, if you set $data to 'filename.ext' and then do \phpseclib\Net\SCP::get(), you will get a file, twelve bytes
     * long, containing 'filename.ext' as its contents.
     *
     * Setting $mode to self::SOURCE_LOCAL_FILE will change the above behavior.  With self::SOURCE_LOCAL_FILE, $remote_file will
     * contain as many bytes as filename.ext does on your local filesystem.  If your filename.ext is 1MB then that is how
     * large $remote_file will be, as well.
     *
     * Currently, only binary mode is supported.  As such, if the line endings need to be adjusted, you will need to take
     * care of that, yourself.
     *
     * @param string $remote_file
     * @param string $data
     * @param int $mode
     * @param callable $callback
     * @return bool
     * @access public
     */
    public function put($remote_file, $data, $mode = self::SOURCE_STRING, $callback = null)
    {
        // phpseclib 3.0 ignored callbacks that weren't callable
        $callback = is_callable($callback) ? \Closure::fromCallable($callback) : null;
        return $this->callOrFalse('put', $remote_file, $data, $mode, $callback) !== false;
    }

    /**
     * Downloads a file from the SCP server.
     *
     * Returns a string containing the contents of $remote_file if $local_file is left undefined or a boolean false if
     * the operation was unsuccessful.  If $local_file is defined, returns true or false depending on the success of the
     * operation
     *
     * @param string $remote_file
     * @param string $local_file
     * @return mixed
     * @access public
     */
    public function get($remote_file, $local_file = null, $progressCallback = null)
    {
        $progressCallback = is_callable($progressCallback) ? \Closure::fromCallable($progressCallback) : null;
        $result = $this->callOrFalse('get', $remote_file, $local_file === false ? null : $local_file, $progressCallback);
        return $result === null ? true : $result;
    }

    /**
     * Returns all errors on the SCP layer
     *
     * @return array
     */
    public function getSCPErrors()
    {
        return $this->scp_errors;
    }

    /**
     * Returns the last error on the SCP layer
     *
     * @return string
     */
    public function getLastSCPError()
    {
        return count($this->scp_errors) ? $this->scp_errors[count($this->scp_errors) - 1] : '';
    }

    /**
     * Creates the phpseclib 4.0 object
     *
     * @param mixed $host
     * @param int $port
     * @param int|float $timeout
     * @return SCPEngine
     */
    protected function createV4($host, $port, $timeout)
    {
        return new SCPEngine($host, (int) $port, self::intTimeout($timeout));
    }

    /**
     * Calls a method of the phpseclib 4.0 object, returning false when phpseclib 3.0 did
     *
     * phpseclib 3.0 logged errors and returned false. Missing local files were the exception to that.
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    private function callOrFalse($method, ...$args)
    {
        $connected = $this->v4->isConnected();
        try {
            return $this->v4->$method(...$args);
        } catch (ConnectionClosedException | TimeoutException $e) {
            // problems with the connection itself were exceptions in phpseclib 3.0, too
            throw Convert::exception($e);
        } catch (BaseException $e) {
            // as were the errors that phpseclib 4.0 disconnects for, eg. unexpected messages from the server
            if (($connected && !$this->v4->isConnected()) || preg_match('~ is not a valid file$~', $e->getMessage())) {
                throw Convert::exception($e);
            }
            $this->scp_errors[] = $e->getMessage();
            return false;
        }
    }
}
