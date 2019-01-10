<?php

declare(strict_types=1);

/**
 *
 * Simple Framework
 *
 * @copyright Simple Inc. All rights reserved.
 *
 */

namespace Simple\Mail\Protocol;

use Simple\Mail\Protocol\ProtocolInterface;

/**
 *
 * abstract mail protocol class
 *
 * @package Simple\Mail\Protocol
 *
 */
abstract class AbstractProtocol implements ProtocolInterface
{
    /**
     *
     * options
     *
     * @var array
     *
     */
    protected $_options;

    /**
     *
     * Constructs a new AbstractProtocol instance.
     *
     * @param array $options options (optional)
     *
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     *
     * Returns an option.
     *
     * @param string $name an option name
     * @return mixed
     *
     */
    public function getOption(string $name)
    {
        return $this->_options[$name] ?? null;
    }

    /**
     *
     * Returns options.
     *
     * @return array
     *
     */
    public function getOptions() : array
    {
        return $this->_options;
    }

    /**
     *
     * Sets an option.
     *
     * @param string $name an option name
     * @param mixed $value an option value
     * @return void
     *
     */
    public function setOption(string $name, $value) : void
    {
        $this->_options[$name] = $value;
    }

    /**
     *
     * Sets options.
     *
     * @param array $options options
     * @return void
     *
     */
    public function setOptions(array $options) : void
    {
        $this->_options = $options;
    }
}
