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

/**
 *
 * mail header interface
 *
 * @package Simple\Mail\Header
 *
 */
interface HeaderInterface
{
    /**
     *
     * Returns a string representation of this instance.
     *
     * @return string
     *
     */
    public function __toString() : string;

    /**
     *
     * Returns a field name.
     *
     * @return string
     *
     */
    public function getFieldName() : string;

    /**
     *
     * Returns a field value.
     *
     * @param boolean $encode a MIME encode flag (optional)
     * @return string
     *
     */
    public function getFieldValue(bool $encode = false) : string;

    /**
     *
     * Sets a field value.
     *
     * @param mixed $fieldValue a field value
     * @return void
     *
     */
    public function setFieldValue($fieldValue) : void;
}
