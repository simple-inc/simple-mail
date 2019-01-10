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

use Simple\Mail\Header\ContentDisposition;
use Simple\Mail\Header\ContentType;
use Simple\Mail\Part\Exception;

/**
 *
 * mail attachment class
 *
 * @package Simple\Mail\Part
 *
 */
class Attachment extends AbstractPart
{
    /**
     *
     * filename
     *
     * @var string
     *
     */
    protected $_filename;

    /**
     *
     * name
     *
     * @var string
     *
     */
    protected $_name;

    /**
     *
     * Constructs a new Attachment instance.
     *
     * @param string $filename a filename
     * @param string $name a name (optional)
     * @param string $charset a charset (optional)
     * @param string $encoding an encoding (optional)
     * @param array $headers headers (optional)
     *
     */
    public function __construct(string $filename, string $name = '', string $charset = '', string $encoding = '', array $headers = [])
    {
        $this->setFilename($filename);
        $this->setName($name);
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
        // content transfer encoding
        $contentTransferEncoding = 'base64';

        // file name not set
        if ($this->_name)
        {
            // set a file name
            $this->_name = basename($this->_filename);
        }

        // Content-Disposition not set
        if (!$this->isHeader('Content-Disposition'))
        {
            // set a Content-Disposition header
            $this->setHeader('Content-Disposition', new ContentDisposition('attachment', $this->_name, $this->_charset, $this->_encoding));
        }

        // Content-Type not set
        if (!$this->isHeader('Content-Type'))
        {
            // fileinfo
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $type = $finfo->file($this->_filename);

            // text
            if (strpos($type, 'text/') === 0)
            {
                $type = 'application/octet-stream';
            }

            // create a Content-Type header
            $contentType = new ContentType($type);
            $contentType->setName($this->_name);
            $contentType->setCharset($this->_charset);
            $contentType->setEncoding($this->_encoding);

            // set a Content-Type header
            $this->setHeader('Content-Type', $contentType);
        }

        // Content-Transfer-Encoding not set
        if (!$this->isHeader('Content-Transfer-Encoding'))
        {
            // set base64 content transfer encoding
            $this->setHeader('Content-Transfer-Encoding', $contentTransferEncoding);
        }

        // quoted-printable
        if ($contentTransferEncoding == 'quoted-printable')
        {
            return implode("\r\n", $this->_headers) . "\r\n\r\n" . quoted_printable_encode(file_get_contents($this->_filename));
        }

        // base64
        return implode("\r\n", $this->_headers) . "\r\n\r\n" . chunk_split(base64_encode(file_get_contents($this->_filename)));
    }

    /**
     *
     * Returns a filename.
     *
     * @return string
     *
     */
    public function getFilename() : string
    {
        return $this->_filename;
    }

    /**
     *
     * Returns a name.
     *
     * @return string
     *
     */
    public function getName() : string
    {
        return $this->_name;
    }

    /**
     *
     * Sets a filename.
     *
     * @param string $filename a filename
     * @throws \Simple\Mail\Part\Exception if an invalid argument supplied
     * @return void
     *
     */
    public function setFilename(string $filename) : void
    {
        if (!is_file($filename) || !file_exists($filename))
        {
            throw new Exception('invalid argument supplied for setFilename');
        }

        $this->_filename = $filename;
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
        $this->_name = $name;
    }
}
