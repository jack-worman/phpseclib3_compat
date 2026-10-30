<?php

/**
 * Pure-PHP implementation of SFTP.
 *
 * Supports SFTPv2/3/4/5/6. Defaults to v3.
 *
 * The API for this library is modeled after the API from PHP's {@link http://php.net/book.ftp FTP extension}.
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $sftp = new \phpseclib3\Net\SFTP('www.domain.tld');
 *    if (!$sftp->login('username', 'password')) {
 *        exit('Login Failed');
 *    }
 *
 *    echo $sftp->pwd() . "\r\n";
 *    $sftp->put('filename.ext', 'hello, world!');
 *    print_r($sftp->nlist());
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
use phpseclib3\Compat\Net\SFTPEngine;
use phpseclib4\Exception\BaseException;
use phpseclib4\Exception\FileSystemException;
use phpseclib4\Exception\InvalidStateException;
use phpseclib4\Net\SFTP\StatusCode;

/**
 * Pure-PHP implementations of SFTP.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class SFTP extends SSH2
{
    /**
     * Error information
     *
     * @see self::getSFTPErrors()
     * @see self::getLastSFTPError()
     * @var array
     */
    private $sftp_errors = [];

    /**
     * The errors phpseclib 4.0 logged during the last call
     *
     * @var array
     */
    private $v4_errors = [];

    /**
     * SFTP channel constant
     *
     * \phpseclib3\Net\SSH2::exec() uses 0 and \phpseclib3\Net\SSH2::read() / \phpseclib3\Net\SSH2::write() use 1.
     *
     * @see SSH2::send_channel_packet()
     * @see SSH2::get_channel_packet()
     */
    const CHANNEL = 256;

    /**
     * Reads data from a local file.
     *
     * @see SFTP::put()
     */
    const SOURCE_LOCAL_FILE = 1;

    /**
     * Reads data from a string.
     *
     * @see SFTP::put()
     */
    const SOURCE_STRING = 2;

    /**
     * Reads data from callback:
     * function callback($length) returns string to proceed, null for EOF
     *
     * @see SFTP::put()
     */
    const SOURCE_CALLBACK = 16;

    /**
     * Resumes an upload
     *
     * @see SFTP::put()
     */
    const RESUME = 4;

    /**
     * Append a local file to an already existing remote file
     *
     * @see SFTP::put()
     */
    const RESUME_START = 8;

    /**
     * Default Constructor.
     *
     * Connects to an SFTP server
     *
     * $host can either be a string, representing the host, or a stream resource.
     *
     * @param mixed $host
     * @param int $port
     * @param int $timeout
     */
    public function __construct($host, $port = 22, $timeout = 10)
    {
        parent::__construct($host, $port, $timeout);

        // phpseclib 4.0 uses these when they're defined but doesn't define them, as phpseclib 3.0 did
        if (!defined('NET_SFTP_QUEUE_SIZE')) {
            define('NET_SFTP_QUEUE_SIZE', 32);
        }
        if (!defined('NET_SFTP_UPLOAD_QUEUE_SIZE')) {
            define('NET_SFTP_UPLOAD_QUEUE_SIZE', 1024);
        }
    }

    /**
     * Disable the stat cache
     *
     */
    public function disableStatCache()
    {
        $this->call('disableStatCache');
    }

    /**
     * Enable the stat cache
     *
     */
    public function enableStatCache()
    {
        $this->call('enableStatCache');
    }

    /**
     * Clear the stat cache
     *
     */
    public function clearStatCache()
    {
        $this->call('clearStatCache');
    }

    /**
     * Enable path canonicalization
     *
     */
    public function enablePathCanonicalization()
    {
        $this->call('enablePathCanonicalization');
    }

    /**
     * Disable path canonicalization
     *
     * If this is enabled then $sftp->pwd() will not return the canonicalized absolute path
     *
     */
    public function disablePathCanonicalization()
    {
        $this->call('disablePathCanonicalization');
    }

    /**
     * Enable arbitrary length packets
     *
     */
    public function enableArbitraryLengthPackets()
    {
        $this->call('enableArbitraryLengthPackets');
    }

    /**
     * Disable arbitrary length packets
     *
     */
    public function disableArbitraryLengthPackets()
    {
        $this->call('disableArbitraryLengthPackets');
    }

    /**
     * Returns the current directory name
     *
     * @return string|bool
     */
    public function pwd()
    {
        return $this->callOrFalse('pwd');
    }

    /**
     * Canonicalize the Server-Side Path Name
     *
     * SFTP doesn't provide a mechanism by which the current working directory can be changed, so we'll emulate it.  Returns
     * the absolute (canonicalized) path.
     *
     * If canonicalize_paths has been disabled using disablePathCanonicalization(), $path is returned as-is.
     *
     * @see self::chdir()
     * @see self::disablePathCanonicalization()
     * @param string $path
     * @throws \UnexpectedValueException on receipt of unexpected packets
     * @return mixed
     */
    public function realpath($path)
    {
        return $this->callOrFalse('realpath', $path);
    }

    /**
     * Changes the current directory
     *
     * @param string $dir
     * @throws \UnexpectedValueException on receipt of unexpected packets
     * @return bool
     */
    public function chdir($dir)
    {
        return $this->callOrFalse('chdir', $dir) !== false;
    }

    /**
     * Returns a list of files in the given directory
     *
     * @param string $dir
     * @param bool $recursive
     * @return array|false
     */
    public function nlist($dir = '.', $recursive = false)
    {
        return $this->callOrFalse('nlist', $dir, (bool) $recursive);
    }

    /**
     * Returns a detailed list of files in the given directory
     *
     * @param string $dir
     * @param bool $recursive
     * @return array|false
     */
    public function rawlist($dir = '.', $recursive = false)
    {
        return $this->callOrFalse('rawlist', $dir, (bool) $recursive);
    }

    /**
     * Defines how nlist() and rawlist() will be sorted - if at all.
     *
     * If sorting is enabled directories and files will be sorted independently with
     * directories appearing before files in the resultant array that is returned.
     *
     * Any parameter returned by stat is a valid sort parameter for this function.
     * Filename comparisons are case insensitive.
     *
     * Examples:
     *
     * $sftp->setListOrder('filename', SORT_ASC);
     * $sftp->setListOrder('size', SORT_DESC, 'filename', SORT_ASC);
     * $sftp->setListOrder(true);
     *    Separates directories from files but doesn't do any sorting beyond that
     * $sftp->setListOrder();
     *    Don't do any sort of sorting
     *
     * @param string ...$args
     */
    public function setListOrder(...$args)
    {
        $this->call('setListOrder', ...$args);
    }

    /**
     * Returns general information about a file.
     *
     * Returns an array on success and false otherwise.
     *
     * @param string $filename
     * @return array|false
     */
    public function stat($filename)
    {
        return $this->callOrFalse('stat', $filename);
    }

    /**
     * Returns general information about a file or symbolic link.
     *
     * Returns an array on success and false otherwise.
     *
     * @param string $filename
     * @return array|false
     */
    public function lstat($filename)
    {
        return $this->callOrFalse('lstat', $filename);
    }

    /**
     * Truncates a file to a given length
     *
     * @param string $filename
     * @param int $new_size
     * @return bool
     */
    public function truncate($filename, $new_size)
    {
        return $this->callOrFalse('truncate', $filename, $new_size) !== false;
    }

    /**
     * Sets access and modification time of file.
     *
     * If the file does not exist, it will be created.
     *
     * @param string $filename
     * @param int $time
     * @param int $atime
     * @throws \UnexpectedValueException on receipt of unexpected packets
     * @return bool
     */
    public function touch($filename, $time = null, $atime = null)
    {
        if ($this->callOrFalse('touch', $filename, $time, $atime) !== false) {
            return true;
        }
        // phpseclib 4.0 gives up when the file already exists whereas phpseclib 3.0 then set its times
        if ($this->callOrFalse('file_exists', $filename) !== true) {
            return false;
        }
        return $this->callOrFalse('setTimes', $filename, $time, $atime) === true;
    }

    /**
     * Changes file or directory owner
     *
     * $uid should be an int for SFTPv3 and a string for SFTPv4+. Ideally the string
     * would be of the form "user@dns_domain" but it does not need to be.
     * `$sftp->getSupportedVersions()['version']` will return the specific version
     * that's being used.
     *
     * Returns true on success or false on error.
     *
     * @param string $filename
     * @param int|string $uid
     * @param bool $recursive
     * @return bool
     */
    public function chown($filename, $uid, $recursive = false)
    {
        return $this->callOrFalse('chown', $filename, $uid, (bool) $recursive) !== false;
    }

    /**
     * Changes file or directory group
     *
     * $gid should be an int for SFTPv3 and a string for SFTPv4+. Ideally the string
     * would be of the form "user@dns_domain" but it does not need to be.
     * `$sftp->getSupportedVersions()['version']` will return the specific version
     * that's being used.
     *
     * Returns true on success or false on error.
     *
     * @param string $filename
     * @param int|string $gid
     * @param bool $recursive
     * @return bool
     */
    public function chgrp($filename, $gid, $recursive = false)
    {
        return $this->callOrFalse('chgrp', $filename, $gid, (bool) $recursive) !== false;
    }

    /**
     * Set permissions on a file.
     *
     * Returns the new file permissions on success or false on error.
     * If $recursive is true than this just returns true or false.
     *
     * @param int $mode
     * @param string $filename
     * @param bool $recursive
     * @throws \UnexpectedValueException on receipt of unexpected packets
     * @return mixed
     * @changed in phpseclib 4.0.0
     */
    public function chmod($mode, $filename, $recursive = false)
    {
        // phpseclib 3.0 accepted the parameters in either order
        if (is_string($mode) && is_int($filename)) {
            $temp = $mode;
            $mode = $filename;
            $filename = $temp;
        }

        if ($this->callOrFalse('chmod', $filename, $mode, (bool) $recursive) === false) {
            return false;
        }
        if ($recursive) {
            return true;
        }

        $attrs = $this->callOrFalse('stat', $filename);
        return $attrs === false ? false : $attrs['mode'];
    }

    /**
     * Return the target of a symbolic link
     *
     * @param string $link
     * @throws \UnexpectedValueException on receipt of unexpected packets
     * @return mixed
     */
    public function readlink($link)
    {
        return $this->callOrFalse('readlink', $link);
    }

    /**
     * Create a symlink
     *
     * symlink() creates a symbolic link to the existing target with the specified name link.
     *
     * @param string $target
     * @param string $link
     * @throws \UnexpectedValueException on receipt of unexpected packets
     * @return bool
     */
    public function symlink($target, $link)
    {
        return $this->callOrFalse('symlink', $target, $link) !== false;
    }

    /**
     * Creates a directory.
     *
     * @param string $dir
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public function mkdir($dir, $mode = -1, $recursive = false)
    {
        // phpseclib 4.0 does nothing when the directory already exists whereas phpseclib 3.0 sent the request
        // anyway and returned false when the server refused it. SFTPv3 has no "already exists" status so
        // servers like OpenSSH's reply with a generic failure
        if ($this->callOrFalse('is_dir', $dir) === true) {
            $this->sftp_errors[] = $this->v4->getNegotiatedVersion() > 3 ?
                'NET_SFTP_STATUS_FILE_ALREADY_EXISTS: File already exists' :
                'NET_SFTP_STATUS_FAILURE: Failure';
            return false;
        }
        return $this->callOrFalse('mkdir', $dir, $mode, (bool) $recursive) !== false;
    }

    /**
     * Removes a directory.
     *
     * @param string $dir
     * @throws \UnexpectedValueException on receipt of unexpected packets
     * @return bool
     */
    public function rmdir($dir)
    {
        return $this->callOrFalse('rmdir', $dir) !== false;
    }

    /**
     * Uploads a file to the SFTP server.
     *
     * By default, \phpseclib3\Net\SFTP::put() does not read from the local filesystem.  $data is dumped directly into $remote_file.
     * So, for example, if you set $data to 'filename.ext' and then do \phpseclib3\Net\SFTP::get(), you will get a file, twelve bytes
     * long, containing 'filename.ext' as its contents.
     *
     * Setting $mode to self::SOURCE_LOCAL_FILE will change the above behavior.  With self::SOURCE_LOCAL_FILE, $remote_file will
     * contain as many bytes as filename.ext does on your local filesystem.  If your filename.ext is 1MB then that is how
     * large $remote_file will be, as well.
     *
     * Setting $mode to self::SOURCE_CALLBACK will use $data as callback function, which gets only one parameter -- number
     * of bytes to return, and returns a string if there is some data or null if there is no more data
     *
     * If $data is a resource then it'll be used as a resource instead.
     *
     * Currently, only binary mode is supported.  As such, if the line endings need to be adjusted, you will need to take
     * care of that, yourself.
     *
     * $mode can take an additional two parameters - self::RESUME and self::RESUME_START. These are bitwise AND'd with
     * $mode. So if you want to resume upload of a 300mb file on the local file system you'd set $mode to the following:
     *
     * self::SOURCE_LOCAL_FILE | self::RESUME
     *
     * If you wanted to simply append the full contents of a local file to the full contents of a remote file you'd replace
     * self::RESUME with self::RESUME_START.
     *
     * If $mode & (self::RESUME | self::RESUME_START) then self::RESUME_START will be assumed.
     *
     * $start and $local_start give you more fine grained control over this process and take precident over self::RESUME
     * when they're non-negative. ie. $start could let you write at the end of a file (like self::RESUME) or in the middle
     * of one. $local_start could let you start your reading from the end of a file (like self::RESUME_START) or in the
     * middle of one.
     *
     * Setting $local_start to > 0 or $mode | self::RESUME_START doesn't do anything unless $mode | self::SOURCE_LOCAL_FILE.
     *
     * {@internal ASCII mode for SFTPv4/5/6 can be supported by adding a new function - \phpseclib3\Net\SFTP::setMode().}
     *
     * @param string $remote_file
     * @param string|resource $data
     * @param int $mode
     * @param int $start
     * @param int $local_start
     * @param callable|null $progressCallback
     * @throws \UnexpectedValueException on receipt of unexpected packets
     * @throws \BadFunctionCallException if you're uploading via a callback and the callback function is invalid
     * @throws FileNotFoundException if you're uploading via a file and the file doesn't exist
     * @return bool
     */
    public function put($remote_file, $data, $mode = self::SOURCE_STRING, $start = -1, $local_start = -1, $progressCallback = null)
    {
        if ($mode & self::SOURCE_CALLBACK && is_callable($data)) {
            $data = \Closure::fromCallable($data);
        }
        // phpseclib 3.0 ignored progress callbacks that weren't callable
        $progressCallback = is_callable($progressCallback) ? \Closure::fromCallable($progressCallback) : null;
        return $this->callOrFalse('put', $remote_file, $data, $mode, $start, $local_start, $progressCallback) !== false;
    }

    /**
     * Downloads a file from the SFTP server.
     *
     * Returns a string containing the contents of $remote_file if $local_file is left undefined or a boolean false if
     * the operation was unsuccessful.  If $local_file is defined, returns true or false depending on the success of the
     * operation.
     *
     * $offset and $length can be used to download files in chunks.
     *
     * @param string $remote_file
     * @param string|bool|resource|callable $local_file
     * @param int $offset
     * @param int $length
     * @param callable|null $progressCallback
     * @throws \UnexpectedValueException on receipt of unexpected packets
     * @return string|bool
     */
    public function get($remote_file, $local_file = false, $offset = 0, $length = -1, $progressCallback = null)
    {
        // phpseclib 3.0 ignored progress callbacks that weren't callable
        $progressCallback = is_callable($progressCallback) ? \Closure::fromCallable($progressCallback) : null;
        // phpseclib 3.0 passed the data to any callable whereas phpseclib 4.0 only does that for closures.
        // strings are file names
        if (!is_string($local_file) && is_callable($local_file)) {
            $local_file = \Closure::fromCallable($local_file);
        }
        $result = $this->callOrFalse('get', $remote_file, $local_file === false ? null : $local_file, $offset, $length, $progressCallback);
        return $result === null ? true : $result;
    }

    /**
     * Deletes a file on the SFTP server.
     *
     * @param string $path
     * @param bool $recursive
     * @return bool
     * @throws \UnexpectedValueException on receipt of unexpected packets
     */
    public function delete($path, $recursive = true)
    {
        if ($this->callOrFalse('delete', $path, (bool) $recursive) === false) {
            return false;
        }
        // phpseclib 4.0 carries on when it can't list a directory that it's deleting whereas phpseclib 3.0
        // returned false, which it did for paths that don't exist, too
        foreach ($this->v4_errors as $error) {
            if (substr($error, 0, 8) == 'OPENDIR ') {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks whether a file or directory exists
     *
     * @param string $path
     * @return bool
     */
    public function file_exists($path)
    {
        return $this->callOrFalse('file_exists', $path);
    }

    /**
     * Tells whether the filename is a directory
     *
     * @param string $path
     * @return bool
     */
    public function is_dir($path)
    {
        return $this->callOrFalse('is_dir', $path);
    }

    /**
     * Tells whether the filename is a regular file
     *
     * @param string $path
     * @return bool
     */
    public function is_file($path)
    {
        return $this->callOrFalse('is_file', $path);
    }

    /**
     * Tells whether the filename is a symbolic link
     *
     * @param string $path
     * @return bool
     */
    public function is_link($path)
    {
        return $this->callOrFalse('is_link', $path);
    }

    /**
     * Tells whether a file exists and is readable
     *
     * @param string $path
     * @return bool
     */
    public function is_readable($path)
    {
        return $this->callOrFalse('is_readable', $path);
    }

    /**
     * Tells whether the filename is writable
     *
     * @param string $path
     * @return bool
     */
    public function is_writable($path)
    {
        return $this->callOrFalse('is_writable', $path);
    }

    /**
     * Tells whether the filename is writeable
     *
     * Alias of is_writable
     *
     * @param string $path
     * @return bool
     */
    public function is_writeable($path)
    {
        return $this->callOrFalse('is_writeable', $path);
    }

    /**
     * Gets last access time of file
     *
     * @param string $path
     * @return mixed
     */
    public function fileatime($path)
    {
        return $this->callOrFalse('fileatime', $path);
    }

    /**
     * Gets file modification time
     *
     * @param string $path
     * @return mixed
     */
    public function filemtime($path)
    {
        return $this->callOrFalse('filemtime', $path);
    }

    /**
     * Gets file permissions
     *
     * @param string $path
     * @return mixed
     */
    public function fileperms($path)
    {
        return $this->callOrFalse('fileperms', $path);
    }

    /**
     * Gets file owner
     *
     * @param string $path
     * @return mixed
     */
    public function fileowner($path)
    {
        return $this->callOrFalse('fileowner', $path);
    }

    /**
     * Gets file group
     *
     * @param string $path
     * @return mixed
     */
    public function filegroup($path)
    {
        return $this->callOrFalse('filegroup', $path);
    }

    /**
     * Gets file size
     *
     * @param string $path
     * @param bool $recursive
     * @return mixed
     */
    public function filesize($path, $recursive = false)
    {
        return $this->callOrFalse('filesize', $path, (bool) $recursive);
    }

    /**
     * Gets file type
     *
     * @param string $path
     * @return string|false
     */
    public function filetype($path)
    {
        return $this->callOrFalse('filetype', $path);
    }

    /**
     * Renames a file or a directory on the SFTP server.
     *
     * If the file already exists this will return false
     *
     * @param string $oldname
     * @param string $newname
     * @return bool
     * @throws \UnexpectedValueException on receipt of unexpected packets
     */
    public function rename($oldname, $newname)
    {
        return $this->callOrFalse('rename', $oldname, $newname) !== false;
    }

    /**
     * Returns a log of the packets that have been sent and received.
     *
     * Returns a string if NET_SFTP_LOGGING == self::LOG_COMPLEX, an array if NET_SFTP_LOGGING == self::LOG_SIMPLE and false if !defined('NET_SFTP_LOGGING')
     *
     * @return array|string|false
     */
    public function getSFTPLog()
    {
        return $this->call('getSFTPLog') ?? false;
    }

    /**
     * Returns all errors on the SFTP layer
     *
     * @return array
     * @removed in phpseclib 4.0.0
     */
    public function getSFTPErrors()
    {
        $this->collectSFTPErrors();
        return $this->sftp_errors;
    }

    /**
     * Returns the last error on the SFTP layer
     *
     * @return string
     * @removed in phpseclib 4.0.0
     */
    public function getLastSFTPError()
    {
        $this->collectSFTPErrors();
        return count($this->sftp_errors) ? $this->sftp_errors[count($this->sftp_errors) - 1] : '';
    }

    /**
     * Get supported SFTP versions
     *
     * @return array
     */
    public function getSupportedVersions()
    {
        return $this->callOrFalse('getSupportedVersions');
    }

    /**
     * Get supported SFTP extensions
     *
     * @return array
     */
    public function getSupportedExtensions()
    {
        return $this->callOrFalse('getSupportedExtensions');
    }

    /**
     * Get supported SFTP versions
     *
     * @return int|false
     */
    public function getNegotiatedVersion()
    {
        return $this->callOrFalse('getNegotiatedVersion');
    }

    /**
     * Set preferred version
     *
     * If you're preferred version isn't supported then the highest supported
     * version of SFTP will be utilized. Set to null or false or int(0) to
     * unset the preferred version
     *
     * @param int $version
     */
    public function setPreferredVersion($version)
    {
        $this->call('setPreferredVersion', $version);
    }

    /**
     * Enable Date Preservation
     */
    public function enableDatePreservation()
    {
        $this->call('enableDatePreservation');
    }

    /**
     * Disable Date Preservation
     */
    public function disableDatePreservation()
    {
        $this->call('disableDatePreservation');
    }

    /**
     * Copy
     *
     * This method (currently) only works if the copy-data extension is available
     *
     * @param string $oldname
     * @param string $newname
     * @return bool
     */
    public function copy($oldname, $newname)
    {
        return $this->callOrFalse('copy', $oldname, $newname) !== false;
    }

    /**
     * POSIX Rename
     *
     * Where rename() fails "if there already exists a file with the name specified by newpath"
     * (draft-ietf-secsh-filexfer-02#section-6.5), posix_rename() overwrites the existing file in an atomic fashion.
     * ie. "there is no observable instant in time where the name does not refer to either the old or the new file"
     * (draft-ietf-secsh-filexfer-13#page-39).
     *
     * @param string $oldname
     * @param string $newname
     * @return bool
     */
    public function posix_rename($oldname, $newname)
    {
        return $this->callOrFalse('posix_rename', $oldname, $newname) !== false;
    }

    /**
     * Returns general information about a file system.
     *
     * The function statvfs() returns information about a mounted filesystem.
     * @see https://man7.org/linux/man-pages/man3/statvfs.3.html
     *
     * @param string $path
     * @return false|array{bsize: int, frsize: int, blocks: int, bfree: int, bavail: int, files: int, ffree: int, favail: int, fsid: int, flag: int, namemax: int}
     */
    public function statvfs($path)
    {
        return $this->callOrFalse('statvfs', $path);
    }

    public function hardlink($oldpath, $newpath)
    {
        return $this->callOrFalse('hardlink', $oldpath, $newpath) !== false;
    }

    /**
     * Creates the phpseclib 4.0 object
     *
     * @param mixed $host
     * @param int $port
     * @param int|float $timeout
     * @return SFTPEngine
     */
    protected function createV4($host, $port, $timeout)
    {
        return new SFTPEngine($host, (int) $port, self::intTimeout($timeout));
    }

    /**
     * Calls a method of the phpseclib 4.0 object, returning false when phpseclib 3.0 did
     *
     * phpseclib 3.0 returned false when the server responded with an error status, which it logged, when
     * a local file couldn't be opened and when the method was called before logging in. phpseclib 4.0 throws
     * exceptions instead.
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    protected function callOrFalse($method, ...$args)
    {
        $this->v4_errors = [];
        try {
            return Convert::toV3($this->v4->$method(...Convert::toV4($args)));
        } catch (FileSystemException $e) {
            if (!preg_match('~^Error from server \((SSH_FX_\w+)\)(?:: (.*))?$~s', $e->getMessage(), $matches)) {
                // a local error. phpseclib 3.0 threw an exception when the file to upload didn't exist and
                // returned false when fopen() failed
                if (preg_match('~ is not a valid file$~', $e->getMessage())) {
                    throw Convert::exception($e);
                }
                return false;
            }
            $error = 'NET_SFTP_STATUS_' . StatusCode::getConstantNameByValue($e->getCode());
            $this->sftp_errors[] = isset($matches[2]) ? "$error: $matches[2]" : $error;
            return false;
        } catch (InvalidStateException $e) {
            // InvalidArgumentException and BadMethodCallException extend InvalidStateException so only
            // "Function should not be called before you've logged in" becomes false
            if (!$this->v4->isAuthenticated()) {
                return false;
            }
            throw Convert::exception($e);
        } catch (BaseException $e) {
            // phpseclib 3.0 also returned false when it couldn't start the SFTP subsystem: when the server
            // refused it, when opening the channel timed out and when the server's SFTP version is unsupported
            $initFailure = '~^(Received failure message when trying to open SFTP channel|Attempt to \(re\)connect timed out|'
                . 'The only SFTP versions phpseclib supports are )~';
            if (preg_match($initFailure, $e->getMessage())) {
                return false;
            }
            throw Convert::exception($e);
        } finally {
            $this->collectSFTPErrors();
        }
    }

    /**
     * Adds the errors that phpseclib 4.0 logged instead of throwing an exception, as phpseclib 3.0 did
     *
     * phpseclib 4.0 logs the errors that it encounters while it deletes or chmods directories recursively
     */
    private function collectSFTPErrors()
    {
        $this->v4_errors = $this->v4->getErrors();
        foreach ($this->v4_errors as $error) {
            // eg. "OPEN /path (SSH_FX_NO_SUCH_FILE): No such file"
            $this->sftp_errors[] = preg_replace('~^.*? \(SSH_FX_(\w+)\)~s', 'NET_SFTP_STATUS_$1', $error);
        }
    }
}
