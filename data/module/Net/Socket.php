<?php
/**
 * Net_Socket
 *
 * PHP Version 4
 *
 * Copyright (c) 1997-2003 The PHP Group
 *
 * This source file is subject to version 2.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available at through the world-wide-web at
 * http://www.php.net/license/2_02.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * Authors: Stig Bakken <ssb@php.net>
 *          Chuck Hagenbuch <chuck@horde.org>
 *
 * @category  Net
 *
 * @author    Stig Bakken <ssb@php.net>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @copyright 1997-2003 The PHP Group
 * @license   http://www.php.net/license/2_02.txt PHP 2.02
 *
 * @version   CVS: $Id$
 *
 * @see      http://pear.php.net/packages/Net_Socket
 */
require_once 'PEAR.php';

define('NET_SOCKET_READ', 1);
define('NET_SOCKET_WRITE', 2);
define('NET_SOCKET_ERROR', 4);

/**
 * Generalized Socket class.
 *
 * @category  Net
 *
 * @author    Stig Bakken <ssb@php.net>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @copyright 1997-2003 The PHP Group
 * @license   http://www.php.net/license/2_02.txt PHP 2.02
 *
 * @see      http://pear.php.net/packages/Net_Socket
 */
class Net_Socket extends PEAR
{
    /**
     * Socket file pointer.
     *
     * @var resource
     */
    public $fp = null;

    /**
     * Whether the socket is blocking. Defaults to true.
     *
     * @var bool
     */
    public $blocking = true;

    /**
     * Whether the socket is persistent. Defaults to false.
     *
     * @var bool
     */
    public $persistent = false;

    /**
     * The IP address to connect to.
     *
     * @var string
     */
    public $addr = '';

    /**
     * The port number to connect to.
     *
     * @var int
     */
    public $port = 0;

    /**
     * Number of seconds to wait on socket connections before assuming
     * there's no more data. Defaults to no timeout.
     *
     * @var int
     */
    public $timeout = false;

    /**
     * Number of bytes to read at a time in readLine() and
     * readAll(). Defaults to 2048.
     *
     * @var int
     */
    public $lineLength = 2048;

    /**
     * The string to use as a newline terminator. Usually "\r\n" or "\n".
     *
     * @var string
     */
    public $newline = "\r\n";

    /**
     * Connect to the specified port. If called when the socket is
     * already connected, it disconnects and connects again.
     *
     * @param string  $addr       IP address or host name.
     * @param int $port       TCP port number.
     * @param bool $persistent (optional) Whether the connection is
     *                            persistent (kept open between requests
     *                            by the web server).
     * @param int $timeout    (optional) How long to wait for data.
     * @param array   $options    See options for stream_context_create.
     *
     * @return bool|PEAR_Error  True on success or a PEAR_Error on failure.
     */
    public function connect($addr, $port = 0, $persistent = null,
        $timeout = null, $options = null)
    {
        if (is_resource($this->fp)) {
            @fclose($this->fp);
            $this->fp = null;
        }

        if (!$addr) {
            return $this->raiseError('$addr cannot be empty');
        } elseif (strspn($addr, '.0123456789') == strlen($addr) ||
                  strstr($addr, '/') !== false) {
            $this->addr = $addr;
        } else {
            $this->addr = @gethostbyname($addr);
        }

        $this->port = $port % 65536;

        if ($persistent !== null) {
            $this->persistent = $persistent;
        }

        if ($timeout !== null) {
            $this->timeout = $timeout;
        }

        $openfunc = $this->persistent ? 'pfsockopen' : 'fsockopen';
        $errno = 0;
        $errstr = '';

        $old_track_errors = @ini_set('track_errors', 1);

        if ($options && function_exists('stream_context_create')) {
            if ($this->timeout) {
                $timeout = $this->timeout;
            } else {
                $timeout = 0;
            }
            $context = stream_context_create($options);

            // Since PHP 5 fsockopen doesn't allow context specification
            if (function_exists('stream_socket_client')) {
                $flags = STREAM_CLIENT_CONNECT;

                if ($this->persistent) {
                    $flags = STREAM_CLIENT_PERSISTENT;
                }

                $addr = $this->addr.':'.$this->port;
                $fp = stream_socket_client($addr, $errno, $errstr,
                    $timeout, $flags, $context);
            } else {
                $fp = @$openfunc($this->addr, $this->port, $errno,
                    $errstr, $timeout, $context);
            }
        } else {
            if ($this->timeout) {
                $fp = @$openfunc($this->addr, $this->port, $errno,
                    $errstr, $this->timeout);
            } else {
                $fp = @$openfunc($this->addr, $this->port, $errno, $errstr);
            }
        }

        if (!$fp) {
            $error = error_get_last();
            if ($errno == 0 && !strlen($errstr) && isset($error)) {
                $errstr = $error['message'];
            }
            @ini_set('track_errors', $old_track_errors);

            return $this->raiseError($errstr, $errno);
        }

        @ini_set('track_errors', $old_track_errors);
        $this->fp = $fp;

        return $this->setBlocking($this->blocking);
    }

