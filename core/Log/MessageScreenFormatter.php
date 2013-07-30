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
use Piwik\Piwik;
use Piwik\Common;

/**
 * Format a standard message event to be displayed on the screen.
 * The message can be a PHP array or a string.
 *
 * @package Piwik
 * @subpackage Log
 */
class MessageScreenFormatter extends ScreenFormatter
{
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array $event    event data
     * @return string  formatted line to write to the log
     */
    public function format($event)
    {
        if (is_array($event['message'])) {
            $message = "<pre>" . var_export($event['message'], true) . "</pre>";
        } else {
            $message = $event['message'];
        }
        if (!Common::isPhpCliMode()) {
            $message .= "<br/>";
        }
        $message .= "\n";

        $memory = '';
        // Hacky: let's hide the memory usage in CLI to hide from the archive.php output
        if (!Common::isPhpCliMode()) {
            $memory = '[' . Piwik::getMemoryUsage() . '] ';
        }
        $message = '[' . $event['timestamp'] . '] [' . $event['requestKey'] . '] ' . $memory . $message;
        return parent::format($message);
    }
}
