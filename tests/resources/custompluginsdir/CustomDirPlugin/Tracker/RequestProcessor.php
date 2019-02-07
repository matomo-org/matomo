<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomDirPlugin\Tracker;

use Piwik\Tracker\Request;
use Piwik\Tracker;

class CustomDirPlugin extends Tracker\RequestProcessor
{
    public function onNewVisit(Tracker\Visit\VisitProperties $visitProperties, Request $request)
    {
        $visitProperties->setProperty('custom_int', 1);
    }

    public function onExistingVisit(&$valuesToUpdate, Tracker\Visit\VisitProperties $visitProperties, Request $request)
    {
        $visitProperties->setProperty('custom_int', 2);
    }
}
