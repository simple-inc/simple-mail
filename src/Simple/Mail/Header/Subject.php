<?php

declare(strict_types=1);

/**
 *
 * Simple Framework
 *
 * @copyright Simple Inc. All rights reserved.
 *
 */

namespace Simple\Mail\Header;

use Simple\Mail\Header;
use Simple\Mail\Header\Exception;
use Simple\Mail\Mime;

/**
 *
 * Subject mail header class
 *
 * @package Simple\Mail\Header
 *
 */
class Subject extends Header
{
    /**
     *
     * Constructs a new Subject instance.
     *
     * @param string $subject a subject
     * @param string $charset a charset (optional)
     * @param string $encoding an encoding (optional)
     *
     */
    public function __construct(string $subject, string $charset = '', string $encoding = '')
    {
        parent::__construct('Subject', $subject);

        $this->setCharset($charset);
        $this->setEncoding($encoding);
    }

    /**
     *
     * Returns a Subject instance.
     *
     * @param string $headerLine a header line
     * @throws \Simple\Mail\Header\Exception if an invalid argument is supplied
     * @return \Simple\Mail\Header\Subject
     *
     */
    public static function parseString(string $headerLine) : HeaderInterface
    {
        $headerLine = explode(':', $headerLine, 2);
        $headerLine[0] = strtolower(trim($headerLine[0]));

        if ($headerLine[0] !== 'subject' || count($headerLine) !== 2)
        {
            throw new Exception('invalid argument supplied for parseString');
        }

        $headerLine[1] = Mime::decode(trim($headerLine[1]));

        return new static($headerLine[1]);
    }
}
