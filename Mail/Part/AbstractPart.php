<?php

declare(strict_types=1);

/**
 *
 * Simple Framework
 *
 * @copyright Simple Inc. All rights reserved.
 *
 */

namespace Simple\Mail\Part;

use Simple\Mail\Header;
use Simple\Mail\Header\HeaderInterface;
use Simple\Mail\Part\PartInterface;

/**
 *
 * abstract mail part class
 *
 * @package Simple\Mail\Part
 *
 */
abstract class AbstractPart implements PartInterface
{
    /**
     *
     * charset
     *
     * @var string
     *
     */
    protected $_charset;

    /**
     *
     * encoding
     *
     * @var string
     *
     */
    protected $_encoding;

    /**
     *
     * headers
     *
     * @var array
     *
     */
    protected $_headers;

    /**
     *
     * Returns a charset.
     *
     * @return string
     *
     */
    public function getCharset() : string
    {
        return $this->_charset;
    }

    /**
     *
     * Returns an encoding.
     *
     * @return string
     *
     */
    public function getEncoding() : string
    {
        return $this->_encoding;
    }

    /**
     *
     * Returns a header.
     *
     * @param string $name a name
     * @return \Simple\Mail\Header\HeaderInterface|null
     *
     */
    public function getHeader(string $name) : ?HeaderInterface
    {
        return $this->_headers[$name] ?? null;
    }

    /**
     *
     * Returns headers.
     *
     * @return array
     *
     */
    public function getHeaders() : array
    {
        return $this->_headers;
    }

    /**
     *
     * Returns true if header exists.
     *
     * @param string $name a name
     * @return boolean
     *
     */
    public function isHeader(string $name) : bool
    {
        return isset($this->_headers[$name]);
    }

    /**
     *
     * Removes a header.
     *
     * @param string $name a name
     * @return void
     *
     */
    public function removeHeader(string $name) : void
    {
        if (isset($this->_headers[$name]))
        {
            unset($this->_headers[$name]);
        }
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
     * Sets a header.
     *
     * @param string $name a name
     * @param string|\Simple\Mail\Header\HeaderInterface $value a value
     * @return void
     *
     */
    public function setHeader(string $name, $value) : void
    {
        if (!($value instanceof HeaderInterface))
        {
            $value = Header::parseString($name . ': ' . $value);
        }

        if ($value instanceof HeaderInterface)
        {
            $this->_headers[$name] = $value;
        }
    }

    /**
     *
     * Sets headers.
     *
     * @param array $headers headers
     * @return void
     *
     */
    public function setHeaders(array $headers) : void
    {
        foreach ($headers as $name => $value)
        {
            $this->setHeader($name, $value);
        }
    }
}
