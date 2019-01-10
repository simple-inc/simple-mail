<?php

declare(strict_types=1);

/**
 *
 * Simple Framework
 *
 * @copyright Simple Inc. All rights reserved.
 *
 */

namespace Simple\Mail\Template;

/**
 *
 * mail template interface
 *
 * @package Simple\Mail\Template
 *
 */
interface TemplateInterface
{

    /**
     *
     * Constructs a new instance.
     *
     * @param array $options options
     *
     */
    public function __construct(array $options);

    /**
     *
     * Returns an HTML body part.
     *
     * @return string
     *
     */
    public function getHtmlTextPart() : string;

    /**
     *
     * Returns a plain text part.
     *
     * @return string
     *
     */
    public function getPlainTextPart() : string;

    /**
     *
     * Returns a subject.
     *
     * @return string
     *
     */
    public function getSubject() : string;

    /**
     *
     * Returns true if a body HTML exists.
     *
     * @return boolean
     *
     */
    public function isHtmlTextPart() : bool;

    /**
     *
     * Returns true if a plain text part exists.
     *
     * @return boolean
     *
     */
    public function isPlainTextPart() : bool;

    /**
     *
     * Returns true if a subject exists.
     *
     * @return boolean
     *
     */
    public function isSubject() : bool;

    /**
     *
     * Loads a template.
     *
     * @return void
     *
     */
    public function load() : void;

    /**
     *
     * Sets an HTML body part.
     *
     * @param string $bodyHtml an HTML body part
     * @return void
     *
     */
    public function setHtmlTextPart(string $bodyHtml) : void;

    /**
     *
     * Sets a plain text part.
     *
     * @param string $bodyText a plain text part
     * @return void
     *
     */
    public function setPlainTextPart(string $bodyText) : void;

    /**
     *
     * Sets a subject.
     *
     * @param string $subject a subject
     * @return void
     *
     */
    public function setSubject(string $subject) : void;
}
