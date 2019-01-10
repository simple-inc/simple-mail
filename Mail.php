<?php

declare(strict_types=1);

/**
 *
 * Simple Framework
 *
 * @copyright Simple Inc. All rights reserved.
 *
 */

namespace Simple;

use Simple\Mail\Address;
use Simple\Mail\Exception;
use Simple\Mail\Header;
use Simple\Mail\Header\Bcc;
use Simple\Mail\Header\Cc;
use Simple\Mail\Header\ErrorsTo;
use Simple\Mail\Header\From;
use Simple\Mail\Header\HeaderInterface;
use Simple\Mail\Header\MessageId;
use Simple\Mail\Header\MimeVersion;
use Simple\Mail\Header\ReplyTo;
use Simple\Mail\Header\ReturnPath;
use Simple\Mail\Header\Sender;
use Simple\Mail\Header\Subject;
use Simple\Mail\Header\To;
use Simple\Mail\Mime;
use Simple\Mail\Part\Attachment;
use Simple\Mail\Part\HtmlTextPart;
use Simple\Mail\Part\PartInterface;
use Simple\Mail\Part\PlainTextPart;
use Simple\Mail\Transport\Php as PhpTransport;
use Simple\Mail\Transport\TransportInterface;
use TypeError;

/**
 *
 * mail class
 *
 * @package Simple
 *
 */
class Mail
{
    /**
     *
     * headers
     *
     * @var array
     *
     */
    protected $_headers = [];

    /**
     *
     * parts
     *
     * @var array
     *
     */
    protected $_parts = [];

    /**
     *
     * transport
     *
     * @var \Simple\Mail\Transport\TransportInterface
     *
     */
    protected $_transport;

    /**
     *
     * Constructs a new Mail instance.
     *
     * @param array $options options (optional)
     * @throws \Simple\Mail\Exception if an invalid option is supplied
     *
     */
    public function __construct(array $options = null)
    {
        if (is_array($options))
        {
            if (isset($options['from']) && is_array($options['from']))
            {
                $address = $options['from']['address'] ?? null;
                $name = $options['from']['name'] ?? '';
                $charset = $options['from']['charset'] ?? '';
                $encoding = $options['from']['encoding'] ?? '';

                $this->addFrom($address, $name, $charset, $encoding);
            }

            if (isset($options['transport']) && is_array($options['transport']))
            {
                $transportClass = $options['transport']['class'] ?? 'PhpTransport';
                $transportOptions = $options['transport']['options'] ?? [];
                $transport = new $transportClass($transportOptions);
                $this->setTransport($transport);
            }
        }

        if ($this->_transport === null)
        {
            $this->_transport = new PhpTransport();
        }

        $this->setHeader('MIME-Version', '1.0');
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
        // get a number of parts
        $count = count($this->_parts);

        // single part
        if ($count === 1)
        {
            // body part is an attachment
            if ($this->_parts[0] instanceof Attachment)
            {
                // create a boundary
                $boundary = Mime::getBoundary();

                // set a multipart/mixed Content-Type
                $this->setHeader('Content-Type', 'multipart/mixed; boundary=' . $boundary);

                return implode("\r\n", $this->_headers) . "\r\n\r\n--" . $boundary . "\r\n" . $this->_parts[0] . "\r\n\r\n--" . $boundary . '--';
            }

            return implode("\r\n", $this->_headers) . "\r\n" . $this->_parts[0];
        }
        // multiple parts
        elseif ($count > 1)
        {
            // Content-Type
            $contentType = $count == 2 ? 'multipart/alternative' : 'multipart/mixed';

            // boundary
            $boundary = Mime::getBoundary();

            // parts
            $parts = '';

            foreach ($this->_parts as $part)
            {
                // attachment found
                if ($part instanceof Attachment)
                {
                    // multipart/mixed Content-Type
                    $contentType = 'multipart/mixed';
                }

                $parts .= "\r\n\r\n--" . $boundary . "\r\n" . $part;
            }

            // set a Content-Type
            $this->setHeader('Content-Type', $contentType . '; boundary=' . $boundary);

            return implode("\r\n", $this->_headers) . $parts . "\r\n\r\n--" . $boundary . '--';
        }

        return implode("\r\n", $this->_headers) . implode("\r\n", $this->_parts);
    }

