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
 * To mail header class
 *
 * @package Simple\Mail\Header
 *
 */
class To extends AbstractAddressHeader
{
    /**
     *
     * file name
     *
     * @var string
     *
     */
    protected $_fieldName = 'To';
}
