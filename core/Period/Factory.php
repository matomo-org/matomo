<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Period;

use Exception;
use Piwik\Date;
use Piwik\Period;
use Piwik\Piwik;

class Factory
{
    /**
     * Creates a new Period instance with a period ID and {@link Date} instance.
     *
     * _Note: This method cannot create {@link Period\Range} periods._
     *
     * @param string $period `"day"`, `"week"`, `"month"`, `"year"`, `"range"`.
     * @param Date|string $date A date within the period or the range of dates.
     * @throws Exception If `$strPeriod` is invalid.
     * @return \Piwik\Period
     */
    static public function build($period, $date)
    {
        if (is_string($date)) {
            if (Period::isMultiplePeriod($date, $period) || $period == 'range') {
                self::checkPeriodIsEnabled('range');
                return new Range($period, $date);
            }
            $date = Date::factory($date);
        }

        self::checkPeriodIsEnabled($period);
        switch ($period) {
            case 'day':
                return new Day($date);
                break;

            case 'week':
                return new Week($date);
                break;

            case 'month':
                return new Month($date);
                break;

            case 'year':
                return new Year($date);
                break;
        }

        self::throwExceptionInvalidPeriod($period);
    }

    private static function checkPeriodIsEnabled($period)
    {
        $enabledPeriodsInAPI = array();
        if(!in_array($period, $enabledPeriodsInAPI)) {
            self::throwExceptionInvalidPeriod($period);
        }
    }

    /**
     * @param $strPeriod
     * @throws \Exception
     */
    private static function throwExceptionInvalidPeriod($strPeriod)
    {
        $message = Piwik::translate('General_ExceptionInvalidPeriod', array($strPeriod, 'day, week, month, year, range'));
        throw new Exception($message);
    }


    /**
     * Creates a Period instance using a period, date and timezone.
     *
     * @param string $timezone The timezone of the date. Only used if `$date` is `'now'`, `'today'`,
     *                         `'yesterday'` or `'yesterdaySameTime'`.
     * @param string $period The period string: `"day"`, `"week"`, `"month"`, `"year"`, `"range"`.
     * @param string $date The date or date range string. Can be a special value including
     *                     `'now'`, `'today'`, `'yesterday'`, `'yesterdaySameTime'`.
     * @return \Piwik\Period
     */
    public static function makePeriodFromQueryParams($timezone, $period, $date)
    {
        if (empty($timezone)) {
            $timezone = 'UTC';
        }

        if ($period == 'range') {
            $oPeriod = new Period\Range('range', $date, $timezone, Date::factory('today', $timezone));
        } else {
            if (!($date instanceof Date)) {
                if ($date == 'now' || $date == 'today') {
                    $date = date('Y-m-d', Date::factory('now', $timezone)->getTimestamp());
                } elseif ($date == 'yesterday' || $date == 'yesterdaySameTime') {
                    $date = date('Y-m-d', Date::factory('now', $timezone)->subDay(1)->getTimestamp());
                }
                $date = Date::factory($date);
            }
            $oPeriod = Factory::build($period, $date);
        }
        return $oPeriod;
    }
}