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

/**
 *
 * MIME-Version mail header class
 *
 * @package Simple\Mail\Header
 *
 */
class MimeVersion extends Header
{
    /**
     *
     * Constructs a new MimeVersion instance.
     *
     * @param string $mimeVersion a MIME version (optional)
     *
     */
    public function __construct(string $mimeVersion = '1.0')
    {
        parent::__construct('MIME-Version', $mimeVersion);
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
     * Returns a MimeVersion instance.
     *
     * @param string $headerLine a header line
     * @throws \Simple\Mail\Header\Exception if an invalid argument is supplied
     * @return \Simple\Mail\Header\HeaderInterface
     *
     */
    public static function parseString(string $headerLine) : HeaderInterface
    {
        $headerLine = explode(':', $headerLine, 2);
        $headerLine[0] = strtolower(trim($headerLine[0]));

        if ($headerLine[0] !== 'mime-version' || count($headerLine) !== 2)
        {
            throw new Exception('invalid argument supplied for parseString');
        }

        return new static(trim($headerLine[1]));
    }

    /**
     *
     * Sets a field value.
     *
     * @param string $fieldValue a field value
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function setFieldValue($fieldValue) : void
    {
        if (filter_var($fieldValue, FILTER_VALIDATE_FLOAT) === false)
        {
            throw new Exception('invalid argument supplied for setFieldValue');
        }

        $this->_fieldValue = $fieldValue;
    }
}
