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
use Piwik\Common;

/**
 *
 * @package Piwik
 * @subpackage Log
 */
class ScreenFormatter implements \Zend_Log_Formatter_Interface
{
    /**
     * Returns the formatted event array
     *
     * @param array $event
     * @return array
     */
    function formatEvent($event)
    {
        // no injection in error messages, backtrace when displayed on screen
        return array_map(array('Piwik\Common', 'sanitizeInputValue'), $event);
    }

    /**
     * Returns the formatted String
     *
     * @param string $string
     * @return string
     */
    function format($string)
    {
        return self::getFormattedString($string);
    }

    /**
     * Returns the formatted String
     *
     * @param string $string
     * @return string
     */
    static public function getFormattedString($string)
    {
        if (!Common::isPhpCliMode()) {
            @header('Content-Type: text/html; charset=utf-8');
        }
        return $string;
    }
}