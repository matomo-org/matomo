<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Processor;

use Piwik\Common;
use Piwik\FrontController;

/**
 * Adds a unique "request id" to the log message to follow log entries for each HTTP request.
 */
class RequestIdProcessor
{
    private $currentRequestKey;

    public function __invoke(array $record)
    {
        if (empty($this->currentRequestKey)) {
            if (Common::isPhpCliMode()) {
                $this->currentRequestKey = getmypid();
            } else {
                $this->currentRequestKey = FrontController::getUniqueRequestId();
            }
        }

        $record['extra']['request_id'] = $this->currentRequestKey;

        return $record;
    }
}
