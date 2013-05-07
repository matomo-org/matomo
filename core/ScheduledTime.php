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

/**
 * The Piwik_ScheduledTime abstract class is used as a base class for different types of scheduling intervals.
 * Piwik_ScheduledTime subclasses are used to schedule tasks within Piwik.
 *
 * @see Piwik_ScheduledTask
 * @package Piwik
 * @subpackage Piwik_ScheduledTime
 */
abstract class Piwik_ScheduledTime
{
    const PERIOD_NEVER = 'never';
    const PERIOD_DAY = 'day';
    const PERIOD_WEEK = 'week';
    const PERIOD_MONTH = 'month';
    const PERIOD_YEAR = 'year';

    /**
     * @link http://php.net/manual/en/function.date.php, format string : 'G'
     * Defaults to midnight
     * @var integer
     */
    public $hour = 0;

    /**
     * For weekly scheduling : http://php.net/manual/en/function.date.php, format string : 'N', defaults to Monday
     * For monthly scheduling : day of the month (1 to 31) (note: will be capped at the latest day available the
     * month), defaults to first day of the month
     * @var integer
     */
    public $day = 1;

    static public function getScheduledTimeForPeriod($period)
    {
        switch ($period) {
            case self::PERIOD_MONTH:
                return new Piwik_ScheduledTime_Monthly();
            case self::PERIOD_WEEK:
                return new Piwik_ScheduledTime_Weekly();
            case self::PERIOD_DAY:
                return new Piwik_ScheduledTime_Daily();

            default:
                throw new Exception('period ' . $period . 'is undefined.');
        }
    }

    /**
     * Returns the system time used by subclasses to compute schedulings.
     * This method has been introduced so unit tests can override the current system time.
     * @return int
     */
    protected function getTime()
    {
        return time();
    }

    /**
     * Computes the next scheduled time based on the system time at which the method has been called and
     * the underlying scheduling interval.
     *
     * @abstract
     * @return integer Returns the rescheduled time measured in the number of seconds since the Unix Epoch
     */
    abstract public function getRescheduledTime();

    /**
     * @abstract
     * @param  int $_day the day to set
     * @throws Exception if method not supported by subclass or parameter _day is invalid
     */
    abstract public function setDay($_day);

    /**
     * @param  int $_hour the hour to set, has to be >= 0 and < 24
     * @throws Exception if method not supported by subclass or parameter _hour is invalid
     */
    public function setHour($_hour)
    {
        if (!($_hour >= 0 && $_hour < 24)) {
            throw new Exception ("Invalid hour parameter, must be >=0 and < 24");
        }

        $this->hour = $_hour;
    }

    /**
     * Computes the delta in seconds needed to adjust the rescheduled time to the required hour.
     *
     * @param int $rescheduledTime The rescheduled time to be adjusted
     * @return int adjusted rescheduled time
     */
    protected function adjustHour($rescheduledTime)
    {
        if ($this->hour !== null) {
            // Reset the number of minutes and set the scheduled hour to the one specified with setHour()
            $rescheduledTime = mktime($this->hour,
                0,
                date('s', $rescheduledTime),
                date('n', $rescheduledTime),
                date('j', $rescheduledTime),
                date('Y', $rescheduledTime)
            );
        }
        return $rescheduledTime;
    }
}
