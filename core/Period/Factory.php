<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Period;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin;

/**
 * Creates Period instances using the values used for the 'period' and 'date'
 * query parameters.
 *
 * ## Custom Periods
 *
 * Plugins can define their own period factories all plugins to define new period types, in addition
 * to "day", "week", "month", "year" and "range".
 *
 * To define a new period type:
 *
 * 1. create a new period class that derives from {@see \Piwik\Period}.
 * 2. extend this class in a new PeriodFactory class and put it in /path/to/piwik/plugins/MyPlugin/PeriodFactory.php
 *
 * Period name collisions:
 *
 * If two plugins try to handle the same period label, the first one encountered will
 * be used. In other words, avoid using another plugin's period label.
 */
abstract class Factory
{
    public function __construct()
    {
        // empty
    }

    /**
     * Returns true if this factory should handle the period/date string combination.
     *
     * @return bool
     */
    public abstract function shouldHandle($strPeriod, $strDate);

    /**
     * Creates a period using the value of the 'date' query parameter.
     *
     * @param string $strPeriod
     * @param string|Date $date
     * @param string $timezone
     * @return Period
     */
    public abstract function make($strPeriod, $date, $timezone);

    /**
     * Creates a new Period instance with a period ID and {@link Date} instance.
     *
     * _Note: This method cannot create {@link Period\Range} periods._
     *
     * @param string $period `"day"`, `"week"`, `"month"`, `"year"`, `"range"`.
     * @param Date|string $date A date within the period or the range of dates.
     * @param Date|string $timezone Optional timezone that will be used only when $period is 'range' or $date is 'last|previous'
     * @throws Exception If `$strPeriod` is invalid or $date is invalid.
     * @return \Piwik\Period
     */
    public static function build($period, $date, $timezone = 'UTC')
    {
        self::checkPeriodIsEnabled($period);

        if (is_string($date)) {
            [$period, $date] = self::convertRangeToDateIfNeeded($period, $date);
            if (Period::isMultiplePeriod($date, $period)
                || $period == 'range'
            ) {

                return new Range($period, $date, $timezone);
            }

            $dateObject = Date::factory($date);
        } else if ($date instanceof Date) {
            $dateObject = $date;
        } else {
            throw new \Exception("Invalid date supplied to Period\Factory::build(): " . gettype($date));
        }

        switch ($period) {
            case 'day':
                return new Day($dateObject);
            case 'week':
                return new Week($dateObject);
            case 'month':
                return new Month($dateObject);
            case 'year':
                return new Year($dateObject);
        }

        /** @var string[] $customPeriodFactories */
        $customPeriodFactories = Plugin\Manager::getInstance()->findComponents('PeriodFactory', self::class);
        foreach ($customPeriodFactories as $customPeriodFactoryClass) {
            $customPeriodFactory = StaticContainer::get($customPeriodFactoryClass);
            if ($customPeriodFactory->shouldHandle($period, $date)) {
                return $customPeriodFactory->make($period, $date, $timezone);
            }
        }

        throw new \Exception("Don't know how to create a '$period' period! (date = $date)");
    }

    public static function checkPeriodIsEnabled($period)
    {
        if (!self::isPeriodEnabledForAPI($period)) {
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

    private static function convertRangeToDateIfNeeded($period, $date)
    {
        if (is_string($period) && is_string($date) && $period === 'range') {
            $dates = explode(',', $date);
            if (count($dates) === 2 && $dates[0] === $dates[1]) {
                $period = 'day';
                $date = $dates[0];
            }
        }

        return array($period, $date);
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

        [$period, $date] = self::convertRangeToDateIfNeeded($period, $date);

        if ($period == 'range') {
            self::checkPeriodIsEnabled('range');
            $oPeriod = new Range('range', $date, $timezone, Date::factory('today', $timezone));
        } else {
            if (!($date instanceof Date)) {
                if (preg_match('/^(now|today|yesterday|yesterdaySameTime|last[ -]?(?:week|month|year))$/i', $date)) {
                    $date = Date::factoryInTimezone($date, $timezone);
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
        $periodValidator = new PeriodValidator();
        return $periodValidator->isPeriodAllowedForAPI($period);
    }

    /**
     * @return array
     */
    public static function getPeriodsEnabledForAPI()
    {
        $periodValidator = new PeriodValidator();
        return $periodValidator->getPeriodsAllowedForAPI();
    }

    public static function isAnyLowerPeriodDisabledForAPI($periodLabel)
    {
        $parentPeriod = null;
        switch ($periodLabel) {
            case 'week':
                $parentPeriod = 'day';
                break;
            case 'month':
                $parentPeriod = 'week';
                break;
            case 'year':
                $parentPeriod = 'month';
                break;
            default:
                break;
        }

        if ($parentPeriod === null) {
            return false;
        }

        return !self::isPeriodEnabledForAPI($parentPeriod)
            || self::isAnyLowerPeriodDisabledForAPI($parentPeriod);
    }
}
