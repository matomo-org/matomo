<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Http;

/**
 * Service that sanitizes query parameters for requests.
 *
 * Eventually once consistent out-only sanitization is implemented, this class should be
 * removed. In the mean time, since this class is accessed through DI, it can be used
 * to implement consistent sanitization, piece by piece.
 */
class RequestSanitizer
{
    // Flag used with htmlspecialchar. See php.net/htmlspecialchars.
    const HTML_ENCODING_QUOTE_STYLE = ENT_QUOTES;

    /**
     * Sanitizes a single value.
     *
     * @param mixed $value If numeric, the value is not changed. If a string, line breaks are removed and HTML entities
     *                     are escaped.
     * @param bool $alreadyStripslashed Implementation detail, ignore.
     * @return string
     * @throws \Exception If the value is null or a boolean.
     */
    public function sanitizeValue($value, $alreadyStripslashed = false)
    {
        if (is_numeric($value)) {
            return $value;
        } else if (is_string($value)) {
            $value = $this->sanitizeLineBreaks($value);
            $value = $this->sanitizeString($value);

            if (!$alreadyStripslashed) { // a JSON array was already stripslashed, don't do it again for each value
                $value = $this->undoMagicQuotes($value);
            }
        } else if (!is_null($value)
            && !is_bool($value)
        ) {
            throw new \Exception("The value to escape has not a supported type. Value = " . var_export($value, true));
        }
        return $value;
    }

    /**
     * Unsanitizes a single value. Replaces escaped HTML entities with the actual characters.
     *
     * @param string $value
     * @return string
     */
    public function unsanitizeValue($value)
    {
        return htmlspecialchars_decode($value, self::HTML_ENCODING_QUOTE_STYLE);
    }

    /**
     * Sanitize an array of values recursively.
     *
     * @param mixed $value If an array, it is sanitized recursively. Keys are sanitized as well as values. If not,
     *                     it is sanitized normally.
     * @param bool $alreadyStripslashed Implementation detail, ignore.
     * @return mixed
     */
    public function sanitizeValues($value, $alreadyStripslashed = false)
    {
        if (is_array($value)) {
            foreach (array_keys($value) as $key) {
                $newKey = $this->sanitizeValue($key, $alreadyStripslashed);

                if ($key != $newKey) {
                    $value[$newKey] = $value[$key];
                    unset($value[$key]);
                }

                $value[$newKey] = $this->sanitizeValues($value[$newKey], $alreadyStripslashed);
            }
            return $value;
        } else {
            return $this->sanitizeValue($value);
        }
    }

    /**
     * Unsanitize array values recursively.
     *
     * @param mixed $values If an array, it is unsanitized recursively. Keys are not unsanitized. If not an array,
     *                      it is sanitized normally.
     * @return mixed
     */
    public function unsanitizeValues($value)
    {
        if (is_array($value)) {
            $result = array();
            foreach ($value as $key => $arrayValue) {
                $result[$key] = $this->unsanitizeValues($arrayValue);
            }
            return $result;
        } else {
            return $this->unsanitizeValue($value);
        }
    }

    /**
     * @param string $value
     * @return string Line breaks and line carriage removed
     */
    public function sanitizeLineBreaks($value)
    {
        return str_replace(array("\n", "\r"), '', $value);
    }

    /**
     * Sanitizes a JSON string by value.
     *
     * @param string $value Parses a JSON string into an array and then sanitizes every element recursively. Array
     *                      keys are sanitized as well.
     * @return array
     */
    public function sanitizeJsonValues($value)
    {
        $value = $this->undoMagicQuotes($value);
        $value = json_decode($value, $assoc = true);
        return $this->sanitizeValues($value, $alreadyStripslashed = true);
    }

    /**
     * Sanitize a single input value
     *
     * @param $value
     * @return string
     */
    private function sanitizeString($value)
    {
        // $_GET and $_REQUEST already urldecode()'d
        // decode
        // note: before php 5.2.7, htmlspecialchars() double encodes &#x hex items
        $value = html_entity_decode($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');

        $value = $this->sanitizeNullBytes($value);

        // escape
        $tmp = @htmlspecialchars($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');

        // note: php 5.2.5 and above, htmlspecialchars is destructive if input is not UTF-8
        if ($value != '' && $tmp == '') {
            // convert and escape
            $value = utf8_encode($value);
            $tmp = htmlspecialchars($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');
            return $tmp;
        }
        return $tmp;
    }

    /**
     * @param string $value
     * @return string Null bytes removed
     */
    private function sanitizeNullBytes($value)
    {
        return str_replace(array("\0"), '', $value);
    }

    /**
     * Undo the damage caused by magic_quotes; deprecated in php 5.3 but not removed until php 5.4
     *
     * @param string
     * @return string  modified or not
     */
    private function undoMagicQuotes($value)
    {
        static $shouldUndo;

        if (!isset($shouldUndo)) {
            $shouldUndo = version_compare(PHP_VERSION, '5.4', '<') && get_magic_quotes_gpc();
        }

        if ($shouldUndo) {
            $value = stripslashes($value);
        }

        return $value;
    }
}