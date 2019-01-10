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

use Simple\Mail\Part\AbstractPart;

/**
 *
 * abstract mail text part class
 *
 * @package Simple\Mail\Part
 *
 */
abstract class AbstractTextPart extends AbstractPart
{
    /**
     *
     * content
     *
     * @var string
     *
     */
    protected $_content;

    /**
     *
     * content type
     *
     * @var string
     *
     */
    protected $_contentType;

    /**
     *
     * Constructs a new PlainTextPart instance.
     *
     * @param string $content a content
     * @param string $charset a charset (optional)
     * @param string $encoding an encoding (optional)
     * @param array $headers headers (optional)
     *
     */
    public function __construct(string $content, string $charset = '', string $encoding = '', array $headers = [])
    {
        $this->setContent($content);
        $this->setCharset($charset);
        $this->setEncoding($encoding);
        $this->setHeaders($headers);
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
        // charset not set
        if ($this->_charset === '')
        {
            // try detecting charset encoding
            $charset = mb_detect_encoding($this->_content);

            // charset found
            if ($charset !== false)
            {
                // set a charset
                $this->_charset = $charset;
            }
        }

        // charset exists
        if ($this->_charset)
        {
            // set a Content-Type header
            $this->setHeader('Content-Type', $this->_contentType . '; charset=' . $this->_charset);
        }
        else
        {
            // set a Content-Type header
            $this->setHeader('Content-Type', $this->_contentType);
        }

        // set a Content-Transfer-Encoding header
        if ($this->_encoding)
        {
            $this->setHeader('Content-Transfer-Encoding', $this->_encoding);
        }
        else
        {
            $this->setHeader('Content-Transfer-Encoding', '7bit');
        }

        // quoted-printable
        if ($this->_encoding == 'quoted-printable')
        {
            // charset exists
            if ($this->_charset)
            {
                return implode("\r\n", $this->_headers) . "\r\n\r\n" . quoted_printable_encode(mb_convert_encoding($this->_content, $this->_charset, mb_detect_encoding($this->_content)));
            }

            return implode("\r\n", $this->_headers) . "\r\n\r\n" . quoted_printable_encode($this->_content);
        }
        // base64
        elseif ($this->_encoding == 'base64')
        {
            // charset exists
            if ($this->_charset)
            {
                return implode("\r\n", $this->_headers) . "\r\n\r\n" . base64_encode(mb_convert_encoding($this->_content, $this->_charset, mb_detect_encoding($this->_content)));
            }

            return implode("\r\n", $this->_headers) . "\r\n\r\n" . base64_encode($this->_content);
        }

        // charset exists
        if ($this->_charset)
        {
            return implode("\r\n", $this->_headers) . "\r\n\r\n" . mb_convert_encoding($this->_content, $this->_charset, mb_detect_encoding($this->_content));
        }

        return implode("\r\n", $this->_headers) . "\r\n\r\n" . $this->_content;
    }

    /**
     *
     * Returns a content.
     *
     * @return string
     *
     */
    public function getContent() : string
    {
        return $this->_content;
    }

    /**
     *
     * Sets a content.
     *
     * @param string $content a content
     * @return void
     *
     */
    public function setContent(string $content) : void
    {
        $this->_content = trim($content);
    }
}
