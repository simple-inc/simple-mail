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
 * Content-Disposition mail header class
 *
 * @package Simple\Mail\Header
 *
 */
class ContentDisposition extends Header
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
     * Constructs a new ContentDisposition instance.
     *
     * @param string $disposition a disposition
     *
     */
    public function __construct(string $disposition)
    {
        parent::__construct('Content-Disposition', $disposition);
    }

    /**
     *
     * Returns a disposition.
     *
     * @return string
     *
     */
    public function getDisposition() : ?string
    {
        return $this->_fieldValue['disposition'] ?? null;
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
        $disposition = $this->getDisposition();
        $filename = $this->getFilename();

        // filename
        if ($filename !== null)
        {
            if ($encode === true)
            {
                $filename = Mime::encode($filename, $this->_charset, $this->_encoding);
            }

            return $disposition . '; filename="' . $filename . '"';
        }

        return $disposition;
    }

    /**
     *
     * Returns a filename.
     *
     * @return string
     *
     */
    public function getFilename() : ?string
    {
        return $this->_fieldValue['filename'] ?? null;
    }

    /**
     *
     * Returns true if disposition is attachment.
     *
     * @return boolean
     *
     */
    public function isAttachment() : bool
    {
        return $this->getDisposition() == 'attachment';
    }

    /**
     *
     * Returns true if disposition is inline.
     *
     * @return boolean
     *
     */
    public function isInline() : bool
    {
        return $this->getDisposition() == 'inline';
    }

    /**
     *
     * Returns a ContentDisposition instance.
     *
     * @param string $headerLine a headerLine
     * @throws \Simple\Mail\Header\Exception if an invalid argument supplied
     * @return \Simple\Mail\Header\ContentDisposition
     *
     */
    public static function parseString(string $headerLine) : HeaderInterface
    {
        $headerLine = explode(':', $headerLine, 2);
        $headerLine[0] = strtolower(trim($headerLine[0]));

        if ($headerLine[0] !== 'content-disposition' || count($headerLine) !== 2)
        {
            throw new Exception('invalid argument supplied for parseString');
        }

        $headerLine = trim($headerLine[1]);
        $parameters = explode(';', $headerLine);
        $contentDisposition = array_shift($parameters);
        $contentDisposition = new static($contentDisposition);

        $names = [];

        foreach ($parameters as $parameter)
        {
            $parameter = explode('=', $parameter, 2);

            if (count($parameter) === 2)
            {
                $parameter[0] = trim($parameter[0]);
                $parameter[1] = trim($parameter[1], " \t\n\r\0\x0B\"");

                // filename
                if ($parameter[0] == 'filename')
                {
                    $parameter[1] = Mime::decode($parameter[1]);
                    $contentDisposition->setFilename($parameter[1]);
                }
                // multiple filenames
                elseif (strpos($parameter[0], 'filename*') === 0)
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
            $contentDisposition->setFilename($names);
        }

        return $contentDisposition;
    }

    /**
     *
     * Sets a disposition.
     *
     * @param string $disposition a disposition
     * @return void
     *
     */
    public function setDisposition(string $disposition) : void
    {
        $this->_fieldValue['disposition'] = strtolower($disposition);
    }

    /**
     *
     * Sets a field value.
     *
     * @param string $fieldValue a field value
     * @return void
     *
     */
    public function setFieldValue($fieldValue) : void
    {
        $parameters = explode(';', $fieldValue);
        $disposition = array_shift($parameters);

        $this->setDisposition($disposition);

        foreach ($parameters as $parameter)
        {
            $parameter = explode('=', $parameter, 2);

            if (count($parameter) === 2)
            {
                $parameter[0] = trim($parameter[0]);
                $parameter[1] = trim($parameter[1], " \t\n\r\0\x0B\"");

                // filename
                if ($parameter[0] == 'filename')
                {
                    $this->setFilename($parameter[1]);
                }
            }
        }
    }

    /**
     *
     * Sets a filename.
     *
     * @param string $filename a filename
     * @return void
     *
     */
    public function setFilename(string $filename) : void
    {
        $this->_fieldValue['filename'] = $filename;
    }
}