    /**
     *
     * Adds an attachment.
     *
     * @param \Simple\Mail\Part\Attachment|string $attachment an attachment
     * @param string $name a name (optional)
     * @param string $charset a charset (optional)
     * @param string encoding an $encoding (optional)
     * @param array $headers headers (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return int
     *
     */
    public function addAttachment($attachment, string $name = '', string $charset = '', string $encoding = '', array $headers = []) : int
    {
        if (is_string($attachment))
        {
            $attachment = new Attachment($attachment);
        }

        if (!($attachment instanceof Attachment))
        {
            throw new Exception('invalid argument supplied for addAttachment');
        }

        $attachment->setName($name);
        $attachment->setCharset($charset);
        $attachment->setEncoding($encoding);
        $attachment->setHeaders($headers);

        return $this->addPart($attachment);
    }

    /**
     *
     * Adds an address to a "Bcc" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @param string $name a name (optional)
     * @param string $charset a charset (optional)
     * @param string encoding an $encoding (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function addBcc($address, string $name = '', string $charset = '', string $encoding = '') : void
    {
        if (is_string($address))
        {
            $address = Address::parseString($address);
        }

        if (!($address instanceof Address))
        {
            throw new Exception('invalid argument supplied for addBcc');
        }

        $address->setName($name);
        $address->setCharset($charset);
        $address->setEncoding($encoding);

        if (!isset($this->_headers['Bcc']))
        {
            $this->_headers['Bcc'] = new Bcc();
        }

        $this->_headers['Bcc']->setAddress($address);
    }

    /**
     *
     * Adds an address to a "Cc" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @param string $name a name (optional)
     * @param string $charset a charset (optional)
     * @param string encoding an $encoding (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function addCc($address, string $name = '', string $charset = '', string $encoding = '') : void
    {
        if (is_string($address))
        {
            $address = Address::parseString($address);
        }

        if (!($address instanceof Address))
        {
            throw new Exception('invalid argument supplied for addCc');
        }

        $address->setName($name);
        $address->setCharset($charset);
        $address->setEncoding($encoding);

        if (!isset($this->_headers['Cc']))
        {
            $this->_headers['Cc'] = new Cc();
        }

        $this->_headers['Cc']->setAddress($address);
    }

    /**
     *
     * Adds an address to an "Errors-To" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @param string $name a name (optional)
     * @param string $charset a charset (optional)
     * @param string encoding an $encoding (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function addErrorsTo($address, string $name = '', string $charset = '', string $encoding = '') : void
    {
        if (is_string($address))
        {
            $address = Address::parseString($address);
        }

        if (!($address instanceof Address))
        {
            throw new Exception('invalid argument supplied for addErrorsTo');
        }

        $address->setName($name);
        $address->setCharset($charset);
        $address->setEncoding($encoding);

        if (!isset($this->_headers['Errors-To']))
        {
            $this->_headers['Errors-To'] = new ErrorsTo();
        }

        $this->_headers['Errors-To']->setAddress($address);
    }

    /**
     *
     * Adds an address to a "From" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @param string $name a name (optional)
     * @param string $charset a charset (optional)
     * @param string encoding an $encoding (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function addFrom($address, string $name = '', string $charset = '', string $encoding = '') : void
    {
        if (is_string($address))
        {
            $address = Address::parseString($address);
        }

        if (!($address instanceof Address))
        {
            throw new Exception('invalid argument supplied for addFrom');
        }

        $address->setName($name);
        $address->setCharset($charset);
        $address->setEncoding($encoding);

        if (!isset($this->_headers['From']))
        {
            $this->_headers['From'] = new From();
        }

        $this->_headers['From']->setAddress($address);
    }

    /**
     *
     * Adds an HTML text part.
     *
     * @param \Simple\Mail\Part\HtmlTextPart|string $htmlTextPart an HTML text part
     * @param string $charset a charset (optional)
     * @param string encoding an $encoding (optional)
     * @param array $headers headers (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return int
     *
     */
    public function addHtmlTextPart($htmlTextPart, string $charset = '', string $encoding = '', array $headers = []) : int
    {
        if (is_string($htmlTextPart))
        {
            $htmlTextPart = new HtmlTextPart($htmlTextPart);
        }

        if (!($htmlTextPart instanceof HtmlTextPart))
        {
            throw new Exception('invalid argument supplied for addHtmlTextPart');
        }

        $htmlTextPart->setCharset($charset);
        $htmlTextPart->setEncoding($encoding);
        $htmlTextPart->setHeaders($headers);

        return $this->addPart($htmlTextPart);
    }

