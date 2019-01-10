<?php

declare(strict_types=1);

/**
 *
 * Simple Framework
 *
 * @copyright Simple Inc. All rights reserved.
 *
 */

namespace Simple\Mail;

use Simple\Mail\Header\Exception;
use Simple\Mail\Header\HeaderInterface;

/**
 *
 * mail header class
 *
 * @package Simple\Mail
 *
 */
class Header implements HeaderInterface
{
    /**
     *
     * charset
     *
     * @var string
     *
     */
    protected $_charset = '';

    /**
     *
     * encoding
     *
     * @var string
     *
     */
    protected $_encoding = '';

    /**
     *
     * file name
     *
     * @var string
     *
     */
    protected $_fieldName;

    /**
     *
     * field value
     *
     * @var mixed
     *
     */
    protected $_fieldValue;

    /**
     *
     * headers
     *
     * @var array
     *
     */
    protected static $_headers = [
        'bcc' => ['Bcc', '\Simple\Mail\Header\Bcc'],
        'cc' => ['Cc', '\Simple\Mail\Header\Cc'],
        'content-disposition' => ['Content-Disposition', '\Simple\Mail\Header\ContentDisposition'],
        'content-type' => ['Content-Type', '\Simple\Mail\Header\ContentType'],
        'errors-to' => ['Errors-To', '\Simple\Mail\Header\ErrorsTo'],
        'from' => ['From', '\Simple\Mail\Header\From'],
        'message-id' => ['Message-ID', '\Simple\Mail\Header\MessageId'],
        'mime-version' => ['MIME-Version', '\Simple\Mail\Header\MimeVersion'],
        'reply-to' => ['Reply-To', '\Simple\Mail\Header\ReplyTo'],
        'return-path' => ['Return-Path', '\Simple\Mail\Header\ReturnPath'],
        'sender' => ['Sender', '\Simple\Mail\Header\Sender'],
        'subject' => ['Subject', '\Simple\Mail\Header\Subject'],
        'to' => ['To', '\Simple\Mail\Header\To']
    ];

    /**
     *
     * Constructs a new Header instance.
     *
     * @param string $fieldName a field name
     * @param mixed $fieldValue a field value
     *
     */
    public function __construct(string $fieldName, $fieldValue)
    {
        $this->_fieldName = $fieldName;
        $this->setFieldValue($fieldValue);
    }

    /**
     *
     * Returns a string representation of this instance.
     *
     * @return string
     *
     */
    public function __toString() : string
    {
        return $this->getFieldName() . ': ' . $this->getFieldValue(true);
    }

    /**
     *
     * Returns a charset.
     *
     * @return string|null
     *
     */
    public function getCharset() : ?string
    {
        return $this->_charset;
    }

    /**
     *
     * Returns an encoding.
     *
     * @return string|null
     *
     */
    public function getEncoding() : ?string
    {
        return $this->_encoding;
    }

    /**
     *
     * Returns a field name.
     *
     * @return string
     *
     */
    public function getFieldName() : string
    {
        return $this->_fieldName;
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
        if ($encode === true)
        {
            return Mime::encode($this->_fieldValue, $this->_charset, $this->_encoding);
        }

        return $this->_fieldValue;
    }

    /**
     *
     * Returns an instance.
     *
     * @param string $headerLine a header line
     * @throws \Simple\Mail\Header\Exception if an invalid argument is supplied
     * @return \Simple\Mail\Header\HeaderInterface
     *
     */
    public static function parseString(string $headerLine) : HeaderInterface
    {
        $headerLine = explode(':', $headerLine, 2);

        if (count($headerLine) !== 2)
        {
            throw new Exception('invalid argument supplied for parseString');
        }

        $headerLine[0] = trim($headerLine[0]);
        $headerLine[1] = trim($headerLine[1]);
        $fieldName = strtolower($headerLine[0]);

        if (isset(static::$_headers[$fieldName]))
        {
            $class = static::$_headers[$fieldName];

            return $class[1]::parseString($class[0] . ': ' . $headerLine[1]);
        }

        return new static($headerLine[0], $headerLine[1]);
    }

    /**
     *
     * Registers a header.
     *
     * @param string $fieldName a name
     * @param string $class a class
     * @return void
     *
     */
    public static function registerHeader(string $fieldName, string $class) : void
    {
        static::$_headers[strtolower($fieldName)] = [$fieldName, $class];
    }

    /**
     *
     * Sets a charset.
     *
     * @param string $charset a charset
     * @return void
     *
     */
    public function setCharset(string $charset) : void
    {
        $this->_charset = $charset;
    }

    /**
     *
     * Sets an encoding.
     *
     * @param string $encoding an encoding
     * @return void
     *
     */
    public function setEncoding(string $encoding) : void
    {
        $this->_encoding = $encoding;
    }

    /**
     *
     * Sets a field value.
     *
     * @param mixed $fieldValue a field value
     * @return void
     *
     */
    public function setFieldValue($fieldValue) : void
    {
        $this->_fieldValue = $fieldValue;
    }

    /**
     *
     * Unregisters a header.
     *
     * @param string $fieldName a name
     * @return void
     *
     */
    public static function UnregisterHeader(string $fieldName) : void
    {
        $fieldName = strtolower($fieldName);

        if (isset(static::$_headers[$fieldName]))
        {
            unset(static::$_headers[$fieldName]);
        }
    }
}
