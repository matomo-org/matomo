<?php
/**
* Piwik - free/libre analytics platform
*
* @link http://piwik.org
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

namespace Piwik\Plugins\BulkTracking\tests\Framework\Mock\Tracker;

use Piwik\Tracker;
use Exception;

class Response extends \Piwik\Plugins\BulkTracking\Tracker\Response
{
    protected function logExceptionToErrorLog(Exception $e)
    {
        // prevent from writing to console in tests
    }

}
