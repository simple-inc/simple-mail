<?php

declare(strict_types=1);

/**
 *
 * Simple Framework
 *
 * @copyright Simple Inc. All rights reserved.
 *
 */

namespace Simple\Mail\Transport;

use Simple\Mail\Protocol\Smtp as SmtpProtocol;
use Simple\Mail\Transport\AbstractTransport;
use Simple\Mail\Transport\Exception;

/**
 *
 * SMTP transport class
 *
 * @package Simple\Mail\Transport
 *
 */
class Smtp extends AbstractTransport
{
    /**
     *
     * SMTP protocol
     *
     * @var \Simple\Mail\Protocol\Smtp
     *
     */
    protected $_smtp;

    /**
     *
     * Constructs a new Smtp transport instance.
     *
     * @param array $options options (optional)
     *
     */
    public function __construct(array $options = [])
    {
        $this->_smtp = new SmtpProtocol($options);
    }

    /**
     *
     * Returns an option.
     *
     * @param string $name an option name
     * @return mixed
     *
     */
    public function getOption(string $name)
    {
        return $this->_smtp->getOption($name);
    }

    /**
     *
     * Returns options.
     *
     * @return array
     *
     */
    public function getOptions() : array
    {
        return $this->_smtp->getOptions();
    }

    /**
     *
     * Sends a message.
     *
     * @param array $headers headers
     * @param string $message a message
     * @throws \Simple\Mail\Protocol\Exception if an error occurs
     * @throws \Simple\Mail\Transport\Exception if an error occurs
     * @return boolean
     *
     */
    public function send(array $headers, string $message) : bool
    {
        // From not set
        if (!isset($headers['From']))
        {
            throw new Exception('From not found');
        }

        // connect to an SMTP server
        $this->_smtp->connect();

        // remote host
        $remoteHost = $this->_smtp->getHost();

        // send EHLO
        $this->_smtp->send('EHLO ' . $remoteHost);
        $this->_smtp->expect(250);

        // TLS
        if ($this->_smtp->getProtocol() == 'tls')
        {
            // send STARTTLS
            $this->_smtp->send('STARTTLS');

            // expect 220
            $this->_smtp->expect(220);

            if (stream_socket_enable_crypto($this->_smtp->getSocket(), true, STREAM_CRYPTO_METHOD_TLS_CLIENT) === false)
            {
                throw new Exception('failed to start TLS');
            }
        }

        // get an auth
        $auth = $this->_smtp->getAuth();

        // AUTH LOGIN
        if ($auth == 'login')
        {
            $this->_smtp->send('AUTH LOGIN');
            $this->_smtp->expect(334);
            $this->_smtp->send(base64_encode($this->_smtp->getUsername()));
            $this->_smtp->expect(334);
            $this->_smtp->send(base64_encode($this->_smtp->getPassword()));
            $this->_smtp->expect(235);
        }
        // AUTH PLAIN
        elseif ($auth == 'plain')
        {
            $this->_smtp->send('AUTH PLAIN');
            $this->_smtp->expect(334);
            $this->_smtp->send(base64_encode("\0" . $this->_smtp->getUsername() . "\0" . $this->_smtp->getPassword()));
            $this->_smtp->expect(235);
        }
        // AUTH CRAM-MD5
        elseif ($auth == 'cram-md5')
        {
            $this->_smtp->send('AUTH CRAM-MD5');
            $ticket = $this->_smtp->expect(334);
            $this->_smtp->send(base64_encode($this->_smtp->getUsername() . ' ' . hash_hmac('MD5', $this->_smtp->getPassword(), base64_decode($ticket))));
            $this->_smtp->expect(235);
        }

        // MAIL FROM
        $addresses = $headers['From'];

        foreach ($addresses as $address)
        {
            $this->_smtp->send('MAIL FROM: <' . $address->getAddress() . '>');
            $this->_smtp->expect(250);
            break;
        }

        // To
        if (isset($headers['To']))
        {
            $addresses = $headers['To'];

            foreach ($addresses as $address)
            {
                $this->_smtp->send('RCPT TO: <' . $address->getAddress() . '>');
                $this->_smtp->expect(250);
            }
        }

        // Cc
        if (isset($headers['Cc']))
        {
            $addresses = $headers['Cc'];

            foreach ($addresses as $address)
            {
                $this->_smtp->send('RCPT TO: <' . $address->getAddress() . '>');
                $this->_smtp->expect(250);
            }
        }

        // Bcc
        if (isset($headers['Bcc']))
        {
            $addresses = $headers['Bcc'];

            foreach ($addresses as $address)
            {
                $this->_smtp->send('RCPT TO: <' . $address->getAddress() . '>');
                $this->_smtp->expect(250);
            }

            // Bcc must be removed from headers
            unset($headers['Bcc']);
        }

        // DATA
        $this->_smtp->send('DATA');
        $this->_smtp->expect(354);
        $this->_smtp->send(implode("\r\n", $headers) . "\r\n\r\n" . $message);
        $this->_smtp->send('.');
        $this->_smtp->expect(250);

        // disconnect from an SMTP server
        $this->_smtp->disconnect();

        return true;
    }

    /**
     *
     * Sets an option.
     *
     * @param string $name an option name
     * @param mixed $value an option value
     * @return void
     *
     */
    public function setOption(string $name, $value) : void
    {
        $this->_smtp->setOption($name, $value);
    }

    /**
     *
     * Sets options.
     *
     * @param array $options options
     * @return void
     *
     */
    public function setOptions(array $options) : void
    {
        $this->_smtp->setOptions($options);
    }
}
