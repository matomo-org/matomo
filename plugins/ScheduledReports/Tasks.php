<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ScheduledReports;

use Piwik\Scheduler\Schedule\Schedule;

class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        foreach (API::getInstance()->getReports() as $report) {
            if (!$report['deleted'] && $report['period'] != Schedule::PERIOD_NEVER) {

                $schedule = Schedule::getScheduledTimeForPeriod($report['period']);
                $schedule->setHour($report['hour']);
                $schedule->setTimezone('UTC'); // saved hour is UTC always

                $this->custom(API::getInstance(), 'sendReport', $report['idreport'], $schedule);
            }
        }
    }
}