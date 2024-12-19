<?php

/* vim: set expandtab softtabstop=4 tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Chuck Hagenbuch <chuck@horde.org>                           |
// |          Jon Parise <jon@php.net>                                    |
// |          Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>      |
// +----------------------------------------------------------------------+
//
// $Id: SMTP.php 304535 2010-10-20 06:48:06Z jon $

require_once 'PEAR.php';
require_once 'Net/Socket.php';

/**
 * Provides an implementation of the SMTP protocol using PEAR's
 * Net_Socket:: class.
 *
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @author  Jon Parise <jon@php.net>
 * @author  Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>
 *
 * @example basic.php   A basic implementation of the Net_SMTP package.
 */
class Net_SMTP
{
    /**
     * The server to connect to.
     *
     * @var string
     */
    public $host = 'localhost';

    /**
     * The port to connect to.
     *
     * @var int
     */
    public $port = 25;

    /**
     * The value to give when sending EHLO or HELO.
     *
     * @var string
     */
    public $localhost = 'localhost';

    /**
     * List of supported authentication methods, in preferential order.
     *
     * @var array
     */
    public $auth_methods = ['DIGEST-MD5', 'CRAM-MD5', 'LOGIN', 'PLAIN'];

    /**
     * Use SMTP command pipelining (specified in RFC 2920) if the SMTP
     * server supports it.
     *
     * When pipeling is enabled, rcptTo(), mailFrom(), sendFrom(),
     * somlFrom() and samlFrom() do not wait for a response from the
     * SMTP server but return immediately.
     *
     * @var bool
     */
    public $pipelining = false;

    /**
     * Number of pipelined commands.
     *
     * @var int
     */
    public $_pipelined_commands = 0;

    /**
     * Should debugging output be enabled?
     *
     * @var bool
     */
    public $_debug = false;

    /**
     * Debug output handler.
     *
     * @var callable
     */
    public $_debug_handler;

    /**
     * The socket resource being used to connect to the SMTP server.
     *
     * @var resource
     */
    public $_socket;

    /**
     * The most recent server response code.
     *
     * @var int
     */
    public $_code = -1;

    /**
     * The most recent server response arguments.
     *
     * @var array
     */
    public $_arguments = [];

    /**
     * Stores the SMTP server's greeting string.
     *
     * @var string
     */
    public $_greeting;

    /**
     * Stores detected features of the SMTP server.
     *
     * @var array
     */
    public $_esmtp = [];

    /**
     * Instantiates a new Net_SMTP object, overriding any defaults
     * with parameters that are passed in.
     *
     * If you have SSL support in PHP, you can connect to a server
     * over SSL using an 'ssl://' prefix:
     *
     *   // 465 is a common smtps port.
     *   $smtp = new Net_SMTP('ssl://mail.host.com', 465);
     *   $smtp->connect();
     *
     * @param string  $host       The server to connect to.
     * @param int $port       The port to connect to.
     * @param string  $localhost  The value to give when sending EHLO or HELO.
     * @param bool $pipeling   Use SMTP command pipelining
     *
     * @since   1.0
     */
    public function __construct($host = null, $port = null, $localhost = null, $pipelining = false)
    {
        if (isset($host)) {
            $this->host = $host;
        }
        if (isset($port)) {
            $this->port = $port;
        }
        if (isset($localhost)) {
            $this->localhost = $localhost;
        }
        $this->pipelining = $pipelining;

        $this->_socket = new Net_Socket();

        /* Include the Auth_SASL package.  If the package is not
         * available, we disable the authentication methods that
         * depend upon it. */
        if ((@include_once 'Auth/SASL.php') === false) {
            $pos = array_search('DIGEST-MD5', $this->auth_methods);
            unset($this->auth_methods[$pos]);
            $pos = array_search('CRAM-MD5', $this->auth_methods);
            unset($this->auth_methods[$pos]);
        }
    }

