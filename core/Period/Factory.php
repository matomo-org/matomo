<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Period;

use Exception;
use Piwik\Config;
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
     * @param Date|string $timezone Optional timezone that will be used only when $period is 'range' or $date is 'last|previous'
     * @throws Exception If `$strPeriod` is invalid.
     * @return \Piwik\Period
     */
    public static function build($period, $date, $timezone = 'UTC')
    {
        self::checkPeriodIsEnabled($period);

        if (is_string($date)) {
            if (Period::isMultiplePeriod($date, $period)
                || $period == 'range') {
                return new Range($period, $date, $timezone);
            }
            $date = Date::factory($date);
        }

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
    }

    public static function checkPeriodIsEnabled($period)
    {
        if(!self::isPeriodEnabledForAPI($period)) {
            self::throwExceptionInvalidPeriod($period);
        }
    }

    /**
     * @param $strPeriod
     * @throws \Exception
     */
    private static function throwExceptionInvalidPeriod($strPeriod)
    {
        $periods = self::getPeriodsEnabledForAPI();
        $periods = implode(", ", $periods);
        $message = Piwik::translate('General_ExceptionInvalidPeriod', array($strPeriod, $periods));
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
            self::checkPeriodIsEnabled('range');
            $oPeriod = new Range('range', $date, $timezone, Date::factory('today', $timezone));
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

    /**
     * @param $period
     * @return bool
     */
    public static function isPeriodEnabledForAPI($period)
    {
        $enabledPeriodsInAPI = self::getPeriodsEnabledForAPI();
        return in_array($period, $enabledPeriodsInAPI);
    }

    /**
     * @return array
     */
    public static function getPeriodsEnabledForAPI()
    {
        $enabledPeriodsInAPI = Config::getInstance()->General['enabled_periods_API'];
        $enabledPeriodsInAPI = explode(",", $enabledPeriodsInAPI);
        $enabledPeriodsInAPI = array_map('trim', $enabledPeriodsInAPI);
        return $enabledPeriodsInAPI;
    }
}
