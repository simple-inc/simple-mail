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

/**
 *
 * MIME class
 *
 * @package Simple\Mail
 *
 */
class Mime
{
    /**
     *
     * Returns a decoded value.
     *
     * @param string $value a value
     * @return string
     *
     */
    public static function decode(string $value) : string
    {
        if ($value)
        {
            // mime encoded value
            if (preg_match('/=\?[a-z0-9_\-]+\?[QB]{1}\?/i', $value) === 1)
            {
                $value = mb_decode_mimeheader($value);
            }
            // RFC 2231
            elseif (preg_match('/^([a-z0-9_\-]+)\'[a-z]*\'/i', $value, $charset) === 1)
            {
                $value = preg_replace('/^([a-z0-9_\-]+)\'[a-z]*\'/i', '', $value);
                $value = urldecode($value);

                $charset = strtoupper($charset[1]) ?? null;

                if ($charset != mb_internal_encoding())
                {
                    $value = mb_convert_encoding($value, mb_internal_encoding(), $charset);
                }
            }
        }

        return $value;
    }

    /**
     *
     * Returns an encoded value.
     *
     * @param string $value a value
     * @param string $charset a charset (optional)
     * @param string $encoding an encoding (optional)
     * @return string
     *
     */
    public static function encode(string $value, string $charset = '', string $encoding = '') : string
    {
        // charset exists
        if ($charset)
        {
            // quoted-printable
            if ($encoding === 'quoted-printable')
            {
                return mb_encode_mimeheader($value, $charset, 'Q');
            }

            // base64
            return mb_encode_mimeheader($value, $charset);
        }

        // quoted-printable
        if ($encoding === 'quoted-printable')
        {
            return mb_encode_mimeheader($value, mb_internal_encoding(), 'Q');
        }

        // base64
        return mb_encode_mimeheader($value);
    }

    /**
     *
     * Returns a boundary.
     *
     * @param string $prefix a prefix (optional)
     * @return string
     *
     */
    public static function getBoundary(string $prefix = 'Part_') : string
    {
        return trim($prefix . md5(uniqid('', true)));
    }
}
