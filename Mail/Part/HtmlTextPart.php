<?php

declare(strict_types=1);

/**
 *
 * Simple Framework
 *
 * @copyright Simple Inc. All rights reserved.
 *
 */

namespace Simple\Mail\Part;

use Simple\Mail\Part\AbstractPart;

/**
 *
 * mail text/html part class
 *
 * @package Simple\Mail\Part
 *
 */
class HtmlTextPart extends AbstractTextPart
{
    /**
     *
     * content type
     *
     * @var string
     *
     */
    protected $_contentType = 'text/html';
}
