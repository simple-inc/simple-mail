<?php

declare(strict_types=1);

/**
 *
 * Simple Framework
 *
 * @copyright Simple Inc. All rights reserved.
 *
 */

namespace Simple\Mail\Protocol;

use Simple\Mail\Protocol\AbstractProtocol;
use Simple\Mail\Protocol\Exception;

/**
 *
 * Simple Mail Transfer Protocol class
 *
 * @package Simple\Mail\Protocol
 *
 */
class Smtp extends AbstractProtocol
{
    /**
     *
     * stream socket
     *
     * @var resource
     *
     */
    protected $_socket;

    /**
     *
     * Connects to an SMTP server.
     *
     * @throws \Simple\Mail\Protocol\Exception if an error occurs
     * @return boolean
     *
     */
    public function connect() : bool
    {
        // socket not opened
        if (!is_resource($this->_socket))
        {
            $protocol = $this->getProtocol();
            $host = $this->getHost();
            $port = $this->getPort();
            $timeout = $this->getTimeout();

            if ($protocol != 'ssl')
            {
                $protocol = 'tcp';
            }

            if ($host === null)
            {
                $host = 'localhost';
            }

            if ($port === null)
            {
                $port = 25;
            }

            if ($timeout === null)
            {
                $timeout = 30;
            }

            $remoteHost = $protocol . '://' . $host . ':' . $port;
            $errorNumber = 0;
            $errorMessage = '';

            // open a socket
            $this->_socket = stream_socket_client($remoteHost, $errorNumber, $errorMessage, $timeout);

            // failed to open a socket
            if ($this->_socket === false)
            {
                if ($errorNumber === 0)
                {
                    $errorMessage = 'failed to connect to ' . $remoteHost;
                }

                throw new Exception($errorMessage, $errorNumber);
            }

            // failed to set a timeout
            if (stream_set_timeout($this->_socket, $timeout) === false)
            {
                throw new Exception('failed to set a timeout');
            }

            // expect 220
            $this->expect(220);
        }

        return true;
    }

    /**
     *
     * Disconnects from an SMTP server.
     *
     * @throws \Simple\Mail\Protocol\Exception if an error occurs
     * @return boolean
     *
     */
    public function disconnect() : bool
    {
        // socket exists
        if (is_resource($this->_socket))
        {
            // send QUIT
            $this->send('QUIT');
            $this->expect(221);

            fclose($this->_socket);
        }

        return true;
    }

    /**
     *
     * Expects a response.
     *
     * @param int $code a response code
     * @throws \Simple\Mail\Protocol\Exception if an error occurs
     * @return string
     *
     */
    public function expect(int $code) : string
    {
        $command = '';
        $flag = '';
        $message = '';
        $errorMessage = '';

        do
        {
            // get a response
            $response = $this->receive();

            list($command, $flag, $message) = preg_split('/([\s-]+)/', $response, 2, PREG_SPLIT_DELIM_CAPTURE);

            // error message exists
            if ($errorMessage !== '')
            {
                $errorMessage .= ' ' . $message;
            }
            // unexpected command
            elseif ($command === null || (int)$command !== $code)
            {
                $errorMessage = $message;
            }
        }
        while(strpos($flag, '-') === 0);

        // error message exists
        if ($errorMessage !== '')
        {
            throw new Exception('unexpected ' . $errorMessage);
        }

        return $message;
    }

    /**
     *
     * Returns a stream socket.
     *
     * @return resource
     *
     */
    public function getSocket()
    {
        return $this->_socket;
    }

    /**
     *
     * Receives a response.
     *
     * @throws \Simple\Mail\Protocol\Exception if an error occurs
     * @return string
     *
     */
    public function receive() : string
    {
        $timeout = $this->getTimeout();

        // timeout exists
        if ($timeout !== null)
        {
            // set a timeout
            if (stream_set_timeout($this->_socket, $timeout) === false)
            {
                throw new Exception('failed to set a timeout');
            }
        }

        // get a response
        $response = fgets($this->_socket, 1024);

        // get a metadata
        $metadata = stream_get_meta_data($this->_socket);

        // timeout
        if (!empty($metadata['timed_out']))
        {
            throw new Exception('connection timeout');
        }

        // error
        if ($response === false)
        {
            throw new Exception('failed to receive a response');
        }

        return $response;
    }

    /**
     *
     * Sends a request.
     *
     * @param string $request a request
     * @throws \Simple\Mail\Protocol\Exception if an error occurs
     * @return int|boolean
     *
     */
    public function send(string $request)
    {
        // write to stream
        $result = fwrite($this->_socket, $request . "\r\n");

        if ($result === false)
        {
            throw new Exception('failed to send a request');
        }

        return $result;
    }

    /**
     *
     * Returns an auth.
     *
     * @return string|null
     *
     */
    public function getAuth() : ?string
    {
        return $this->getOption('auth');
    }

    /**
     *
     * Returns a host.
     *
     * @return string|null
     *
     */
    public function getHost() : ?string
    {
        return $this->getOption('host');
    }

    /**
     *
     * Returns a password.
     *
     * @return string|null
     *
     */
    public function getPassword() : ?string
    {
        return $this->getOption('password');
    }

    /**
     *
     * Returns a port.
     *
     * @return int|null
     *
     */
    public function getPort() : ?int
    {
        return $this->getOption('port');
    }

    /**
     *
     * Returns a protocol.
     *
     * @return string|null
     *
     */
    public function getProtocol() : ?string
    {
        return $this->getOption('protocol');
    }

    /**
     *
     * Returns a timeout.
     *
     * @return int|null
     *
     */
    public function getTimeout() : ?int
    {
        return $this->getOption('timeout');
    }

    /**
     *
     * Returns a username.
     *
     * @return string|null
     *
     */
    public function getUsername() : ?string
    {
        return $this->getOption('username');
    }

    /**
     *
     * Sets an auth.
     *
     * @param string $auth an auth
     * @return void
     *
     */
    public function setAuth(string $auth) : void
    {
        $this->setOption('auth', $auth);
    }

    /**
     *
     * Sets a host.
     *
     * @param string $host a host
     * @return void
     *
     */
    public function setHost(string $host) : void
    {
        $this->setOption('host', $host);
    }

    /**
     *
     * Sets a password.
     *
     * @param string $password a password
     * @return void
     *
     */
    public function setPassword(string $password) : void
    {
        $this->setOption('password', $password);
    }

    /**
     *
     * Sets a port.
     *
     * @param int $port a port
     * @return void
     *
     */
    public function setPort(int $port) : void
    {
        $this->setOption('port', $port);
    }

    /**
     *
     * Sets a protocol.
     *
     * @param string $protocol a protocol
     * @return void
     *
     */
    public function setProtocol(string $protocol) : void
    {
        $this->setOption('protocol', $protocol);
    }

    /**
     *
     * Sets a timeout.
     *
     * @param int $timeout a timeout
     * @return void
     *
     */
    public function setTimeout(int $timeout) : void
    {
        $this->setOption('timeout', $timeout);
    }

    /**
     *
     * Sets a username.
     *
     * @param string $username a username
     * @return void
     *
     */
    public function setUsername(string $username) : void
    {
        $this->setOption('username', $username);
    }
}
