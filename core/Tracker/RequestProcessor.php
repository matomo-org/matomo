<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker;

use Piwik\Tracker\Visit\VisitProperties;

/**
 * TODO
 */
abstract class RequestProcessor
{
    /**
     * TODO
     */
    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        return false;
    }

    /**
     * TODO
     */
    public function manipulateVisitProperties(VisitProperties $visitProperties)
    {
        return false;
    }

    /**
     * TODO
     */
    public function processRequest(VisitProperties $visitProperties)
    {
        // empty
    }
}