    /**
     * Set the value of the debugging flag.
     *
     * @param   bool $debug      New value for the debugging flag.
     *
     * @since   1.1.0
     */
    public function setDebug($debug, $handler = null)
    {
        $this->_debug = $debug;
        $this->_debug_handler = $handler;
    }

    /**
     * Write the given debug text to the current debug output handler.
     *
     * @param   string  $message    Debug mesage text.
     *
     * @since   1.3.3
     */
    public function _debug($message)
    {
        if ($this->_debug) {
            if ($this->_debug_handler) {
                call_user_func_array($this->_debug_handler,
                    [&$this, $message]);
            } else {
                echo "DEBUG: $message\n";
            }
        }
    }

    /**
     * Send the given string of data to the server.
     *
     * @param   string  $data       The string of data to send.
     *
     * @return  mixed   True on success or a PEAR_Error object on failure.
     *
     * @since   1.1.0
     */
    public function _send($data)
    {
        $this->_debug("Send: $data");

        $error = $this->_socket->write($data);
        if ($error === false || PEAR::isError($error)) {
            $msg = ($error) ? $error->getMessage() : 'unknown error';

            return PEAR::raiseError("Failed to write to socket: $msg");
        }

        return true;
    }

    /**
     * Send a command to the server with an optional string of
     * arguments.  A carriage return / linefeed (CRLF) sequence will
     * be appended to each command string before it is sent to the
     * SMTP server - an error will be thrown if the command string
     * already contains any newline characters. Use _send() for
     * commands that must contain newlines.
     *
     * @param   string  $command    The SMTP command to send to the server.
     * @param   string  $args       A string of optional arguments to append
     *                              to the command.
     *
     * @return  mixed   The result of the _send() call.
     *
     * @since   1.1.0
     */
    public function _put($command, $args = '')
    {
        if (!empty($args)) {
            $command .= ' '.$args;
        }

        if (strcspn($command, "\r\n") !== strlen($command)) {
            return PEAR::raiseError('Commands cannot contain newlines');
        }

        return $this->_send($command."\r\n");
    }

    /**
     * Read a reply from the SMTP server.  The reply consists of a response
     * code and a response message.
     *
     * @param   mixed   $valid      The set of valid response codes.  These
     *                              may be specified as an array of integer
     *                              values or as a single integer value.
     * @param   bool    $later      Do not parse the response now, but wait
     *                              until the last command in the pipelined
     *                              command group
     *
     * @return  mixed   True if the server returned a valid response code or
     *                  a PEAR_Error object is an error condition is reached.
     *
     * @since   1.1.0
     * @see     getResponse
     */
    public function _parseResponse($valid, $later = false)
    {
        $this->_code = -1;
        $this->_arguments = [];

        if ($later) {
            $this->_pipelined_commands++;

            return true;
        }

        for ($i = 0; $i <= $this->_pipelined_commands; $i++) {
            while ($line = $this->_socket->readLine()) {
                $this->_debug("Recv: $line");

                /* If we receive an empty line, the connection has been closed. */
                if (empty($line)) {
                    $this->disconnect();

                    return PEAR::raiseError('Connection was unexpectedly closed');
                }

                /* Read the code and store the rest in the arguments array. */
                $code = substr($line, 0, 3);
                $this->_arguments[] = trim(substr($line, 4));

                /* Check the syntax of the response code. */
                if (is_numeric($code)) {
                    $this->_code = (int) $code;
                } else {
                    $this->_code = -1;
                    break;
                }

                /* If this is not a multiline response, we're done. */
                if (substr($line, 3, 1) != '-') {
                    break;
                }
            }
        }

        $this->_pipelined_commands = 0;

        /* Compare the server's response code with the valid code/codes. */
        if (is_int($valid) && ($this->_code === $valid)) {
            return true;
        } elseif (is_array($valid) && in_array($this->_code, $valid, true)) {
            return true;
        }

        return PEAR::raiseError('Invalid response code received from server',
            $this->_code);
    }

