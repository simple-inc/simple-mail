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
 * Content-Type mail header class
 *
 * @package Simple\Mail\Header
 *
 */
class ContentType extends Header
{
    /**
     *
     * field value
     *
     * @var array
     *
     */
    protected $_fieldValue = [];

    /**
     *
     * Constructs a new ContentType instance.
     *
     * @param string $contentType a content type
     *
     */
    public function __construct(string $contentType)
    {
        parent::__construct('Content-Type', $contentType);
    }

    /**
     *
     * Returns a boundary.
     *
     * @return string|null
     *
     */
    public function getBoundary() : ?string
    {
        return $this->_fieldValue['boundary'] ?? null;
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
        $type = $this->getType();
        $boundary = $this->getBoundary();
        $charset = $this->getCharset();
        $name = $this->getName();

        // text
        if ($this->isText())
        {
            if ($charset !== null)
            {
                return $type . '; charset=' . $charset;
            }
        }
        // multipart
        elseif ($this->isMultipart())
        {
            if ($boundary === null)
            {
                // set a boundary
                $boundary = Mime::getBoundary();
            }

            return $type . '; boundary=' . $boundary;
        }
        // name
        elseif ($name !== null)
        {
            if ($encode === true)
            {
                $name = Mime::encode($name, $charset, $this->_encoding);
            }

            return $type . '; name="' . $name . '"';
        }

        return $type;
    }

    /**
     *
     * Returns a name.
     *
     * @return string|null
     *
     */
    public function getName() : ?string
    {
        return $this->_fieldValue['name'] ?? null;
    }

    /**
     *
     * Returns a sub type.
     *
     * @return string
     *
     */
    public function getSubType() : ?string
    {
        if (isset($this->_fieldValue['type']))
        {
            return substr($this->_fieldValue['type'], strpos($this->_fieldValue['type'], '/') + 1);
        }

        return null;
    }

    /**
     *
     * Returns a type.
     *
     * @return string|null
     *
     */
    public function getType() : ?string
    {
        return $this->_fieldValue['type'] ?? null;
    }

    /**
     *
     * Returns true if this ContentType is multipart/alternative.
     *
     * @return boolean
     *
     */
    public function isAlternative() : bool
    {
        return $this->getType() == 'multipart/alternative';
    }

    /**
     *
     * Returns true if this ContentType is multipart/mixed.
     *
     * @return boolean
     *
     */
    public function isMixed() : bool
    {
        return $this->getType() == 'multipart/mixed';
    }

    /**
     *
     * Returns true if this ContentType is multipart.
     *
     * @return boolean
     *
     */
    public function isMultipart() : bool
    {
        return strpos($this->getType(), 'multipart') === 0;
    }

    /**
     *
     * Returns true if this ContentType is text.
     *
     * @return boolean
     *
     */
    public function isText() : bool
    {
        return strpos($this->getType(), 'text') === 0;
    }

    /**
     *
     * Returns a ContentType instance.
     *
     * @param string $headerLine a header line
     * @throws \Simple\Mail\Header\Exception if an invalid argument is supplied
     * @return \Simple\Mail\Header\ContentType
     *
     */
    public static function parseString(string $headerLine) : HeaderInterface
    {
        $headerLine = explode(':', $headerLine, 2);
        $headerLine[0] = strtolower(trim($headerLine[0]));

        if ($headerLine[0] !== 'content-type' || count($headerLine) !== 2)
        {
            throw new Exception('invalid argument supplied for parseString');
        }

        $headerLine = trim($headerLine[1]);
        $parameters = explode(';', $headerLine);
        $contentType = array_shift($parameters);
        $contentType = new static($contentType);

        $names = [];

        foreach ($parameters as $parameter)
        {
            $parameter = explode('=', $parameter, 2);

            if (count($parameter) === 2)
            {
                $parameter[0] = trim($parameter[0]);
                $parameter[1] = trim($parameter[1], " \t\n\r\0\x0B\"");

                // boundary
                if ($parameter[0] == 'boundary')
                {
                    $contentType->setBoundary($parameter[1]);
                }
                // charset
                elseif ($parameter[0] == 'charset')
                {
                    $contentType->setCharset($parameter[1]);
                }
                // name
                elseif ($parameter[0] == 'name')
                {
                    $parameter[1] = Mime::decode($parameter[1]);
                    $contentType->setName($parameter[1]);
                }
                // multiple names
                elseif (strpos($parameter[0], 'name*') === 0)
                {
                    $names[] = $parameter[1];
                }
            }
        }

        // multiple names found
        if (count($names) > 0)
        {
            $names = implode('', $names);
            $names = Mime::decode($names);
            $contentType->setName($names);
        }

        return $contentType;
    }

    /**
     *
     * Sets a boundary.
     *
     * @param string $boundary a boundary
     * @return void
     *
     */
    public function setBoundary(string $boundary) : void
    {
        $this->_fieldValue['boundary'] = $boundary;
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
        $parameters = explode(';', $fieldValue);
        $type = array_shift($parameters);

        $this->setType($type);

        foreach ($parameters as $parameter)
        {
            $parameter = explode('=', $parameter, 2);

            if (count($parameter) === 2)
            {
                $parameter[0] = trim($parameter[0]);
                $parameter[1] = trim($parameter[1], " \t\n\r\0\x0B\"");

                // boundary
                if ($parameter[0] == 'boundary')
                {
                    $this->setBoundary($parameter[1]);
                }
                // charset
                elseif ($parameter[0] == 'charset')
                {
                    $this->setCharset($parameter[1]);
                }
                // name
                elseif ($parameter[0] == 'name')
                {
                    $this->setName($parameter[1]);
                }
            }
        }
    }

    /**
     *
     * Sets a name.
     *
     * @param string $name a name
     * @return void
     *
     */
    public function setName(string $name) : void
    {
        $this->_fieldValue['name'] = $name;
    }

    /**
     *
     * Sets a type.
     *
     * @param string $type a type
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function setType(string $type) : void
    {
        if (preg_match('/^[a-z\-]+\/[a-z0-9.+\-]+$/i', $type) !== 1)
        {
            throw new Exception('invalid argument supplied for setType');
        }

        $this->_fieldValue['type'] = strtolower($type);
    }
}
