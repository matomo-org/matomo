<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\DateTimeFormatProvider;

/**
 * Utility class that wraps date/time related PHP functions. Using this class can
 * be easier than using `date`, `time`, `date_default_timezone_set`, etc.
 *
 * ### Performance concerns
 *
 * The helper methods in this class are instance methods and thus `Date` instances
 * need to be constructed before they can be used. The memory allocation can result
 * in noticeable performance degradation if you construct thousands of Date instances,
 * say, in a loop.
 *
 * ### Examples
 *
 * **Basic usage**
 *
 *     $date = Date::factory('2007-07-24 14:04:24', 'EST');
 *     $date->addHour(5);
 *     echo $date->getLocalized("EEE, d. MMM y 'at' HH:mm:ss");
 *
 * @api
 */
class Date
{
    /** Number of seconds in a day. */
    const NUM_SECONDS_IN_DAY = 86400;

    /** The default date time string format. */
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /** Timestamp when first website came online - Tue, 06 Aug 1991 00:00:00 GMT. */
    const FIRST_WEBSITE_TIMESTAMP = 681436800;

    const DATETIME_FORMAT_LONG    = DateTimeFormatProvider::DATE_FORMAT_LONG;
    const DATETIME_FORMAT_SHORT   = DateTimeFormatProvider::DATETIME_FORMAT_SHORT;
    const DATE_FORMAT_LONG        = DateTimeFormatProvider::DATE_FORMAT_LONG;
    const DATE_FORMAT_DAY_MONTH   = DateTimeFormatProvider::DATE_FORMAT_DAY_MONTH;
    const DATE_FORMAT_SHORT       = DateTimeFormatProvider::DATE_FORMAT_SHORT;
    const DATE_FORMAT_MONTH_SHORT = DateTimeFormatProvider::DATE_FORMAT_MONTH_SHORT;
    const DATE_FORMAT_MONTH_LONG  = DateTimeFormatProvider::DATE_FORMAT_MONTH_LONG;
    const DATE_FORMAT_YEAR        = DateTimeFormatProvider::DATE_FORMAT_YEAR;
    const TIME_FORMAT             = DateTimeFormatProvider::TIME_FORMAT;

    // for tests
    public static $now = null;

    /**
     * Max days for months (non-leap-year). See {@link addPeriod()} implementation.
     *
     * @var int[]
     */
    private static $maxDaysInMonth = array(
        '1' => 31,
        '2' => 28,
        '3' => 31,
        '4' => 30,
        '5' => 31,
        '6' => 30,
        '7' => 31,
        '8' => 31,
        '9' => 30,
        '10' => 31,
        '11' => 30,
        '12' => 31
    );

    /**
     * The stored timestamp is always UTC based.
     * The returned timestamp via getTimestamp() will have the conversion applied
     * @var int|null
     */
    protected $timestamp = null;

    /**
     * Timezone the current date object is set to.
     * Timezone will only affect the returned timestamp via getTimestamp()
     * @var string
     */
    protected $timezone = 'UTC';

    /**
     * Constructor.
     *
     * @param int $timestamp The number in seconds since the unix epoch.
     * @param string $timezone The timezone of the datetime.
     * @throws Exception If $timestamp is not an int.
     */
    protected function __construct($timestamp, $timezone = 'UTC')
    {
        if (!is_int($timestamp)) {
            throw new Exception("Date is expecting a unix timestamp, got: '$timestamp'.");
        }
        $this->timezone = $timezone;
        $this->timestamp = $timestamp;
    }