    /**
     * Return a 2-tuple containing the last response from the SMTP server.
     *
     * @return  array   A two-element array: the first element contains the
     *                  response code as an integer and the second element
     *                  contains the response's arguments as a string.
     *
     * @since   1.1.0
     */
    public function getResponse()
    {
        return [$this->_code, implode("\n", $this->_arguments)];
    }

    /**
     * Return the SMTP server's greeting string.
     *
     * @return  string  A string containing the greeting string, or null if a
     *                  greeting has not been received.
     *
     * @since   1.3.3
     */
    public function getGreeting()
    {
        return $this->_greeting;
    }

    /**
     * Attempt to connect to the SMTP server.
     *
     * @param   int     $timeout    The timeout value (in seconds) for the
     *                              socket connection.
     * @param   bool    $persistent Should a persistent socket connection
     *                              be used?
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.0
     */
    public function connect($timeout = null, $persistent = false)
    {
        $this->_greeting = null;
        $result = $this->_socket->connect($this->host, $this->port,
            $persistent, $timeout);
        if (PEAR::isError($result)) {
            return PEAR::raiseError('Failed to connect socket: '.
                                    $result->getMessage());
        }

        if (PEAR::isError($error = $this->_parseResponse(220))) {
            return $error;
        }

        /* Extract and store a copy of the server's greeting string. */
        list(, $this->_greeting) = $this->getResponse();

        if (PEAR::isError($error = $this->_negotiate())) {
            return $error;
        }

        return true;
    }

