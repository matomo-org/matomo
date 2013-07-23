<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Log;

/**
 * @package Piwik
 * @subpackage Log
 */
class Formatter_FileFormatter implements \Zend_Log_Formatter_Interface
{
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        foreach ($event as &$value) {
            $value = str_replace("\n", '\n', $value);
            $value = '"' . $value . '"';
        }
        $ts = $event['timestamp'];
        unset($event['timestamp']);
        return $ts . ' ' . implode(" ", $event) . "\n";
    }
}

/**
 *
 * @package Piwik
 * @subpackage Log
 */
class Formatter_ScreenFormatter implements \Zend_Log_Formatter_Interface
{
    function formatEvent($event)
    {
        // no injection in error messages, backtrace when displayed on screen
        return array_map(array('Piwik\Common', 'sanitizeInputValue'), $event);
    }

    function format($string)
    {
        return self::getFormattedString($string);
    }

    static public function getFormattedString($string)
    {
        if (!Common::isPhpCliMode()) {
            @header('Content-Type: text/html; charset=utf-8');
        }
        return $string;
    }
}