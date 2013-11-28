<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\ScheduledTime;

use Exception;
use Piwik\ScheduledTime;

/**
 * Weekly class is used to schedule tasks every week.
 *
 * @see ScheduledTask
 * @package Piwik
 * @subpackage ScheduledTime
 *
 */
class Weekly extends ScheduledTime
{
    /**
     * @see ScheduledTime::getRescheduledTime
     * @return int
     */
    public function getRescheduledTime()
    {
        $currentTime = $this->getTime();

        // Adds 7 days
        $rescheduledTime = mktime(date('H', $currentTime),
            date('i', $currentTime),
            date('s', $currentTime),
            date('n', $currentTime),
            date('j', $currentTime) + 7,
            date('Y', $currentTime)
        );

        // Adjusts the scheduled hour
        $rescheduledTime = $this->adjustHour($rescheduledTime);

        // Adjusts the scheduled day
        if ($this->day !== null) {
            // Removes or adds a number of days to set the scheduled day to the one specified with setDay()
            $rescheduledTime = mktime(date('H', $rescheduledTime),
                date('i', $rescheduledTime),
                date('s', $rescheduledTime),
                date('n', $rescheduledTime),
                date('j', $rescheduledTime) - (date('N', $rescheduledTime) - $this->day),
                date('Y', $rescheduledTime)
            );
        }

        return $rescheduledTime;
    }

    /**
     * @param int $day the day to set, has to be >= 1 and < 8
     * @throws Exception if parameter _day is invalid
     */
    public function setDay($day)
    {
        if (!is_int($day)) {
            $day = self::getDayIntFromString($day);
        }

        if (!($day >= 1 && $day < 8)) {
            throw new Exception ("Invalid day parameter, must be >=1 and < 8");
        }

        $this->day = $day;
    }

    public static function getDayIntFromString($dayString)
    {
        $time = strtotime($dayString);
        if ($time === false) {
            throw new Exception("Invalid day string '$dayString'. Must be 'monday', 'tuesday', etc.");
        }

        return date("N", $time);
    }
}