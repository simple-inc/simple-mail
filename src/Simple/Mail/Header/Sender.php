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

use Simple\Mail\Address;
use Simple\Mail\Header;
use Simple\Mail\Header\Exception;

/**
 *
 * Sender mail header class
 *
 * @package Simple\Mail\Header
 *
 */
class Sender extends Header
{
    /**
     *
     * Constructs a new Sender instance.
     *
     * @param \Simple\Mail\Address|string $sender a sender
     *
     */
    public function __construct($sender)
    {
        parent::__construct('Sender', $sender);
    }

    /**
     *
     * Returns a field value.
     *
     * @param boolean $encode a MIME encode flag (optional)
     * @return string
     *
     */
    public function getFieldValue(bool $encode = false) : string
    {
        return parent::getFieldValue(false);
    }

    /**
     *
     * Returns a Sender instance.
     *
     * @param string $headerLine a header line
     * @throws \Simple\Mail\Header\Exception if an invalid argument is supplied
     * @return \Simple\Mail\Header\Sender
     *
     */
    public static function parseString(string $headerLine) : HeaderInterface
    {
        $headerLine = explode(':', $headerLine, 2);
        $headerLine[0] = strtolower(trim($headerLine[0]));

        if ($headerLine[0] !== 'sender' || count($headerLine) !== 2)
        {
            throw new Exception('invalid argument supplied for parseString');
        }

        return new static(trim($headerLine[1]));
    }

    /**
     *
     * Sets a field value.
     *
     * @param \Simple\Mail\Address|string $fieldValue a field value
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function setFieldValue($fieldValue) : void
    {
        if ($fieldValue instanceof Address)
        {
            $fieldValue = $fieldValue->getAddress();
        }

        if (filter_var($fieldValue, FILTER_VALIDATE_EMAIL) === false)
        {
            throw new Exception('invalid argument supplied for setFieldValue');
        }

        $this->_fieldValue = $fieldValue;
    }
}
