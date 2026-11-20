<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports;

use Exception;
use Piwik\Date;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\Site;

/**
 * Schedules a weekly or monthly report on the first day of the period in the site's timezone
 * (Monday, or the first day of the month), at the report's saved UTC hour.
 *
 * The saved hour is UTC, so the local delivery time can fall on a different UTC day. Each candidate
 * UTC day is checked against the site's local calendar, which keeps DST and non-hour offsets correct.
 * When a DST change skips the first day, the report is sent on the first local day of the period it reaches.
 *
 * @internal
 */
class ReportSchedule extends Schedule
{
    /**
     * Upper bound of days to scan, covering the longest month plus a day of timezone offset.
     */
    private const MAX_DAYS_TO_SCAN = 33;

    /**
     * @var string
     */
    private $period;

    /**
     * @var int
     */
    private $idSite;

    public function __construct(string $period, int $idSite)
    {
        if (!self::isScheduledOnSiteCalendar($period)) {
            throw new Exception("Unsupported report schedule period '$period'.");
        }

        $this->period = $period;
        $this->idSite = $idSite;
    }

    /**
     * Whether reports of this period are scheduled on the site's local calendar. Other periods keep
     * their UTC schedule.
     */
    public static function isScheduledOnSiteCalendar(string $period): bool
    {
        return $period === Schedule::PERIOD_WEEK || $period === Schedule::PERIOD_MONTH;
    }

    /**
     * Returns the current day on the calendar the report is scheduled on, so the reported period
     * matches the day the report is sent.
     */
    public static function getToday(string $period, int $idSite): Date
    {
        if (self::isScheduledOnSiteCalendar($period)) {
            return Date::factoryInTimezone('today', Site::getTimezoneFor($idSite));
        }

        return Date::today();
    }

    public function getRescheduledTime()
    {
        $currentTime = $this->getTime();
        $timezone = Site::getTimezoneFor($this->idSite);
        $localPeriodFormat = $this->period === Schedule::PERIOD_WEEK ? 'o-W' : 'Y-m';

        // Pick the first run in a new local week or month, rather than the run on its first day, which a
        // daylight saving time change can skip.
        $candidate = mktime((int) $this->hour, 0, 0, (int) date('n', $currentTime), (int) date('j', $currentTime), (int) date('Y', $currentTime));
        $previousLocalPeriod = date($localPeriodFormat, Date::adjustForTimezone($candidate - 86400, $timezone));
        for ($i = 0; $i <= self::MAX_DAYS_TO_SCAN; $i++, $candidate += 86400) {
            $localPeriod = date($localPeriodFormat, Date::adjustForTimezone($candidate, $timezone));
            if ($candidate > $currentTime && $localPeriod !== $previousLocalPeriod) {
                return $candidate;
            }
            $previousLocalPeriod = $localPeriod;
        }

        throw new Exception("Could not compute the next scheduled time for a report of site {$this->idSite}.");
    }

    public function setDay($_day)
    {
        throw new Exception('Setting the day of a report schedule is not supported.');
    }
}
