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

/**
 *
 * mail transport interface
 *
 * @package Simple\Mail\Transport
 *
 */
interface TransportInterface
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
    public function send(array $headers, string $message) : bool;
}
