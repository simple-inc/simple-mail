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

use Simple\Mail\Transport\AbstractTransport;
use Simple\Mail\Transport\Exception;

/**
 *
 * php transport class
 *
 * @package Simple\Mail\Transport
 *
 */
class Php extends AbstractTransport
{
    /**
     *
     * Sends a message.
     *
     * @param array $headers headers
     * @param string $message a message
     * @throws \Simple\Mail\Transport\Exception if error occurs
     * @return boolean
     *
     */
    public function send(array $headers, string $message) : bool
    {
        // get a host
        $host = $this->getOption('host');

        // host exists
        if ($host !== null)
        {
            ini_set('SMTP', $host);
        }

        // get a port
        $port = $this->getOption('port');

        // port exists
        if ($port !== null)
        {
            // set smtp_port
            ini_set('smtp_port', (string)$port);
        }

        // To
        $to = null;

        // To exists
        if (isset($headers['To']))
        {
            $to = str_replace('To: ', '', (string)$headers['To']);
            unset($headers['To']);
        }

        // Subject
        $subject = null;

        // Subject exists
        if (isset($headers['Subject']))
        {
            $subject = str_replace('Subject: ', '', (string)$headers['Subject']);
            unset($headers['Subject']);
        }

        // send
        $result = mail($to, $subject, $message, implode("\r\n", $headers));

        // failed to send a message
        if ($result === false)
        {
            throw new Exception('failed to send a message');
        }

        return true;
    }
}
