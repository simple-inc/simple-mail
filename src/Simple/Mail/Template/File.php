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

use Simple\Mail\Template;

/**
 *
 * file mail template class
 *
 * @package Simple\Mail\Template
 *
 */
class File extends Template
{
    /**
     *
     * HTML text part separator
     *
     * @var string
     *
     */
    protected $_htmlTextPartSeparator = '-------html-text-part-------';

    /**
     *
     * plain text part separator
     *
     * @var string
     *
     */
    protected $_plainTextPartSeparator = '-------plain-text-part-------';

    /**
     *
     * subject separator
     *
     * @var string
     *
     */
    protected $_subjectSeparator = '-------subject-------';

    /**
     *
     * Loads a template.
     *
     * @return void
     *
     */
    public function load() : void
    {
        // reset properties
        $this->_subject = '';
        $this->_htmlTextPart = '';
        $this->_plainTextPart = '';

        // subject separator
        if ($this->isOption('subjectSeparator'))
        {
            $this->_subjectSeparator = $this->getOption('subjectSeparator');
        }

        // HTML text part separator
        if ($this->isOption('htmlTextPartSeparator'))
        {
            $this->_htmlTextPartSeparator = $this->getOption('htmlTextPartSeparator');
        }

        // plain text part separator
        if ($this->isOption('plainTextPartSeparator'))
        {
            $this->_plainTextPartSeparator = $this->getOption('plainTextPartSeparator');
        }

        // filename
        $filename = $this->getOption('filename');

        // file exists
        if (file_exists($filename))
        {
            // get contents
            $contents = file_get_contents($filename);

            // subject found
            if (preg_match('/' . $this->_subjectSeparator . '(.*)' . $this->_subjectSeparator . '/s', $contents, $matches) === 1)
            {
                $this->_subject = trim($matches[1]);
            }

            // HTML text part found
            if (preg_match('/' . $this->_htmlTextPartSeparator . '(.*)' . $this->_htmlTextPartSeparator . '/s', $contents, $matches) === 1)
            {
                $this->_htmlTextPart = trim($matches[1]);
            }

            // plain text part found
            if (preg_match('/' . $this->_plainTextPartSeparator . '(.*)' . $this->_plainTextPartSeparator . '/s', $contents, $matches) === 1)
            {
                $this->_plainTextPart = trim($matches[1]);
            }
        }
    }
}