    /**
     * Attempt to disconnect from the SMTP server.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.0
     */
    public function disconnect()
    {
        if (PEAR::isError($error = $this->_put('QUIT'))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse(221))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_socket->disconnect())) {
            return PEAR::raiseError('Failed to disconnect socket: '.
                                    $error->getMessage());
        }

        return true;
    }

    /**
     * Attempt to send the EHLO command and obtain a list of ESMTP
     * extensions available, and failing that just send HELO.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.1.0
     */
    public function _negotiate()
    {
        if (PEAR::isError($error = $this->_put('EHLO', $this->localhost))) {
            return $error;
        }

        if (PEAR::isError($this->_parseResponse(250))) {
            /* If we receive a 503 response, we're already authenticated. */
            if ($this->_code === 503) {
                return true;
            }

            /* If the EHLO failed, try the simpler HELO command. */
            if (PEAR::isError($error = $this->_put('HELO', $this->localhost))) {
                return $error;
            }
            if (PEAR::isError($this->_parseResponse(250))) {
                return PEAR::raiseError('HELO was not accepted: ', $this->_code);
            }

            return true;
        }

        foreach ($this->_arguments as $argument) {
            $verb = strtok($argument, ' ');
            $arguments = substr($argument, strlen($verb) + 1,
                strlen($argument) - strlen($verb) - 1);
            $this->_esmtp[$verb] = $arguments;
        }

        if (!isset($this->_esmtp['PIPELINING'])) {
            $this->pipelining = false;
        }

        return true;
    }

    /**
     * Returns the name of the best authentication method that the server
     * has advertised.
     *
     * @return mixed    Returns a string containing the name of the best
     *                  supported authentication method or a PEAR_Error object
     *                  if a failure condition is encountered.
     *
     * @since  1.1.0
     */
    public function _getBestAuthMethod()
    {
        $available_methods = explode(' ', $this->_esmtp['AUTH']);

        foreach ($this->auth_methods as $method) {
            if (in_array($method, $available_methods)) {
                return $method;
            }
        }

        return PEAR::raiseError('No supported authentication methods');
    }

    /**
     * Attempt to do SMTP authentication.
     *
     * @param string The userid to authenticate as.
     * @param string The password to authenticate with.
     * @param string The requested authentication method.  If none is
     *               specified, the best supported method will be used.
     * @param bool   Flag indicating whether or not TLS should be attempted.
     * @param string An optional authorization identifier.  If specified, this
     *               identifier will be used as the authorization proxy.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.0
     */
    public function auth($uid, $pwd, $method = '', $tls = true, $authz = '')
    {
        /* We can only attempt a TLS connection if one has been requested,
         * we're running PHP 5.1.0 or later, have access to the OpenSSL
         * extension, are connected to an SMTP server which supports the
         * STARTTLS extension, and aren't already connected over a secure
         * (SSL) socket connection. */
        if ($tls && version_compare(PHP_VERSION, '5.1.0', '>=')
            && extension_loaded('openssl') && isset($this->_esmtp['STARTTLS'])
            && strncasecmp($this->host, 'ssl://', 6) !== 0) {
            /* Start the TLS connection attempt. */
            if (PEAR::isError($result = $this->_put('STARTTLS'))) {
                return $result;
            }
            if (PEAR::isError($result = $this->_parseResponse(220))) {
                return $result;
            }
            if (PEAR::isError($result = $this->_socket->enableCrypto(true, STREAM_CRYPTO_METHOD_TLS_CLIENT))) {
                return $result;
            } elseif ($result !== true) {
                return PEAR::raiseError('STARTTLS failed');
            }

            /* Send EHLO again to recieve the AUTH string from the
             * SMTP server. */
            $this->_negotiate();
        }

        if (empty($this->_esmtp['AUTH'])) {
            return PEAR::raiseError('SMTP server does not support authentication');
        }

        /* If no method has been specified, get the name of the best
         * supported method advertised by the SMTP server. */
        if (empty($method)) {
            if (PEAR::isError($method = $this->_getBestAuthMethod())) {
                /* Return the PEAR_Error object from _getBestAuthMethod(). */
                return $method;
            }
        } else {
            $method = strtoupper($method);
            if (!in_array($method, $this->auth_methods)) {
                return PEAR::raiseError("$method is not a supported authentication method");
            }
        }

        switch ($method) {
            case 'DIGEST-MD5':
                $result = $this->_authDigest_MD5($uid, $pwd, $authz);
                break;

            case 'CRAM-MD5':
                $result = $this->_authCRAM_MD5($uid, $pwd);
                break;

            case 'LOGIN':
                $result = $this->_authLogin($uid, $pwd);
                break;

            case 'PLAIN':
                $result = $this->_authPlain($uid, $pwd, $authz);
                break;

            default:
                $result = PEAR::raiseError("$method is not a supported authentication method");
                break;
        }

        /* If an error was encountered, return the PEAR_Error object. */
        if (PEAR::isError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Authenticates the user using the DIGEST-MD5 method.
     *
     * @param string The userid to authenticate as.
     * @param string The password to authenticate with.
     * @param string The optional authorization proxy identifier.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.1.0
     * @deprecated 本体で使用されていないため非推奨
     */
    public function _authDigest_MD5($uid, $pwd, $authz = '')
    {
        if (PEAR::isError($error = $this->_put('AUTH', 'DIGEST-MD5'))) {
            return $error;
        }
        /* 334: Continue authentication request */
        if (PEAR::isError($error = $this->_parseResponse(334))) {
            /* 503: Error: already authenticated */
            if ($this->_code === 503) {
                return true;
            }

            return $error;
        }

        $challenge = base64_decode($this->_arguments[0]);
        $digest = &Auth_SASL::factory('digestmd5');
        $auth_str = base64_encode($digest->getResponse($uid, $pwd, $challenge,
            $this->host, 'smtp'));

        if (PEAR::isError($error = $this->_put($auth_str))) {
            return $error;
        }
        /* 334: Continue authentication request */
        if (PEAR::isError($error = $this->_parseResponse(334))) {
            return $error;
        }

        /* We don't use the protocol's third step because SMTP doesn't
         * allow subsequent authentication, so we just silently ignore
         * it. */
        if (PEAR::isError($error = $this->_put(''))) {
            return $error;
        }
        /* 235: Authentication successful */
        if (PEAR::isError($error = $this->_parseResponse(235))) {
            return $error;
        }
    }

    /**
     * Authenticates the user using the CRAM-MD5 method.
     *
     * @param string The userid to authenticate as.
     * @param string The password to authenticate with.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.1.0
     * @deprecated 本体で使用されていないため非推奨
     */
    public function _authCRAM_MD5($uid, $pwd)
    {
        if (PEAR::isError($error = $this->_put('AUTH', 'CRAM-MD5'))) {
            return $error;
        }
        /* 334: Continue authentication request */
        if (PEAR::isError($error = $this->_parseResponse(334))) {
            /* 503: Error: already authenticated */
            if ($this->_code === 503) {
                return true;
            }

            return $error;
        }

        $challenge = base64_decode($this->_arguments[0]);
        $cram = &Auth_SASL::factory('crammd5');
        $auth_str = base64_encode($cram->getResponse($uid, $pwd, $challenge));

        if (PEAR::isError($error = $this->_put($auth_str))) {
            return $error;
        }

        /* 235: Authentication successful */
        if (PEAR::isError($error = $this->_parseResponse(235))) {
            return $error;
        }
    }

    /**
     * Authenticates the user using the LOGIN method.
     *
     * @param string The userid to authenticate as.
     * @param string The password to authenticate with.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.1.0
     */
    public function _authLogin($uid, $pwd)
    {
        if (PEAR::isError($error = $this->_put('AUTH', 'LOGIN'))) {
            return $error;
        }
        /* 334: Continue authentication request */
        if (PEAR::isError($error = $this->_parseResponse(334))) {
            /* 503: Error: already authenticated */
            if ($this->_code === 503) {
                return true;
            }

            return $error;
        }

        if (PEAR::isError($error = $this->_put(base64_encode($uid)))) {
            return $error;
        }
        /* 334: Continue authentication request */
        if (PEAR::isError($error = $this->_parseResponse(334))) {
            return $error;
        }

        if (PEAR::isError($error = $this->_put(base64_encode($pwd)))) {
            return $error;
        }

        /* 235: Authentication successful */
        if (PEAR::isError($error = $this->_parseResponse(235))) {
            return $error;
        }

        return true;
    }

    /**
     * Authenticates the user using the PLAIN method.
     *
     * @param string The userid to authenticate as.
     * @param string The password to authenticate with.
     * @param string The optional authorization proxy identifier.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.1.0
     */
    public function _authPlain($uid, $pwd, $authz = '')
    {
        if (PEAR::isError($error = $this->_put('AUTH', 'PLAIN'))) {
            return $error;
        }
        /* 334: Continue authentication request */
        if (PEAR::isError($error = $this->_parseResponse(334))) {
            /* 503: Error: already authenticated */
            if ($this->_code === 503) {
                return true;
            }

            return $error;
        }

        $auth_str = base64_encode($authz.chr(0).$uid.chr(0).$pwd);

        if (PEAR::isError($error = $this->_put($auth_str))) {
            return $error;
        }

        /* 235: Authentication successful */
        if (PEAR::isError($error = $this->_parseResponse(235))) {
            return $error;
        }

        return true;
    }

    /**
     * Send the HELO command.
     *
     * @param string The domain name to say we are.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.0
     */
    public function helo($domain)
    {
        if (PEAR::isError($error = $this->_put('HELO', $domain))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse(250))) {
            return $error;
        }

        return true;
    }

    /**
     * Return the list of SMTP service extensions advertised by the server.
     *
     * @return array The list of SMTP service extensions.
     *
     * @since 1.3
     */
    public function getServiceExtensions()
    {
        return $this->_esmtp;
    }

    /**
     * Send the MAIL FROM: command.
     *
     * @param string $sender    The sender (reverse path) to set.
     * @param string $params    String containing additional MAIL parameters,
     *                          such as the NOTIFY flags defined by RFC 1891
     *                          or the VERP protocol.
     *
     *                          If $params is an array, only the 'verp' option
     *                          is supported.  If 'verp' is true, the XVERP
     *                          parameter is appended to the MAIL command.  If
     *                          the 'verp' value is a string, the full
     *                          XVERP=value parameter is appended.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.0
     */
    public function mailFrom($sender, $params = null)
    {
        $args = "FROM:<$sender>";

        /* Support the deprecated array form of $params. */
        if (is_array($params) && isset($params['verp'])) {
            /* XVERP */
            if ($params['verp'] === true) {
                $args .= ' XVERP';

            /* XVERP=something */
            } elseif (trim($params['verp'])) {
                $args .= ' XVERP='.$params['verp'];
            }
        } elseif (is_string($params)) {
            $args .= ' '.$params;
        }

        if (PEAR::isError($error = $this->_put('MAIL', $args))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
            return $error;
        }

        return true;
    }

    /**
     * Send the RCPT TO: command.
     *
     * @param string $recipient The recipient (forward path) to add.
     * @param string $params    String containing additional RCPT parameters,
     *                          such as the NOTIFY flags defined by RFC 1891.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.0
     */
    public function rcptTo($recipient, $params = null)
    {
        $args = "TO:<$recipient>";
        if (is_string($params)) {
            $args .= ' '.$params;
        }

        if (PEAR::isError($error = $this->_put('RCPT', $args))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse([250, 251], $this->pipelining))) {
            return $error;
        }

        return true;
    }

    /**
     * Quote the data so that it meets SMTP standards.
     *
     * This is provided as a separate public function to facilitate
     * easier overloading for the cases where it is desirable to
     * customize the quoting behavior.
     *
     * @param string $data  The message text to quote. The string must be passed
     *                      by reference, and the text will be modified in place.
     *
     * @since  1.2
     */
    public function quotedata(&$data)
    {
        /* Change Unix (\n) and Mac (\r) linefeeds into
         * Internet-standard CRLF (\r\n) linefeeds. */
        $data = preg_replace(['/(?<!\r)\n/', '/\r(?!\n)/'], "\r\n", $data);

        /* Because a single leading period (.) signifies an end to the
         * data, legitimate leading periods need to be "doubled"
         * (e.g. '..'). */
        $data = str_replace("\n.", "\n..", $data);
    }

    /**
     * Send the DATA command.
     *
     * @param mixed $data     The message data, either as a string or an open
     *                        file resource.
     * @param string $headers The message headers.  If $headers is provided,
     *                        $data is assumed to contain only body data.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.0
     */
    public function data($data, $headers = null)
    {
        /* Verify that $data is a supported type. */
        if (!is_string($data) && !is_resource($data)) {
            return PEAR::raiseError('Expected a string or file resource');
        }

        /* Start by considering the size of the optional headers string.  We
         * also account for the addition 4 character "\r\n\r\n" separator
         * sequence. */
        $size = (is_null($headers)) ? 0 : strlen($headers) + 4;

        if (is_resource($data)) {
            $stat = fstat($data);
            if ($stat === false) {
                return PEAR::raiseError('Failed to get file size');
            }
            $size += $stat['size'];
        } else {
            $size += strlen($data);
        }

        /* RFC 1870, section 3, subsection 3 states "a value of zero indicates
         * that no fixed maximum message size is in force".  Furthermore, it
         * says that if "the parameter is omitted no information is conveyed
         * about the server's fixed maximum message size". */
        $limit = (isset($this->_esmtp['SIZE'])) ? $this->_esmtp['SIZE'] : 0;
        if ($limit > 0 && $size >= $limit) {
            $this->disconnect();

            return PEAR::raiseError('Message size exceeds server limit');
        }

        /* Initiate the DATA command. */
        if (PEAR::isError($error = $this->_put('DATA'))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse(354))) {
            return $error;
        }

        /* If we have a separate headers string, send it first. */
        if (!is_null($headers)) {
            $this->quotedata($headers);
            if (PEAR::isError($result = $this->_send($headers."\r\n\r\n"))) {
                return $result;
            }
        }

        /* Now we can send the message body data. */
        if (is_resource($data)) {
            /* Stream the contents of the file resource out over our socket
             * connection, line by line.  Each line must be run through the
             * quoting routine. */
            while ($line = fgets($data, 1024)) {
                $this->quotedata($line);
                if (PEAR::isError($result = $this->_send($line))) {
                    return $result;
                }
            }
        } else {
            /*
             * Break up the data by sending one chunk (up to 512k) at a time.
             * This approach reduces our peak memory usage.
             */
            for ($offset = 0; $offset < $size;) {
                $end = $offset + 512000;

                /*
                 * Ensure we don't read beyond our data size or span multiple
                 * lines.  quotedata() can't properly handle character data
                 * that's split across two line break boundaries.
                 */
                if ($end >= $size) {
                    $end = $size;
                } else {
                    for (; $end < $size; $end++) {
                        if ($data[$end] != "\n") {
                            break;
                        }
                    }
                }

                /* Extract our chunk and run it through the quoting routine. */
                $chunk = substr($data, $offset, $end - $offset);
                $this->quotedata($chunk);

                /* If we run into a problem along the way, abort. */
                if (PEAR::isError($result = $this->_send($chunk))) {
                    return $result;
                }

                /* Advance the offset to the end of this chunk. */
                $offset = $end;
            }
        }

        /* Finally, send the DATA terminator sequence. */
        if (PEAR::isError($result = $this->_send("\r\n.\r\n"))) {
            return $result;
        }

        /* Verify that the data was successfully received by the server. */
        if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
            return $error;
        }

        return true;
    }

    /**
     * Send the SEND FROM: command.
     *
     * @param string The reverse path to send.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.2.6
     */
    public function sendFrom($path)
    {
        if (PEAR::isError($error = $this->_put('SEND', "FROM:<$path>"))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
            return $error;
        }

        return true;
    }

    /**
     * Backwards-compatibility wrapper for sendFrom().
     *
     * @param string The reverse path to send.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since       1.0
     * @deprecated  1.2.6
     */
    public function send_from($path)
    {
        return self::sendFrom($path);
    }

    /**
     * Send the SOML FROM: command.
     *
     * @param string The reverse path to send.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.2.6
     */
    public function somlFrom($path)
    {
        if (PEAR::isError($error = $this->_put('SOML', "FROM:<$path>"))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
            return $error;
        }

        return true;
    }

    /**
     * Backwards-compatibility wrapper for somlFrom().
     *
     * @param string The reverse path to send.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since       1.0
     * @deprecated  1.2.6
     */
    public function soml_from($path)
    {
        return self::somlFrom($path);
    }

    /**
     * Send the SAML FROM: command.
     *
     * @param string The reverse path to send.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.2.6
     */
    public function samlFrom($path)
    {
        if (PEAR::isError($error = $this->_put('SAML', "FROM:<$path>"))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
            return $error;
        }

        return true;
    }

    /**
     * Backwards-compatibility wrapper for samlFrom().
     *
     * @param string The reverse path to send.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since       1.0
     * @deprecated  1.2.6
     */
    public function saml_from($path)
    {
        return self::samlFrom($path);
    }

    /**
     * Send the RSET command.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.0
     */
    public function rset()
    {
        if (PEAR::isError($error = $this->_put('RSET'))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
            return $error;
        }

        return true;
    }

    /**
     * Send the VRFY command.
     *
     * @param string The string to verify
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.0
     */
    public function vrfy($string)
    {
        /* Note: 251 is also a valid response code */
        if (PEAR::isError($error = $this->_put('VRFY', $string))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse([250, 252]))) {
            return $error;
        }

        return true;
    }

    /**
     * Send the NOOP command.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     *
     * @since  1.0
     */
    public function noop()
    {
        if (PEAR::isError($error = $this->_put('NOOP'))) {
            return $error;
        }
        if (PEAR::isError($error = $this->_parseResponse(250))) {
            return $error;
        }

        return true;
    }

    /**
     * Backwards-compatibility method.  identifySender()'s functionality is
     * now handled internally.
     *
     * @return  bool     This method always return true.
     *
     * @since   1.0
     */
    public function identifySender()
    {
        return true;
    }
}
