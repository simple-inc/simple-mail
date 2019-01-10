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

use Simple\Mail\Template\TemplateInterface;

/**
 *
 * mail template class
 *
 * @package Simple\Mail
 *
 */
class Template implements TemplateInterface
{
    /**
     *
     * HTML text part
     *
     * @var string
     *
     */
    protected $_htmlTextPart = '';

    /**
     *
     * plain text part
     *
     * @var string
     *
     */
    protected $_plainTextPart = '';

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
     * subject
     *
     * @var string
     *
     */
    protected $_subject = '';

    /**
     *
     * Constructs a new Template instance.
     *
     * @param array $options options
     *
     */
    public function __construct(array $options)
    {
        $this->setOptions($options);
        $this->load();
    }

    /**
     *
     * Replaces a template property with the first argument.
     *
     * @param string $method a method (property name)
     * @param array $arguments arguments
     * @return void
     *
     */
    public function __call(string $method, array $arguments)
    {
        // target and replacement
        $target = '%' . $method . '%';
        $replacement = $arguments[0] ?? 'NULL';

        // decode html entities
        $replacement = html_entity_decode($replacement);

        // replace a target
        $this->_subject = str_replace($target, $replacement, $this->_subject);
        $this->_htmlTextPart = str_replace($target, $replacement, $this->_htmlTextPart);
        $this->_plainTextPart = str_replace($target, $replacement, $this->_plainTextPart);
    }

    /**
     *
     * Returns an HTML text part.
     *
     * @return string
     *
     */
    public function getHtmlTextPart() : string
    {
        return $this->_htmlTextPart;
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
     * Returns a plain text part.
     *
     * @return string
     *
     */
    public function getPlainTextPart() : string
    {
        return $this->_plainTextPart;
    }

    /**
     *
     * Returns a subject.
     *
     * @return string
     *
     */
    public function getSubject() : string
    {
        return $this->_subject;
    }

    /**
     *
     * Returns true if HTML text part exists.
     *
     * @return boolean
     *
     */
    public function isHtmlTextPart() : bool
    {
        return $this->_htmlTextPart !== '';
    }

    /**
     *
     * Returns true if an option exists.
     *
     * @param string $name an option name
     * @return boolean
     *
     */
    public function isOption($name) : bool
    {
        return isset($this->_options[$name]);
    }

    /**
     *
     * Returns true if plain text part exists.
     *
     * @return boolean
     *
     */
    public function isPlainTextPart() : bool
    {
        return $this->_plainTextPart !== '';
    }

    /**
     *
     * Returns true if subject exists.
     *
     * @return boolean
     *
     */
    public function isSubject() : bool
    {
        return $this->_subject !== '';
    }

    /**
     *
     * Loads a template.
     *
     * @return void
     *
     */
    public function load() : void
    {
        $this->_subject = (string)$this->getOption('subject');
        $this->_htmlTextPart = (string)$this->getOption('htmlTextPart');
        $this->_plainTextPart = (string)$this->getOption('plainTextPart');
    }

    /**
     *
     * Sets an HTML text part.
     *
     * @param string $htmlTextPart a HTML text part
     * @return void
     *
     */
    public function setHtmlTextPart(string $htmlTextPart) : void
    {
        $this->_htmlTextPart = $htmlTextPart;
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

    /**
     *
     * Sets a plain text part.
     *
     * @param string $plainTextPart a plain text part
     * @return void
     *
     */
    public function setPlainTextPart(string $plainTextPart) : void
    {
        $this->_plainTextPart = $plainTextPart;
    }

    /**
     *
     * Sets a subject
     *
     * @param string $subject a subject
     * @return void
     *
     */
    public function setSubject(string $subject) : void
    {
        $this->_subject = $subject;
    }
}
