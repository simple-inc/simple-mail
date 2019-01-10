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

use Simple\Mail\Exception;

/**
 *
 * mail address class
 *
 * @package Simple\Mail
 *
 */
class Address
{
    /**
     *
     * address
     *
     * @var string
     *
     */
    protected $_address;

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
     * name
     *
     * @var string
     *
     */
    protected $_name;

    /**
     *
     * Constructs a new Address instance.
     *
     * @param string $address an address
     * @param string $name a name (optional)
     * @param string $charset a charset (optional)
     * @param string $encoding an encoding (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     *
     */
    public function __construct(string $address, string $name = '', string $charset = '', string $encoding = '')
    {
        $this->setAddress($address);
        $this->setName($name);
        $this->setCharset($charset);
        $this->setEncoding($encoding);
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
        if (is_string($this->_name) && strlen($this->_name) > 0)
        {
            return Mime::encode($this->_name, $this->_charset, $this->_encoding) . ' <' . $this->_address . '>';
        }

        return $this->_address;
    }

    /**
     *
     * Returns an address.
     *
     * @return string
     *
     */
    public function getAddress() : string
    {
        return $this->_address;
    }

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
     * Returns an Address instance.
     *
     * @param string $value a value
     * return \Simple\Mail\Address
     *
     */
    public static function parseString(string $value) : Address
    {
        $value = Mime::decode($value);
        $value = explode(' ', trim($value));
        $address = array_pop($value);
        $address = trim(str_replace(['<', '>'], '', $address));
        $name = count($value) > 0 ? trim(implode(' ', $value), " \t\n\r\0\x0B'\"") : '';

        return new static($address, $name);
    }

    /**
     *
     * Sets an address.
     *
     * @param string $address an address
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function setAddress(string $address) : void
    {
        if (filter_var($address, FILTER_VALIDATE_EMAIL) === false)
        {
            throw new Exception('invalid argument supplied for setAddress');
        }

        $this->_address = $address;
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
