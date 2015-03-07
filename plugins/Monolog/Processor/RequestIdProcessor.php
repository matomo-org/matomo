<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Processor;

use Piwik\Common;

/**
 * Adds a unique "request id" to the log message to follow log entries for each HTTP request.
 */
class RequestIdProcessor
{
    private $currentRequestKey;

    public function __invoke(array $record)
    {
        if (Common::isPhpCliMode()) {
            return $record;
        }

        if (empty($this->currentRequestKey)) {
            $this->currentRequestKey = substr(Common::generateUniqId(), 0, 5);
        }

        $record['extra']['request_id'] = $this->currentRequestKey;

        return $record;
    }
}