    /**
     * Disconnects from the peer, closes the socket.
     *
     * @return mixed true on success or a PEAR_Error instance otherwise
     */
    public function disconnect()
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        @fclose($this->fp);
        $this->fp = null;

        return true;
    }

    /**
     * Set the newline character/sequence to use.
     *
     * @param string $newline  Newline character(s)
     *
     * @return bool True
     */
    public function setNewline($newline)
    {
        $this->newline = $newline;

        return true;
    }

    /**
     * Find out if the socket is in blocking mode.
     *
     * @return bool  The current blocking mode.
     */
    public function isBlocking()
    {
        return $this->blocking;
    }

    /**
     * Sets whether the socket connection should be blocking or
     * not. A read call to a non-blocking socket will return immediately
     * if there is no data available, whereas it will block until there
     * is data for blocking sockets.
     *
     * @param bool $mode True for blocking sockets, false for nonblocking.
     *
     * @return mixed true on success or a PEAR_Error instance otherwise
     */
    public function setBlocking($mode)
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        $this->blocking = $mode;
        stream_set_blocking($this->fp, (int) $this->blocking);

        return true;
    }

    /**
     * Sets the timeout value on socket descriptor,
     * expressed in the sum of seconds and microseconds
     *
     * @param int $seconds      Seconds.
     * @param int $microseconds Microseconds.
     *
     * @return mixed true on success or a PEAR_Error instance otherwise
     */
    public function setTimeout($seconds, $microseconds)
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        return stream_set_timeout($this->fp, $seconds, $microseconds);
    }

    /**
     * Sets the file buffering size on the stream.
     * See php's stream_set_write_buffer for more information.
     *
     * @param int $size Write buffer size.
     *
     * @return mixed on success or an PEAR_Error object otherwise
     */
    public function setWriteBuffer($size)
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        $returned = stream_set_write_buffer($this->fp, $size);
        if ($returned == 0) {
            return true;
        }

        return $this->raiseError('Cannot set write buffer.');
    }

    /**
     * Returns information about an existing socket resource.
     * Currently returns four entries in the result array:
     *
     * <p>
     * timed_out (bool) - The socket timed out waiting for data<br>
     * blocked (bool) - The socket was blocked<br>
     * eof (bool) - Indicates EOF event<br>
     * unread_bytes (int) - Number of bytes left in the socket buffer<br>
     * </p>
     *
     * @return mixed Array containing information about existing socket
     *               resource or a PEAR_Error instance otherwise
     */
    public function getStatus()
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        return stream_get_meta_data($this->fp);
    }

    /**
     * Get a specified line of data
     *
     * @param int $size ??
     *
     * @return $size bytes of data from the socket, or a PEAR_Error if
     *         not connected.
     */
    public function gets($size = null)
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        if (is_null($size)) {
            return @fgets($this->fp);
        } else {
            return @fgets($this->fp, $size);
        }
    }

    /**
     * Read a specified amount of data. This is guaranteed to return,
     * and has the added benefit of getting everything in one fread()
     * chunk; if you know the size of the data you're getting
     * beforehand, this is definitely the way to go.
     *
     * @param int $size The number of bytes to read from the socket.
     *
     * @return $size bytes of data from the socket, or a PEAR_Error if
     *         not connected.
     */
    public function read($size)
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        return @fread($this->fp, $size);
    }

    /**
     * Write a specified amount of data.
     *
     * @param string  $data      Data to write.
     * @param int $blocksize Amount of data to write at once.
     *                           NULL means all at once.
     *
     * @return mixed If the socket is not connected, returns an instance of
     *               PEAR_Error
     *               If the write succeeds, returns the number of bytes written
     *               If the write fails, returns false.
     */
    public function write($data, $blocksize = null)
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        if (is_null($blocksize) && !OS_WINDOWS) {
            return @fwrite($this->fp, $data);
        } else {
            if (is_null($blocksize)) {
                $blocksize = 1024;
            }

            $pos = 0;
            $size = strlen($data);
            while ($pos < $size) {
                $written = @fwrite($this->fp, substr($data, $pos, $blocksize));
                if (!$written) {
                    return $written;
                }
                $pos += $written;
            }

            return $pos;
        }
    }

    /**
     * Write a line of data to the socket, followed by a trailing newline.
     *
     * @param string $data Data to write
     *
     * @return mixed fputs result, or an error
     */
    public function writeLine($data)
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        return fwrite($this->fp, $data.$this->newline);
    }

    /**
     * Tests for end-of-file on a socket descriptor.
     *
     * Also returns true if the socket is disconnected.
     *
     * @return bool
     */
    public function eof()
    {
        return !is_resource($this->fp) || feof($this->fp);
    }

    /**
     * Reads a byte of data
     *
     * @return 1 byte of data from the socket, or a PEAR_Error if
     *         not connected.
     */
    public function readByte()
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        return ord(@fread($this->fp, 1));
    }

    /**
     * Reads a word of data
     *
     * @return 1 word of data from the socket, or a PEAR_Error if
     *         not connected.
     */
    public function readWord()
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        $buf = @fread($this->fp, 2);

        return ord($buf[0]) + (ord($buf[1]) << 8);
    }

    /**
     * Reads an int of data
     *
     * @return int  1 int of data from the socket, or a PEAR_Error if
     *                  not connected.
     */
    public function readInt()
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        $buf = @fread($this->fp, 4);

        return ord($buf[0]) + (ord($buf[1]) << 8) +
                (ord($buf[2]) << 16) + (ord($buf[3]) << 24);
    }

    /**
     * Reads a zero-terminated string of data
     *
     * @return string, or a PEAR_Error if
     *         not connected.
     */
    public function readString()
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        $string = '';
        while (($char = @fread($this->fp, 1)) != "\x00") {
            $string .= $char;
        }

        return $string;
    }

    /**
     * Reads an IP Address and returns it in a dot formatted string
     *
     * @return Dot formatted string, or a PEAR_Error if
     *         not connected.
     */
    public function readIPAddress()
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        $buf = @fread($this->fp, 4);

        return sprintf('%d.%d.%d.%d', ord($buf[0]), ord($buf[1]),
            ord($buf[2]), ord($buf[3]));
    }

    /**
     * Read until either the end of the socket or a newline, whichever
     * comes first. Strips the trailing newline from the returned data.
     *
     * @return All available data up to a newline, without that
     *         newline, or until the end of the socket, or a PEAR_Error if
     *         not connected.
     */
    public function readLine()
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        $line = '';

        $timeout = time() + $this->timeout;

        while (!feof($this->fp) && (!$this->timeout || time() < $timeout)) {
            $line .= @fgets($this->fp, $this->lineLength);
            if (substr($line, -1) == "\n") {
                return rtrim($line, $this->newline);
            }
        }

        return $line;
    }

    /**
     * Read until the socket closes, or until there is no more data in
     * the inner PHP buffer. If the inner buffer is empty, in blocking
     * mode we wait for at least 1 byte of data. Therefore, in
     * blocking mode, if there is no data at all to be read, this
     * function will never exit (unless the socket is closed on the
     * remote end).
     *
     * @return string  All data until the socket closes, or a PEAR_Error if
     *                 not connected.
     */
    public function readAll()
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        $data = '';
        while (!feof($this->fp)) {
            $data .= @fread($this->fp, $this->lineLength);
        }

        return $data;
    }

    /**
     * Runs the equivalent of the select() system call on the socket
     * with a timeout specified by tv_sec and tv_usec.
     *
     * @param int $state   Which of read/write/error to check for.
     * @param int $tv_sec  Number of seconds for timeout.
     * @param int $tv_usec Number of microseconds for timeout.
     *
     * @return false if select fails, integer describing which of read/write/error
     *         are ready, or PEAR_Error if not connected.
     */
    public function select($state, $tv_sec, $tv_usec = 0)
    {
        if (!is_resource($this->fp)) {
            return $this->raiseError('not connected');
        }

        $read = null;
        $write = null;
        $except = null;
        if ($state & NET_SOCKET_READ) {
            $read[] = $this->fp;
        }
        if ($state & NET_SOCKET_WRITE) {
            $write[] = $this->fp;
        }
        if ($state & NET_SOCKET_ERROR) {
            $except[] = $this->fp;
        }
        if (false === ($sr = stream_select($read, $write, $except,
            $tv_sec, $tv_usec))) {
            return false;
        }

        $result = 0;
        if (count($read)) {
            $result |= NET_SOCKET_READ;
        }
        if (count($write)) {
            $result |= NET_SOCKET_WRITE;
        }
        if (count($except)) {
            $result |= NET_SOCKET_ERROR;
        }

        return $result;
    }

    /**
     * Turns encryption on/off on a connected socket.
     *
     * @param bool    $enabled Set this parameter to true to enable encryption
     *                         and false to disable encryption.
     * @param int $type    Type of encryption. See stream_socket_enable_crypto()
     *                         for values.
     *
     * @see    http://se.php.net/manual/en/function.stream-socket-enable-crypto.php
     *
     * @return false on error, true on success and 0 if there isn't enough data
     *         and the user should try again (non-blocking sockets only).
     *         A PEAR_Error object is returned if the socket is not
     *         connected
     */
    public function enableCrypto($enabled, $type)
    {
        if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
            if (!is_resource($this->fp)) {
                return $this->raiseError('not connected');
            }
            stream_context_set_option($this->fp, 'ssl', 'verify_peer_name', false);

            return @stream_socket_enable_crypto($this->fp, $enabled, $type);
        } else {
            $msg = 'Net_Socket::enableCrypto() requires php version >= 5.1.0';

            return $this->raiseError($msg);
        }
    }
}