    /**
     *
     * Adds a part.
     *
     * @param \Simple\Mail\Part\PartInterface $part a part
     * @return int
     *
     */
    public function addPart(PartInterface $part) : int
    {
        $this->_parts[] = $part;

        return count($this->_parts) - 1;
    }

    /**
     *
     * Adds a plain text part.
     *
     * @param \Simple\Mail\Part\PlainTextPart|string $plainTextPart a plain text part
     * @param string $charset a charset (optional)
     * @param string encoding an $encoding (optional)
     * @param array $headers headers (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return int
     *
     */
    public function addPlainTextPart($plainTextPart, string $charset = '', string $encoding = '', array $headers = []) : int
    {
        if (is_string($plainTextPart))
        {
            $plainTextPart = new PlainTextPart($plainTextPart);
        }

        if (!($plainTextPart instanceof PlainTextPart))
        {
            throw new Exception('invalid argument supplied for addPlainText');
        }

        $plainTextPart->setCharset($charset);
        $plainTextPart->setEncoding($encoding);
        $plainTextPart->setHeaders($headers);

        return $this->addPart($plainTextPart);
    }

    /**
     *
     * Adds an address to a "Reply-To" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @param string $name a name (optional)
     * @param string $charset a charset (optional)
     * @param string encoding an $encoding (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function addReplyTo($address, string $name = '', string $charset = '', string $encoding = '') : void
    {
        if (is_string($address))
        {
            $address = Address::parseString($address);
        }

        if (!($address instanceof Address))
        {
            throw new Exception('invalid argument supplied for addReplyTo');
        }

        $address->setName($name);
        $address->setCharset($charset);
        $address->setEncoding($encoding);

        if (!isset($this->_headers['Reply-To']))
        {
            $this->_headers['Reply-To'] = new ReplyTo();
        }

        $this->_headers['Reply-To']->setAddress($address);
    }

    /**
     *
     * Adds an address to a "To" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @param string $name a name (optional)
     * @param string $charset a charset (optional)
     * @param string encoding an $encoding (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function addTo($address, string $name = '', string $charset = '', string $encoding = '') : void
    {
        if (is_string($address))
        {
            $address = Address::parseString($address);
        }

        if (!($address instanceof Address))
        {
            throw new Exception('invalid argument supplied for addTo');
        }

        $address->setName($name);
        $address->setCharset($charset);
        $address->setEncoding($encoding);

        if (!isset($this->_headers['To']))
        {
            $this->_headers['To'] = new To();
        }

        $this->_headers['To']->setAddress($address);
    }

    /**
     *
     * Returns a "Bcc" header.
     *
     * @return \Simple\Mail\Header\Bcc|null
     *
     */
    public function getBcc() : ?Bcc
    {
        return $this->getHeader('Bcc');
    }

    /**
     *
     * Returns a "Cc" header.
     *
     * @return \Simple\Mail\Header\Cc|null
     *
     */
    public function getCc() : ?Cc
    {
        return $this->getHeader('Cc');
    }

    /**
     *
     * Returns a "Errors-To" header.
     *
     * @return \Simple\Mail\Header\ErrorsTo|null
     *
     */
    public function getErrorsTo() : ?ErrorsTo
    {
        return $this->getHeader('Errors-To');
    }

