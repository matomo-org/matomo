<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Formatter;

use Piwik\Common;
use Piwik\Log;

/**
 * Adds a unique "request id" to the log message to follow log entries for each HTTP request.
 */
class AddRequestIdFormatter extends Formatter
{
    public function format($message, $level, $tag, $datetime, Log $logger)
    {
        static $currentRequestKey;

        if (empty($currentRequestKey)) {
            $currentRequestKey = substr(Common::generateUniqId(), 0, 5);
        }

        $message = $this->next($message, $level, $tag, $datetime, $logger);

        if (! is_string($message)) {
            return $message;
        }

        // Decorate the error message with the "request id"
        if (!defined('PIWIK_TEST_MODE')) {
            $message = '[' . $currentRequestKey . '] ' . $message;
        }

        return $message;
    }
}
