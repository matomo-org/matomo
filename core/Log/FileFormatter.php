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
 * @package Piwik
 * @subpackage Log
 */
class FileFormatter implements \Zend_Log_Formatter_Interface
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
