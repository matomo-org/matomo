<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ScheduledReports;

use Piwik\ScheduledTime;
use Piwik\Site;

class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        foreach (API::getInstance()->getReports() as $report) {
            if (!$report['deleted'] && $report['period'] != ScheduledTime::PERIOD_NEVER) {

                $timezone = Site::getTimezoneFor($report['idsite']);

                $schedule = ScheduledTime::getScheduledTimeForPeriod($report['period']);
                $schedule->setHour($report['hour']);
                $schedule->setTimezone($timezone);

                $this->custom(API::getInstance(), 'sendReport', $report['idreport'], $schedule);
            }
        }
    }
}