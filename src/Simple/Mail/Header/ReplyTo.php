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
 * Reply-To mail header class
 *
 * @package Simple\Mail\Header
 *
 */
class ReplyTo extends AbstractAddressHeader
{
    /**
     *
     * field name
     *
     * @var string
     *
     */
    protected $_fieldName = 'Reply-To';
}