    /**
     * Creates a new Date instance using a string datetime value. The timezone of the Date
     * result will be in UTC.
     *
     * @param string|int $dateString `'today'`, `'yesterday'`, `'now'`, `'yesterdaySameTime'`, a string with
     *                               `'YYYY-MM-DD HH:MM:SS'` format or a unix timestamp.
     * @param string $timezone The timezone of the result. If specified, `$dateString` will be converted
     *                         from UTC to this timezone before being used in the Date return value.
     * @throws Exception If `$dateString` is in an invalid format or if the time is before
     *                   Tue, 06 Aug 1991.
     * @return Date
     */
    public static function factory($dateString, $timezone = null)
    {
        if ($dateString instanceof self) {
            return new Date($dateString->timestamp, $dateString->timezone);
        }
        if ($dateString === 'now') {
            $date = self::now();
        } elseif ($dateString === 'today') {
            $date = self::today();
        } else if ($dateString === 'tomorrow') {
            $date = self::tomorrow();
        } elseif ($dateString === 'yesterday') {
            $date = self::yesterday();
        } elseif ($dateString === 'yesterdaySameTime') {
            $date = self::yesterdaySameTime();
        } else if (is_string($dateString) && preg_match('/last[ -]?week/i', urldecode($dateString))) {
            $date = self::lastWeek();
        } else if (is_string($dateString) && preg_match('/last[ -]?month/i', urldecode($dateString))) {
            $date = self::lastMonth();
        } else if (is_string($dateString) && preg_match('/last[ -]?year/i', urldecode($dateString))) {
            $date = self::lastYear();
        } elseif (!is_int($dateString)
            && (
                !is_string($dateString)
                // strtotime returns the timestamp for April 1st for a date like 2011-04-01,today
                // but we don't want this, as this is a date range and supposed to throw the exception
                || strpos($dateString, ',') !== false
                || ($dateString = strtotime($dateString)) === false
            )
        ) {
            throw self::getInvalidDateFormatException($dateString);
        } else {
            $date = new Date($dateString);
        }
        $timestamp = $date->getTimestamp();

        if ($timestamp < self::FIRST_WEBSITE_TIMESTAMP) {
            $dateOfFirstWebsite = new self(self::FIRST_WEBSITE_TIMESTAMP);
            $message = Piwik::translate('General_ExceptionInvalidDateBeforeFirstWebsite', array(
                $date->toString(),
                $dateOfFirstWebsite->getLocalized(self::DATE_FORMAT_SHORT),
                $dateOfFirstWebsite->getTimestamp()
            ));
            throw new Exception($message . ": $dateString");
        }

        if (empty($timezone)) {
            return $date;
        }

        $timestamp = self::adjustForTimezone($timestamp, $timezone);
        return Date::factory($timestamp);
    }

    /**
     * Returns Date w/ UTC timestamp of time $dateString/$timezone.
     * (Only applies to special strings, like 'now','today','yesterday','yesterdaySameTime'.
     *
     * @param $dateString
     * @param $timezone
     * @return Date
     * @ignore
     */
    public static function factoryInTimezone($dateString, $timezone)
    {
        if ($dateString === 'now') {
            return self::nowInTimezone($timezone);
        } else if ($dateString === 'today') {
            return self::todayInTimezone($timezone);
        } else if ($dateString === 'yesterday') {
            return self::yesterdayInTimezone($timezone);
        } else if ($dateString === 'yesterdaySameTime') {
            return self::yesterdaySameTimeInTimezone($timezone);
        } else if (preg_match('/last[ -]?week/i', urldecode($dateString))) {
            return self::lastWeekInTimezone($timezone);
        } else if (preg_match('/last[ -]?month/i', urldecode($dateString))) {
            return self::lastMonthInTimezone($timezone);
        } else if (preg_match('/last[ -]?year/i', urldecode($dateString))) {
            return self::lastYearInTimezone($timezone);
        } else {
            throw new \Exception("Date::factoryInTimezone() should not be used with $dateString.");
        }
    }

    private static function nowInTimezone($timezone)
    {
        $now = self::getNowTimestamp();
        $now = self::adjustForTimezone($now, $timezone);
        return new Date($now);
    }

    private static function todayInTimezone($timezone)
    {
        return self::nowInTimezone($timezone)->getStartOfDay();
    }

    private static function yesterdayInTimezone($timezone)
    {
        return self::todayInTimezone($timezone)->subDay(1);
    }

    private static function yesterdaySameTimeInTimezone($timezone)
    {
        return self::nowInTimezone($timezone)->subDay(1);
    }

    private static function lastWeekInTimezone($timezone)
    {
        return new Date(strtotime('-1week', self::todayInTimezone($timezone)->getTimestamp()));
    }

    private static function lastMonthInTimezone($timezone)
    {
        return new Date(strtotime('-1month', self::todayInTimezone($timezone)->getTimestamp()));
    }

