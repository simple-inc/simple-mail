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

/**
 *
 * mail part interface
 *
 * @package Simple\Mail\Part
 *
 */
interface PartInterface
{
    /**
     *
     * Returns a string representation of this instance.
     *
     * @return string
     *
     */
    public function __toString();
}
