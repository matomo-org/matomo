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
use Piwik\Log;
use Piwik\API\ResponseBuilder;

/**
 * Format an exception event to be displayed on the screen.
 *
 * @package Piwik
 * @subpackage Log
 */
class ExceptionScreenFormatter extends ScreenFormatter
{
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array $event    event data
     * @return string  formatted line to write to the log
     */
    public function format($event)
    {
        $event = parent::formatEvent($event);
        $errstr = $event['message'];

        $outputFormat = strtolower(Common::getRequestVar('format', 'html', 'string'));
        $response = new ResponseBuilder($outputFormat);
        $message = $response->getResponseException(new \Exception($errstr));
        return parent::format($message);
    }
}
