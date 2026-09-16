<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Date;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\Scheduler\Schedule\Weekly;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var Model
     */
    private $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function schedule()
    {
        $reports = API::getInstance()->getReports();
        $idSites = array_values(array_unique(array_map('intval', array_column($reports, 'idsite'))));
        $siteTimezones = $this->model->getSiteTimezones($idSites);

        foreach ($reports as $report) {
            if (!$report['deleted'] && $report['period'] != Schedule::PERIOD_NEVER) {
                $schedule = Schedule::getScheduledTimeForPeriod($report['period']);
                $schedule->setHour($report['hour']);
                $schedule->setTimezone('UTC'); // saved hour is UTC always

                if ($schedule instanceof Weekly) {
                    $siteTimezone = $siteTimezones[(int) $report['idsite']] ?? 'UTC';
                    $this->alignWeeklyScheduleWithSiteTimezone($schedule, $siteTimezone);
                }

                $this->custom(API::getInstance(), 'sendReport', $report['idreport'], $schedule);
            }
        }
    }

    private function alignWeeklyScheduleWithSiteTimezone(Weekly $schedule, string $timezone): void
    {
        $nextMondayUtc = $schedule->getRescheduledTime();
        $timezoneOffset = Date::adjustForTimezone($nextMondayUtc, $timezone) - $nextMondayUtc;
        $localHour = (int) gmdate('G', $nextMondayUtc) + ($timezoneOffset / 3600);

        // Keep the report on local Monday when its saved UTC hour crosses a calendar-day boundary.
        if ($localHour >= 24) {
            $schedule->setDay(7);
        } elseif ($localHour < 0) {
            $schedule->setDay(2);
        }
    }
}
