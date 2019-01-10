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
 * Errors-To mail header class
 *
 * @package Simple\Mail\Header
 *
 */
class ErrorsTo extends AbstractAddressHeader
{
    /**
     *
     * field name
     *
     * @var string
     *
     */
    protected $_fieldName = 'Errors-To';
}