    /**
     *
     * Returns a "From" header.
     *
     * @return \Simple\Mail\Header\From|null
     *
     */
    public function getFrom() : ?From
    {
        return $this->getHeader('From');
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
     * Returns a "Message-ID" header.
     *
     * @return \Simple\Mail\Header\MessageId|null
     *
     */
    public function getMessageId() : ?MessageId
    {
        return $this->getHeader('Message-ID');
    }

    /**
     *
     * Returns a part.
     *
     * @param int $index an index
     * @return \Simple\Mail\Part\PartInterface|null
     *
     */
    public function getPart(int $index) : ?PartInterface
    {
        return $this->_parts[$index] ?? null;
    }

    /**
     *
     * Returns parts.
     *
     * @return array
     *
     */
    public function getParts() : array
    {
        return $this->_parts;
    }

    /**
     *
     * Returns a "Reply-To" header.
     *
     * @return \Simple\Mail\Header\ReplyTo|null
     *
     */
    public function getReplyTo() : ?ReplyTo
    {
        return $this->getHeader('Reply-To');
    }

    /**
     *
     * Returns a "Return-Path" header.
     *
     * @return \Simple\Mail\Header\ReturnPath|null
     *
     */
    public function getReturnPath() : ?ReturnPath
    {
        return $this->getHeader('Return-Path');
    }

    /**
     *
     * Returns a "Sender" header.
     *
     * @return \Simple\Mail\Header\Sender|null
     *
     */
    public function getSender() : ?Sender
    {
        return $this->getHeader('Sender');
    }

    /**
     *
     * Returns a "Subject" header.
     *
     * @return \Simple\Mail\Header\Subject|null
     *
     */
    public function getSubject() : ?Subject
    {
        return $this->getHeader('Subject');
    }

    /**
     *
     * Returns a "To" header.
     *
     * @return \Simple\Mail\Header\To|null
     *
     */
    public function getTo() : ?To
    {
        return $this->getHeader('To');
    }

    /**
     *
     * Returns a transport.
     *
     * @return \Simple\Mail\Transport\TransportInterface
     *
     */
    public function getTransport() : ?TransportInterface
    {
        return $this->_transport;
    }

    /**
     *
     * Returns true if a header exists.
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
     * Removes an address from a "Bcc" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @return void
     *
     */
    public function removeBcc($address) : void
    {
        if (isset($this->_headers['Bcc']))
        {
            $this->_headers['Bcc']->removeAddress($address);
        }
    }

    /**
     *
     * Removes an address from a "Cc" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @return void
     *
     */
    public function removeCc($address) : void
    {
        if (isset($this->_headers['Cc']))
        {
            $this->_headers['Cc']->removeAddress($address);
        }
    }

    /**
     *
     * Removes an address from a "Errors-To" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @return void
     *
     */
    public function removeErrorsTo($address) : void
    {
        if (isset($this->_headers['Errors-To']))
        {
            $this->_headers['Errors-To']->removeAddress($address);
        }
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
     * Removes an address from a "From" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @return void
     *
     */
    public function removeFrom($address) : void
    {
        if (isset($this->_headers['From']))
        {
            $this->_headers['From']->removeAddress($address);
        }
    }

    /**
     *
     * Removes a part.
     *
     * @param int $index an index
     * @return void
     *
     */
    public function removePart(int $index) : void
    {
        if (isset($this->_parts[$index]))
        {
            unset($this->_parts[$index]);
        }
    }

    /**
     *
     * Removes an address from a "Reply-To" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @return void
     *
     */
    public function removeReplyTo($address) : void
    {
        if (isset($this->_headers['Reply-To']))
        {
            $this->_headers['Reply-To']->removeAddress($address);
        }
    }

    /**
     *
     * Removes an address from a "To" header.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @return void
     *
     */
    public function removeTo($address) : void
    {
        if (isset($this->_headers['To']))
        {
            $this->_headers['To']->removeAddress($address);
        }
    }

    /**
     *
     * Sends this message.
     *
     * @throws \Simple\Mail\Exception if an error occurs
     * @return boolean
     *
     */
    public function send() : bool
    {
        // message
        $message = '';

        // get a number of parts
        $count = count($this->_parts);

        // single part
        if ($count === 1)
        {
            // body part is an attachment
            if ($this->_parts[0] instanceof Attachment)
            {
                // boundary
                $boundary = Mime::getBoundary();

                // set a multipart/mixed Content-Type header
                $this->setHeader('Content-Type', 'multipart/mixed; boundary=' . $boundary);

                $message = '--' . $boundary . "\r\n" . $this->_parts[0] . "\r\n\r\n--" . $boundary . '--';
            }
            else
            {
                // remove headers
                $message = (string)$this->_parts[0];
                $message = explode("\r\n\r\n", $message);
                array_shift($message);
                $message = implode("\r\n\r\n", $message);

                $headers = $this->_parts[0]->getHeaders();
                $this->setHeaders($headers);
            }
        }
        // multi parts
        elseif ($count > 1)
        {
            // Content-Type
            $contentType = $count == 2 ? 'multipart/alternative' : 'multipart/mixed';

            // boundary
            $boundary = Mime::getBoundary();

            foreach ($this->_parts as $part)
            {
                // attachment found
                if ($part instanceof Attachment)
                {
                    // multipart/mixed Content-Type
                    $contentType = 'multipart/mixed';
                }

                $message .= "\r\n\r\n--" . $boundary . "\r\n" . $part;
            }

            // set a content type
            $this->setHeader('Content-Type', $contentType . '; boundary=' . $boundary);

            $message = trim($message . "\r\n\r\n--" . $boundary . '--');
        }

        // Date
        if (!$this->isHeader('Date'))
        {
            $this->setHeader('Date', date('r'));
        }

        // Message-ID
        if (!$this->isHeader('Message-ID') && $this->isHeader('From'))
        {
            $address = $this->_headers['From']->current();
            $address = $address->getAddress();
            $address = explode('@', $address);
            $messageId = md5(uniqid($address[0], true)) . '@' . $address[1];

            $this->setMessageId($messageId);
        }

        return $this->_transport->send($this->_headers, $message);
    }

    /**
     *
     * Sets a "Bcc" header.
     *
     * @param \Simple\Mail\Address|\Simple\Mail\Header\Bcc|array|string|null $bcc a bcc
     * @param string|null $charset a charset (optional)
     * @param string|null $encoding an encoding (optional)
     * @return void
     *
     */
    public function setBcc($bcc, string $charset = '', string $encoding = '') : void
    {
        if (empty($bcc))
        {
            $this->removeHeader('Bcc');
        }
        else
        {
            if ($bcc instanceof Bcc)
            {
                $this->_headers['Bcc'] = $bcc;
            }
            elseif (!isset($this->_headers['Bcc']))
            {
                $this->_headers['Bcc'] = new Bcc($bcc);
            }
            else
            {
                $this->_headers['Bcc']->setFieldValue($bcc);
            }

            $this->_headers['Bcc']->setCharset($charset);
            $this->_headers['Bcc']->setEncoding($encoding);
        }
    }

    /**
     *
     * Sets a "Cc" header.
     *
     * @param \Simple\Mail\Address|\Simple\Mail\Header\Cc|array|string|null $cc a cc
     * @param string|null $charset a charset (optional)
     * @param string|null $encoding an encoding (optional)
     * @return void
     *
     */
    public function setCc($cc, string $charset = '', string $encoding = '') : void
    {
        if (empty($cc))
        {
            $this->removeHeader('Cc');
        }
        else
        {
            if ($cc instanceof Cc)
            {
                $this->_headers['Cc'] = $cc;
            }
            elseif (!isset($this->_headers['Cc']))
            {
                $this->_headers['Cc'] = new Cc($cc);
            }
            else
            {
                $this->_headers['Cc']->setFieldValue($cc);
            }

            $this->_headers['Cc']->setCharset($charset);
            $this->_headers['Cc']->setEncoding($encoding);
        }
    }

    /**
     *
     * Sets an "Errors-To" header.
     *
     * @param \Simple\Mail\Address|\Simple\Mail\Header\ErrorsTo|array|string|null $errorsTo an errors to
     * @param string|null $charset a charset (optional)
     * @param string|null $encoding an encoding (optional)
     * @return void
     *
     */
    public function setErrorsTo($errorsTo, string $charset = '', string $encoding = '') : void
    {
        if (empty($errorsTo))
        {
            $this->removeHeader('Errors-To');
        }
        else
        {
            if ($errorsTo instanceof ErrorsTo)
            {
                $this->_headers['Errors-To'] = $errorsTo;
            }
            elseif (!isset($this->_headers['Errors-To']))
            {
                $this->_headers['Errors-To'] = new ErrorsTo($errorsTo);
            }
            else
            {
                $this->_headers['Errors-To']->setFieldValue($errorsTo);
            }

            $this->_headers['Errors-To']->setCharset($charset);
            $this->_headers['Errors-To']->setEncoding($encoding);
        }
    }

    /**
     *
     * Sets a "From" header.
     *
     * @param \Simple\Mail\Address|\Simple\Mail\Header\From|array|string|null $from a from
     * @param string|null $charset a charset (optional)
     * @param string|null $encoding an encoding (optional)
     * @return void
     *
     */
    public function setFrom($from, string $charset = '', string $encoding = '') : void
    {
        if (empty($from))
        {
            $this->removeHeader('From');
        }
        else
        {
            if ($from instanceof From)
            {
                $this->_headers['From'] = $from;
            }
            elseif (!isset($this->_headers['From']))
            {
                $this->_headers['From'] = new From($from);
            }
            else
            {
                $this->_headers['From']->setFieldValue($from);
            }

            $this->_headers['From']->setCharset($charset);
            $this->_headers['From']->setEncoding($encoding);
        }
    }

    /**
     *
     * Sets a header.
     *
     * @param string $name a name
     * @param \Simple\Mail\Header\HeaderInterface|string $value a value
     * @return void
     *
     */
    public function setHeader(string $name, $value) : void
    {
        if (empty($value))
        {
            $this->removeHeader($name);
        }
        elseif ($value instanceof HeaderInterface)
        {
            $this->_headers[$value->getFieldName()] = $value;
        }
        else
        {
            $header = Header::parseString($name . ': ' . $value);

            $this->_headers[$header->getFieldName()] = $header;
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

    /**
     *
     * Sets a message ID.
     *
     * @param \Simple\Mail\Header\MessageId|string $messageId a message ID
     * @return void
     *
     */
    public function setMessageId($messageId) : void
    {
        if (empty($messageId))
        {
            $this->removeHeader('Message-ID');
        }
        else
        {
            if ($messageId instanceof MessageId)
            {
                $this->_headers['Message-ID'] = $messageId;
            }
            elseif (!isset($this->_headers['Message-ID']))
            {
                $this->_headers['Message-ID'] = new MessageId($messageId);
            }
            else
            {
                $this->_headers['Message-ID']->setFieldValue($messageId);
            }
        }
    }

    /**
     *
     * Sets a part.
     *
     * @param \Simple\Mail\Part\PartInterface $part a part
     * @param int $index an index
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function setPart(PartInterface $part, int $index) : void
    {
        if ($index < 0 || $index > count($this->_parts))
        {
            throw new Exception('invalid argument supplied for setPart');
        }

        $this->_parts[$index] = $part;
    }

    /**
     *
     * Sets parts.
     *
     * @param array $parts parts
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function setParts(array $parts) : void
    {
        $this->_parts = [];

        foreach ($parts as $part)
        {
            if (!$part instanceof PartInterface)
            {
                throw new Exception('invalid argument supplied for setParts');
            }

            $this->_parts[] = $part;
        }
    }

    /**
     *
     * Sets a "Reply-To" header.
     *
     * @param \Simple\Mail\Address|\Simple\Mail\Header\ReplyTo|array|string|null $replyTo a reply to
     * @param string|null $charset a charset (optional)
     * @param string|null $encoding an encoding (optional)
     * @return void
     *
     */
    public function setReplyTo($replyTo, string $charset = '', string $encoding = '') : void
    {
        if (empty($replyTo))
        {
            $this->removeHeader('Reply-To');
        }
        else
        {
            if ($replyTo instanceof ReplyTo)
            {
                $this->_headers['Reply-To'] = $replyTo;
            }
            elseif (!isset($this->_headers['Reply-To']))
            {
                $this->_headers['Reply-To'] = new ReplyTo($replyTo);
            }
            else
            {
                $this->_headers['Reply-To']->setFieldValue($replyTo);
            }

            $this->_headers['Reply-To']->setCharset($charset);
            $this->_headers['Reply-To']->setEncoding($encoding);
        }
    }

    /**
     *
     * Sets a "Return-Path" header.
     *
     * @param \Simple\Mail\Address|\Simple\Mail\Header\ReturnPath|string|null $returnPath a return path
     * @return void
     *
     */
    public function setReturnPath($returnPath) : void
    {
        if (empty($returnPath))
        {
            $this->removeHeader('Return-Path');
        }
        else
        {
            if ($returnPath instanceof ReturnPath)
            {
                $this->_headers['Return-Path'] = $returnPath;
            }
            elseif (!isset($this->_headers['Return-Path']))
            {
                $this->_headers['Return-Path'] = new ReturnPath($returnPath);
            }
            else
            {
                $this->_headers['Return-Path']->setFieldValue($returnPath);
            }
        }
    }

    /**
     *
     * Sets a "Sender" header.
     *
     * @param \Simple\Mail\Address|\Simple\Mail\Header\Sender|string|null $sender a sender
     * @return void
     *
     */
    public function setSender($sender) : void
    {
        if (empty($sender))
        {
            $this->removeHeader('Sender');
        }
        else
        {
            if ($sender instanceof Sender)
            {
                $this->_headers['Sender'] = $sender;
            }
            elseif (!isset($this->_headers['Sender']))
            {
                $this->_headers['Sender'] = new Sender($sender);
            }
            else
            {
                $this->_headers['Sender']->setFieldValue($sender);
            }
        }
    }

    /**
     *
     * Sets a "Subject" header.
     *
     * @param \Simple\Mail\Header\Subject|string|null $subject a subject
     * @param string|null $charset a charset (optional)
     * @param string|null $encoding an encoding (optional)
     * @return void
     *
     */
    public function setSubject($subject, string $charset = '', string $encoding = '') : void
    {
        if (empty($subject))
        {
            $this->removeHeader('Subject');
        }
        else
        {
            if ($subject instanceof Subject)
            {
                $this->_headers['Subject'] = $subject;
            }
            elseif (!isset($this->_headers['Subject']))
            {
                $this->_headers['Subject'] = new Subject($subject);
            }
            else
            {
                $this->_headers['Subject']->setFieldValue($subject);
            }

            $this->_headers['Subject']->setCharset($charset);
            $this->_headers['Subject']->setEncoding($encoding);
        }
    }

    /**
     *
     * Sets a "To" header.
     *
     * @param \Simple\Mail\Address|\Simple\Mail\Header\To|array|string|null $to a to
     * @param string|null $charset a charset (optional)
     * @param string|null $encoding an encoding (optional)
     * @return void
     *
     */
    public function setTo($to, string $charset = '', string $encoding = '') : void
    {
        if (empty($to))
        {
            $this->removeHeader('To');
        }
        else
        {
            if ($to instanceof To)
            {
                $this->_headers['To'] = $to;
            }
            elseif (!isset($this->_headers['To']))
            {
                $this->_headers['To'] = new To($to);
            }
            else
            {
                $this->_headers['To']->setFieldValue($to);
            }

            $this->_headers['To']->setCharset($charset);
            $this->_headers['To']->setEncoding($encoding);
        }
    }

    /**
     *
     * Sets a transport.
     *
     * @param \Simple\Mail\Transport\TransportInterface $transport a transport
     * @return void
     *
     */
    public function setTransport(TransportInterface $transport) : void
    {
        $this->_transport = $transport;
    }
}
