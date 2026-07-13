<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Processor;

use Monolog\LogRecord;

/**
 * Processes a log message using `sprintf()`.
 */
class SprintfProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $message = $record['message'];
        $parameters = $record['context'];

        if (is_string($message) && !empty($parameters) && strpos($message, '%') !== false) {
            $parameters = $this->ensureParametersAreStrings($parameters);

            $record = $record->with(message: vsprintf($message, $parameters));
        }

        return $record;
    }

    private function ensureParametersAreStrings(array $parameters)
    {
        foreach ($parameters as &$param) {
            if (is_array($param)) {
                $param = json_encode($param);
            } elseif (is_object($param)) {
                $param = get_class($param);
            }
        }

        return $parameters;
    }
}
