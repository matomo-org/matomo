<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\Framework\Mock\Tracker;

class Response extends \Piwik\Plugins\BulkTracking\Tracker\Response
{
    protected function logExceptionToErrorLog($e)
    {
        // prevent from writing to console in tests
    }
}
