<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugins\Events\Columns\EventName;

/**
 * Report metadata class for the Events.getNameFromActionId class.
 */
class GetNameFromActionId extends Report
{
    protected function init()
    {
        $this->category = 'Events_Events';
        $this->processedMetrics = false;
        $this->dimension     = new EventName();
        $this->name          = Piwik::translate('Events_Names');
        $this->isSubtableReport = true;
    }
}