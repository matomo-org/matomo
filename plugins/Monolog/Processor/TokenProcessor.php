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
 * Removes any token_auth that might appear in the logs.
 *
 * Ideally the token_auth should never be logged, but...
 */
class TokenProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(message: preg_replace('/token_auth=[0-9a-h]+/', 'token_auth=removed', $record['message']));
    }
}