    private static function lastYearInTimezone($timezone)
    {
        return new Date(strtotime('-1year', self::todayInTimezone($timezone)->getTimestamp()));
    }

    /**
     * Returns the current timestamp as a string with the following format: `'YYYY-MM-DD HH:MM:SS'`.
     *
     * @return string
     */
    public function getDatetime()
    {
        return $this->toString(self::DATE_TIME_FORMAT);
    }

    /**
     * Returns the current hour in UTC timezone.
     * @return string
     * @throws Exception
     */
    public function getHourUTC()
    {
        $dateTime = $this->getDatetime();
        $hourInTz = Date::factory($dateTime, 'UTC')->toString('G');

        return $hourInTz;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getDateStartUTC()
    {
        return $this->getStartOfDay()->toString(self::DATE_TIME_FORMAT);
    }

    /**
     * Returns the start of the day of the current timestamp in UTC. For example,
     * if the current timestamp is `'2007-07-24 14:04:24'` in UTC, the result will
     * be `'2007-07-24'` as a Date.
     *
     * @return Date
     */
    public function getStartOfDay()
    {
        $dateStartUTC = gmdate('Y-m-d', $this->timestamp);
        return Date::factory($dateStartUTC)->setTimezone($this->timezone);
    }

    /**
     * @return string
     * @deprecated
     */
    public function getDateEndUTC()
    {
        return $this->getEndOfDay()->toString(self::DATE_TIME_FORMAT);
    }

    /**
     * Returns the end of the day of the current timestamp in UTC. For example,
     * if the current timestamp is `'2007-07-24 14:03:24'` in UTC, the result will
     * be `'2007-07-24 23:59:59'`.
     *
     * @return Date
     */
    public function getEndOfDay()
    {
        $dateEndUTC = gmdate('Y-m-d 23:59:59', $this->timestamp);
        return Date::factory($dateEndUTC)->setTimezone($this->timezone);
    }

    /**
     * Returns a new date object with the same timestamp as `$this` but with a new
     * timezone.
     *
     * See {@link getTimestamp()} to see how the timezone is used.
     *
     * @param string $timezone eg, `'UTC'`, `'Europe/London'`, etc.
     * @return Date
     */
    public function setTimezone($timezone)
    {
        return new Date($this->timestamp, $timezone);
    }

    /**
     * Returns the offset to UTC time for the given timezone
     *
     * @param string $timezone
     * @return int offset in seconds
     */
    public static function getUtcOffset($timezone)
    {
        $timestampUTC       = self::today()->getTimestampUTC();
        $timestampZone      = self::adjustForTimezone($timestampUTC, $timezone);
        return ($timestampZone - $timestampUTC);
    }

    /**
     * Helper function that returns the offset in the timezone string 'UTC+14'
     * Returns false if the timezone is not UTC+X or UTC-X
     *
     * @param string $timezone
     * @return int|bool  utc offset or false
     */
    protected static function extractUtcOffset($timezone)
    {
        if ($timezone === 'UTC') {
            return 0;
        }
        $start = substr($timezone, 0, 4);
        if ($start !== 'UTC-'
            && $start !== 'UTC+'
        ) {
            return false;
        }
        $offset = (float)substr($timezone, 4);
        if ($start === 'UTC-') {
            $offset = -$offset;
        }
        return $offset;
    }

    /**
     * Converts a timestamp from UTC to a timezone.
     *
     * @param int $timestamp The UNIX timestamp to adjust.
     * @param string $timezone The timezone to adjust to.
     * @return int The adjusted time as seconds from EPOCH.
     */
    public static function adjustForTimezone($timestamp, $timezone)
    {
        if (empty($timezone)) {
            return $timestamp;
        }

        // manually adjust for UTC timezones
        $utcOffset = self::extractUtcOffset($timezone);
        if ($utcOffset !== false) {
            return self::addHourTo($timestamp, $utcOffset);
        }

        date_default_timezone_set($timezone);
        $datetime = date(self::DATE_TIME_FORMAT, $timestamp);
        date_default_timezone_set('UTC');

        return strtotime($datetime);
    }

    /**
     * Returns the date in the "Y-m-d H:i:s" PHP format
     *
     * @param int $timestamp
     * @return string
     */
    public static function getDatetimeFromTimestamp($timestamp)
    {
        return date("Y-m-d H:i:s", $timestamp);
    }

    /**
     * Returns the Unix timestamp of the date in UTC.
     *
     * @return int
     */
    public function getTimestampUTC()
    {
        return $this->timestamp;
    }

    /**
     * Returns the unix timestamp of the date in UTC, converted from the current
     * timestamp timezone.
     *
     * @return int
     */
    public function getTimestamp()
    {
        if (empty($this->timezone)) {
            $this->timezone = 'UTC';
        }
        $utcOffset = self::extractUtcOffset($this->timezone);
        if ($utcOffset !== false) {
            return (int)($this->timestamp - $utcOffset * 3600);
        }
        // The following code seems clunky - I thought the DateTime php class would allow to return timestamps
        // after applying the timezone offset. Instead, the underlying timestamp is not changed.
        // I decided to get the date without the timezone information, and create the timestamp from the truncated string.
        // Unit tests pass (@see Date.test.php) but I'm pretty sure this is not the right way to do it
        date_default_timezone_set($this->timezone);
        $dtzone = timezone_open('UTC');
        $time   = date('r', $this->timestamp);
        $dtime  = date_create($time);

        date_timezone_set($dtime, $dtzone);
        $dateWithTimezone    = date_format($dtime, 'r');
        $dateWithoutTimezone = substr($dateWithTimezone, 0, -6);
        $timestamp           = strtotime($dateWithoutTimezone);
        date_default_timezone_set('UTC');

        return (int) $timestamp;
    }

    /**
     * Returns `true` if the current date is older than the given `$date`.
     *
     * @param Date $date
     * @return bool
     */
    public function isLater(Date $date)
    {
        return $this->getTimestamp() > $date->getTimestamp();
    }

    /**
     * Returns `true` if the current date is earlier than the given `$date`.
     *
     * @param Date $date
     * @return bool
     */
    public function isEarlier(Date $date)
    {
        return $this->getTimestamp() < $date->getTimestamp();
    }

    /**
     * Returns `true` if the current year is a leap year, false otherwise.
     *
     * @return bool
     */
    public function isLeapYear()
    {
        $isLeap = (bool)(date('L', $this->getTimestamp()));

        return $isLeap;
    }

    /**
     * Converts this date to the requested string format. See {@link http://php.net/date}
     * for the list of format strings.
     *
     * @param string $format
     * @return string
     */
    public function toString($format = 'Y-m-d')
    {
        return date($format, $this->getTimestamp());
    }

    /**
     * See {@link toString()}.
     *
     * @return string The current date in `'YYYY-MM-DD'` format.
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Performs three-way comparison of the week of the current date against the given `$date`'s week.
     *
     * @param \Piwik\Date $date
     * @return int Returns `0` if the current week is equal to `$date`'s, `-1` if the current week is
     *             earlier or `1` if the current week is later.
     */
    public function compareWeek(Date $date)
    {
        $currentWeek = date('W', $this->getTimestamp());
        $toCompareWeek = date('W', $date->getTimestamp());
        if ($currentWeek == $toCompareWeek) {
            return 0;
        }
        if ($currentWeek < $toCompareWeek) {
            return -1;
        }
        return 1;
    }

    /**
     * Performs three-way comparison of the month of the current date against the given `$date`'s month.
     *
     * @param \Piwik\Date $date Month to compare
     * @return int Returns `0` if the current month is equal to `$date`'s, `-1` if the current month is
     *             earlier or `1` if the current month is later.
     */
    public function compareMonth(Date $date)
    {
        $currentMonth = date('n', $this->getTimestamp());
        $toCompareMonth = date('n', $date->getTimestamp());
        if ($currentMonth == $toCompareMonth) {
            return 0;
        }
        if ($currentMonth < $toCompareMonth) {
            return -1;
        }
        return 1;
    }

    /**
     * Performs three-way comparison of the month of the current date against the given `$date`'s year.
     *
     * @param \Piwik\Date $date Year to compare
     * @return int Returns `0` if the current year is equal to `$date`'s, `-1` if the current year is
     *             earlier or `1` if the current year is later.
     */
    public function compareYear(Date $date)
    {
        $currentYear   = date('Y', $this->getTimestamp());
        $toCompareYear = date('Y', $date->getTimestamp());
        if ($currentYear == $toCompareYear) {
            return 0;
        }
        if ($currentYear < $toCompareYear) {
            return -1;
        }
        return 1;
    }

    /**
     * Returns `true` if current date is today.
     *
     * @return bool
     */
    public function isToday()
    {
        return $this->toString('Y-m-d') === Date::factory('today', $this->timezone)->toString('Y-m-d');
    }

    /**
     * Returns a date object set to now in UTC (same as {@link today()}, except that the time is also set).
     *
     * @return \Piwik\Date
     */
    public static function now()
    {
        return new Date(self::getNowTimestamp());
    }

    /**
     * Returns a date object set to today at midnight in UTC.
     *
     * @return \Piwik\Date
     */
    public static function today()
    {
        return new Date(strtotime(date("Y-m-d 00:00:00", self::getNowTimestamp())));
    }

    /**
     * Returns a date object set to tomorrow at midnight in UTC.
     *
     * @return \Piwik\Date
     */
    public static function tomorrow()
    {
        return new Date(strtotime('tomorrow', self::getNowTimestamp()));
    }

    /**
     * Returns a date object set to yesterday at midnight in UTC.
     *
     * @return \Piwik\Date
     */
    public static function yesterday()
    {
        return new Date(strtotime("yesterday", self::getNowTimestamp()));
    }

    /**
     * Returns a date object set to yesterday with the current time of day in UTC.
     *
     * @return \Piwik\Date
     */
    public static function yesterdaySameTime()
    {
        return new Date(strtotime("yesterday " . date('H:i:s', self::getNowTimestamp()), self::getNowTimestamp()));
    }

    /**
     * Returns a date object set to the day a week ago at midnight in UTC.
     *
     * @return \Piwik\Date
     */
    public static function lastWeek()
    {
        return new Date(strtotime("-1week 00:00:00", self::getNowTimestamp()));
    }

    /**
     * Returns a date object set to the day a month ago at midnight in UTC.
     *
     * @return \Piwik\Date
     */
    public static function lastMonth()
    {
        return new Date(strtotime("-1month 00:00:00", self::getNowTimestamp()));
    }

    /**
     * Returns a date object set to the day a year ago at midnight in UTC.
     *
     * @return \Piwik\Date
     */
    public static function lastYear()
    {
        return new Date(strtotime("-1year 00:00:00", self::getNowTimestamp()));
    }

    /**
     * Returns a new Date instance with `$this` date's day and the specified new
     * time of day.
     *
     * @param string $time String in the `'HH:MM:SS'` format.
     * @return \Piwik\Date The new date with the time of day changed.
     */
    public function setTime($time)
    {
        return new Date(strtotime(date("Y-m-d", $this->timestamp) . " $time"), $this->timezone);
    }

    /**
     * Returns a new Date instance with `$this` date's time of day and the day specified
     * by `$day`.
     *
     * @param int $day The day eg. `31`.
     * @return \Piwik\Date
     */
    public function setDay($day)
    {
        $ts = $this->timestamp;
        $result = mktime(
            date('H', $ts),
            date('i', $ts),
            date('s', $ts),
            date('n', $ts),
            $day,
            date('Y', $ts)
        );
        return new Date($result, $this->timezone);
    }

    /**
     * Returns a new Date instance with `$this` date's time of day, month and day, but with
     * a new year (specified by `$year`).
     *
     * @param int $year The year, eg. `2010`.
     * @return \Piwik\Date
     */
    public function setYear($year)
    {
        $ts = $this->timestamp;
        $result = mktime(
            date('H', $ts),
            date('i', $ts),
            date('s', $ts),
            date('n', $ts),
            date('j', $ts),
            $year
        );
        return new Date($result, $this->timezone);
    }

    /**
     * Subtracts `$n` number of days from `$this` date and returns a new Date object.
     *
     * @param int $n An integer > 0.
     * @return \Piwik\Date
     */
    public function subDay($n)
    {
        if ($n === 0) {
            return clone $this;
        }
        $ts = strtotime("-$n day", $this->timestamp);
        return new Date($ts, $this->timezone);
    }

    /**
     * Subtracts `$n` weeks from `$this` date and returns a new Date object.
     *
     * @param int $n An integer > 0.
     * @return \Piwik\Date
     */
    public function subWeek($n)
    {
        return $this->subDay(7 * $n);
    }

    /**
     * Subtracts `$n` months from `$this` date and returns the result as a new Date object.
     *
     * @param int $n An integer > 0.
     * @return \Piwik\Date  new date
     */
    public function subMonth($n)
    {
        if ($n === 0) {
            return clone $this;
        }
        $ts = $this->timestamp;
        $result = mktime(
            date('H', $ts),
            date('i', $ts),
            date('s', $ts),
            date('n', $ts) - $n,
            1, // we set the day to 1
            date('Y', $ts)
        );
        return new Date($result, $this->timezone);
    }

    /**
     * Subtracts `$n` years from `$this` date and returns the result as a new Date object.
     *
     * @param int $n An integer > 0.
     * @return \Piwik\Date
     */
    public function subYear($n)
    {
        if ($n === 0) {
            return clone $this;
        }
        $ts = $this->timestamp;
        $result = mktime(
            date('H', $ts),
            date('i', $ts),
            date('s', $ts),
            1, // we set the month to 1
            1, // we set the day to 1
            date('Y', $ts) - $n
        );
        return new Date($result, $this->timezone);
    }

    /**
     * Returns a localized date string using the given template.
     * The template should contain tags that will be replaced with localized date strings.
     *
     * @param string $template eg. `"MMM y"`
     * @param bool   $ucfirst  whether the first letter should be upper-cased
     * @return string eg. `"Aug 2009"`
     */
    public function getLocalized($template, $ucfirst = true)
    {
        $dateTimeFormatProvider = StaticContainer::get('Piwik\Intl\Data\Provider\DateTimeFormatProvider');

        $template = $dateTimeFormatProvider->getFormatPattern($template);

        $tokens = self::parseFormat($template);

        $out = '';

        foreach ($tokens AS $token) {
            if (is_array($token)) {
                $out .= $this->formatToken(array_shift($token));

            } else {
                $out .= $token;
            }
        }

        if ($ucfirst) {
          $out = mb_strtoupper(mb_substr($out, 0, 1)) . mb_substr($out, 1);
        }

        return $out;
    }

    protected function formatToken($token)
    {
        $dayOfWeek = $this->toString('N');
        $monthOfYear = $this->toString('n');
        $translator = StaticContainer::get('Piwik\Translation\Translator');

        switch ($token) {
            // year
            case "yyyy":
            case "y":
                return $this->toString('Y');
            case "yy":
                return $this->toString('y');
            // month
            case "MMMM":
                return $translator->translate('Intl_Month_Long_' . $monthOfYear);
            case "MMM":
                return $translator->translate('Intl_Month_Short_' . $monthOfYear);
            case "MM":
                return $this->toString('n');
            case "M":
                return $this->toString('m');
            case "LLLL":
                return $translator->translate('Intl_Month_Long_StandAlone_' . $monthOfYear);
            case "LLL":
                return $translator->translate('Intl_Month_Short_StandAlone_' . $monthOfYear);
            case "LL":
                return $this->toString('n');
            case "L":
                return $this->toString('m');
            // day
            case "dd":
                return $this->toString('d');
            case "d":
                return $this->toString('j');
            case "EEEE":
                return $translator->translate('Intl_Day_Long_' . $dayOfWeek);
            case "EEE":
            case "EE":
            case "E":
                return $translator->translate('Intl_Day_Short_' . $dayOfWeek);
            case "cccc":
                return $translator->translate('Intl_Day_Long_StandAlone_' . $dayOfWeek);
            case "ccc":
            case "cc":
            case "c":
                return $translator->translate('Intl_Day_Short_StandAlone_' . $dayOfWeek);
            case "D":
                return 1 + (int)$this->toString('z'); // 1 - 366
            case "F":
                return (int)(((int)$this->toString('j') + 6) / 7);
            // week in month
            case "w":
                $weekDay = date('N', mktime(0, 0, 0, $this->toString('m'), 1, $this->toString('y')));
                return floor(($weekDay + (int)$this->toString('m') - 2) / 7) + 1;
            // week in year
            case "W":
                return $this->toString('N');
            // hour
            case "HH":
                return $this->toString('H');
            case "H":
                return $this->toString('G');
            case "hh":
                return $this->toString('h');
            case "h":
                return $this->toString('g');
            case "KK": // 00 .. 11
                return str_pad($this->toString('g') - 1, 2, '0');
            case "K": // 0 .. 11
                return $this->toString('g') - 1;
            case "kk": // 01 .. 24
                return str_pad($this->toString('G') + 1, 2, '0');
            case "k": // 1 .. 24
                return $this->toString('G') + 1;
            // minute
            case "mm":
            case "m":
                return $this->toString('i');
            // second
            case "ss":
            case "s":
                return $this->toString('s');
            // would normally also include AM, PM, Noon and Midnight
            case "b":
            // would normally be a textual presentation like "in the afternoon"
            case "B":
            // am / pm
            case "a":
                return $this->toString('a') == 'am' ? $translator->translate('Intl_Time_AM') : $translator->translate('Intl_Time_PM');

            // currently not implemented:
            case "G":
            case "GG":
            case "GGG":
            case "GGGG":
            case "GGGGG":
                return ''; // era
            case "z":
            case "Z":
            case "v":
                return ''; // time zone

        }

        return '';
    }

    protected static $tokens = array(
        'G', 'y', 'M', 'L', 'd', 'h', 'H', 'k', 'K', 'm', 's', 'E', 'c', 'e', 'D', 'F', 'w', 'W', 'a', 'b', 'B', 'z', 'Z', 'v',
    );

    /**
     * Parses the datetime format pattern and returns a tokenized result array
     *
     * Examples:
     * Input                     Output
     * 'dd.mm.yyyy'              array(array('dd'), '.', array('mm'), '.', array('yyyy'))
     * 'y?M?d?EEEE ah:mm:ss'   array(array('y'), '?', array('M'), '?', array('d'), '?', array('EEEE'), ' ', array('a'), array('h'), ':', array('mm'), ':', array('ss'))
     *
     * @param string $pattern the pattern to be parsed
     * @return array tokenized parsing result
     */
    protected static function parseFormat($pattern)
    {
        static $formats = array();  // cache
        if (isset($formats[$pattern])) {
            return $formats[$pattern];
        }
        $tokens = array();
        $n = strlen($pattern);
        $isLiteral = false;
        $literal = '';
        for ($i = 0; $i < $n; ++$i) {
            $c = $pattern[$i];
            if ($c === "'") {
                if ($i < $n - 1 && $pattern[$i + 1] === "'") {
                    $tokens[] = "'";
                    $i++;
                } elseif ($isLiteral) {
                    $tokens[] = $literal;
                    $literal = '';
                    $isLiteral = false;
                } else {
                    $isLiteral = true;
                    $literal = '';
                }
            } elseif ($isLiteral) {
                $literal .= $c;
            } else {
                for ($j = $i + 1; $j < $n; ++$j) {
                    if ($pattern[$j] !== $c) {
                        break;
                    }
                }
                $p = str_repeat($c, $j - $i);
                if (in_array($c, self::$tokens)) {
                    $tokens[] = array($p);
                } else {
                    $tokens[] = $p;
                }
                $i = $j - 1;
            }
        }
        if ($literal !== '') {
            $tokens[] = $literal;
        }
        return $formats[$pattern] = $tokens;
    }

    /**
     * Adds `$n` days to `$this` date and returns the result in a new Date.
     * instance.
     *
     * @param int $n Number of days to add, must be > 0.
     * @return \Piwik\Date
     */
    public function addDay($n)
    {
        $ts = strtotime("+$n day", $this->timestamp);
        return new Date($ts, $this->timezone);
    }

    /**
     * Adds `$n` Month to `$this` date and returns the result in a new Date.
     * instance.
     *
     * @param int $n Number of days to add, must be > 0.
     * @return \Piwik\Date
     */

    public function addMonth($n)
    {
        $ts = strtotime("+$n month", $this->timestamp);
        return new Date($ts, $this->timezone);
    }

    /**
     * Adds `$n` hours to `$this` date and returns the result in a new Date.
     *
     * @param int $n Number of hours to add. Can be less than 0.
     * @return \Piwik\Date
     */
    public function addHour($n)
    {
        $ts = self::addHourTo($this->timestamp, $n);
        return new Date($ts, $this->timezone);
    }

    /**
     * Adds N number of hours to a UNIX timestamp and returns the result. Using
     * this static function instead of {@link addHour()} will be faster since a
     * Date instance does not have to be created.
     *
     * @param int $timestamp The timestamp to add to.
     * @param number $n Number of hours to add, must be > 0.
     * @return int The result as a UNIX timestamp.
     */
    public static function addHourTo($timestamp, $n)
    {
        $isNegative = ($n < 0);
        $minutes = 0;
        if ($n != round($n)) {
            if ($n >= 1 || $n <= -1) {
                $extraMinutes = floor(abs($n));
                if ($isNegative) {
                    $extraMinutes = -$extraMinutes;
                }
                $minutes = abs($n - $extraMinutes) * 60;
                if ($isNegative) {
                    $minutes *= -1;
                }
            } else {
                $minutes = $n * 60;
            }
            $n = floor(abs($n));
            if ($isNegative) {
                $n *= -1;
            }
        }
        return (int)($timestamp + round($minutes * 60) + $n * 3600);
    }

    /**
     * Subtracts `$n` hours from `$this` date and returns the result in a new Date.
     *
     * @param int $n Number of hours to subtract. Can be less than 0.
     * @return \Piwik\Date
     */
    public function subHour($n)
    {
        return $this->addHour(-$n);
    }

    /**
     * Subtracts `$n` seconds from `$this` date and returns the result in a new Date.
     *
     * @param int $n Number of seconds to subtract. Can be less than 0.
     * @return \Piwik\Date
     */
    public function subSeconds($n)
    {
        return new Date($this->timestamp - $n, $this->timezone);
    }

    /**
     * Adds a period to `$this` date and returns the result in a new Date instance.
     *
     * @param int $n The number of periods to add. Can be negative.
     * @param string $period The type of period to add (YEAR, MONTH, WEEK, DAY, ...)
     * @return \Piwik\Date
     */
    public function addPeriod($n, $period)
    {
        if (strtolower($period) == 'month') { // TODO: comments
            $dateInfo = getdate($this->timestamp);

            $ts = mktime(
                $dateInfo['hours'],
                $dateInfo['minutes'],
                $dateInfo['seconds'],
                $dateInfo['mon'] + (int)$n,
                1,
                $dateInfo['year']
            );

            $daysToAdd = min($dateInfo['mday'], self::getMaxDaysInMonth($ts)) - 1;
            $ts += self::NUM_SECONDS_IN_DAY * $daysToAdd;
        } else {
            $time = $n < 0 ? "$n $period" : "+$n $period";

            $ts = strtotime($time, $this->timestamp);
        }

        return new Date($ts, $this->timezone);
    }

    private static function getMaxDaysInMonth($timestamp)
    {
        $month = (int)date('m', $timestamp);
        if (date('L', $timestamp) == 1
            && $month == 2
        ) {
            return 29;
        } else {
            return self::$maxDaysInMonth[$month];
        }
    }

    /**
     * Subtracts a period from `$this` date and returns the result in a new Date instance.
     *
     * @param int $n The number of periods to add. Can be negative.
     * @param string $period The type of period to add (YEAR, MONTH, WEEK, DAY, ...)
     * @return \Piwik\Date
     */
    public function subPeriod($n, $period)
    {
        return $this->addPeriod(-$n, $period);
    }

    /**
     * Returns the number of days represented by a number of seconds.
     *
     * @param int $secs
     * @return float
     */
    public static function secondsToDays($secs)
    {
        return $secs / self::NUM_SECONDS_IN_DAY;
    }

    private static function getInvalidDateFormatException($dateString)
    {
        $message = Piwik::translate('General_ExceptionInvalidDateFormat', array("YYYY-MM-DD, or 'today' or 'yesterday'", "strtotime", "http://php.net/strtotime"));
        return new Exception($message . ": " . var_export($dateString, true));
    }

    /**
     * For tests.
     * @return int|null
     * @ignore
     */
    public static function getNowTimestamp()
    {
        return isset(self::$now) ? self::$now : time();
    }
}
