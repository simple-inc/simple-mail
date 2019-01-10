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

use Countable;
use Iterator;
use Simple\Mail\Address;
use Simple\Mail\Header;
use Simple\Mail\Header\Exception;
use Simple\Mail\Mime;

/**
 *
 * abstract mail address header class
 *
 * @package Simple\Mail\Header
 *
 */
abstract class AbstractAddressHeader extends Header implements Countable, Iterator
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
     * Constructs a new AbstractAddressHeader instance.
     *
     * @param \Simple\Mail\Address|array|string $value a value (optional)
     * @throws \Simple\Mail\Header\Exception if an invalid argument is supplied
     *
     */
    public function __construct($value = null)
    {
        $this->setFieldValue($value);
    }

    /**
     *
     * Returns a count.
     *
     * @return int
     *
     */
    public function count() : int
    {
        return count($this->_fieldValue);
    }

    /**
     *
     * Returns a current item.
     *
     * @return mixed
     *
     */
    public function current()
    {
        return current($this->_fieldValue);
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
        if ($encode !== true)
        {
            return Mime::decode(implode(', ', $this->_fieldValue));
        }

        return implode(', ', $this->_fieldValue);
    }

    /**
     *
     * Returns a key of the current item.
     *
     * @return mixed
     *
     */
    public function key()
    {
        return key($this->_fieldValue);
    }

    /**
     *
     * Returns a next item.
     *
     * @return void
     *
     */
    public function next() : void
    {
        next($this->_fieldValue);
    }

    /**
     *
     * Returns an AbstractAddressHeader instance.
     *
     * @param string $headerLine a header line
     * @throws \Simple\Mail\Header\Exception if an invalid argument is supplied
     * @return \Simple\Mail\Header\AbstractAddressHeader
     *
     */
    public static function parseString(string $headerLine) : HeaderInterface
    {
        $headerLine = explode(':', $headerLine, 2);
        $headerLine[0] = trim($headerLine[0]);

        $instance = new static();

        if (strcasecmp($headerLine[0], $instance->getFieldName()) !== 0 || count($headerLine) !== 2)
        {
            throw new Exception('invalid argument supplied for parseString');
        }

        $headerLine[1] = Mime::decode(trim($headerLine[1]));

        $instance->setFieldValue($headerLine[1]);

        return $instance;
    }

    /**
     *
     * Removes an address.
     *
     * @param string|\Simple\Mail\Address $address an address
     * @return void
     *
     */
    public function removeAddress($address) : void
    {
        if ($address instanceof Address)
        {
            $address = $address->getAddress();
        }

        $address = strtolower($address);

        if (isset($this->_fieldValue[$address]))
        {
            unset($this->_fieldValue[$address]);
        }
    }

    /**
     *
     * Rewinds the iteration.
     *
     * @return void
     *
     */
    public function rewind() : void
    {
        reset($this->_fieldValue);
    }

    /**
     *
     * Sets an address.
     *
     * @param \Simple\Mail\Address|string $address an address
     * @param string $name a name (optional)
     * @param string $charset a charset (optional)
     * @param string $encoding an encoding (optional)
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function setAddress($address, string $name = '', string $charset = '', string $encoding = '') : void
    {
        if (!($address instanceof Address))
        {
            $address = new Address($address, $name, $charset, $encoding);
        }

        $this->_fieldValue[strtolower($address->getAddress())] = $address;
    }

    /**
     *
     * Sets a field value.
     *
     * @param \Simple\Mail\Address|array|string $fieldValue a field value
     * @throws \Simple\Mail\Exception if an invalid argument is supplied
     * @return void
     *
     */
    public function setFieldValue($fieldValue) : void
    {
        $this->_fieldValue = [];

        if ($fieldValue instanceof Address)
        {
            $this->_fieldValue[strtolower($fieldValue->getAddress())] = $fieldValue;
        }
        elseif (is_array($fieldValue))
        {
            foreach ($fieldValue as $index => $value)
            {
                if (is_numeric($index))
                {
                    if (is_string($value))
                    {
                        $value = new Address($value);
                    }

                    if ($value instanceof Address)
                    {
                        $this->_fieldValue[strtolower($value->getAddress())] = $value;
                    }
                }
                elseif (is_string($index) && is_string($value))
                {
                    $value = new Address($index, $value);
                    $this->_fieldValue[strtolower($value->getAddress())] = $value;
                }
            }
        }
        elseif ($fieldValue)
        {
            $fieldValue = explode(',', $fieldValue);

            foreach ($fieldValue as $index => $value)
            {
                $value = Address::parseString($value);
                $this->_fieldValue[strtolower($value->getAddress())] = $value;
            }
        }
    }

    /**
     *
     * Returns true if the current item is valid.
     *
     * @return boolean
     *
     */
    public function valid() : bool
    {
        $key = key($this->_fieldValue);
        return $key !== null && $key !== false;
    }
}
