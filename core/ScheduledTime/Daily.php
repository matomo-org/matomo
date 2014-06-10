<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ScheduledTime;

use Exception;
use Piwik\ScheduledTime;

/**
 * Daily class is used to schedule tasks every day.
 *
 * @see ScheduledTask
 */
class Daily extends ScheduledTime
{
    /**
     * @see ScheduledTime::getRescheduledTime
     * @return int
     *
     */
    public function getRescheduledTime()
    {
        $currentTime = $this->getTime();

        // Add one day
        $rescheduledTime = mktime(date('H', $currentTime),
            date('i', $currentTime),
            date('s', $currentTime),
            date('n', $currentTime),
            date('j', $currentTime) + 1,
            date('Y', $currentTime)
        );

        // Adjusts the scheduled hour
        $rescheduledTime = $this->adjustHour($rescheduledTime);
        $rescheduledTime = $this->adjustTimezone($rescheduledTime);

        return $rescheduledTime;
    }

    /**
     * @see ScheduledTime::setDay
     * @param int $_day
     * @throws \Exception
     * @ignore
     */
    public function setDay($_day)
    {
        throw new Exception ("Method not supported");
    }
}
