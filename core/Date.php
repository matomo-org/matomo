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
 * Date object widely used in Piwik.
 *
 * @package Piwik
 */
class Piwik_Date
{
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

    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Builds a Piwik_Date object
     *
     * @param int $timestamp
     * @param string $timezone
     * @throws Exception
     */
    protected function __construct($timestamp, $timezone = 'UTC')
    {
        if (!is_int($timestamp)) {
            throw new Exception("Piwik_Date is expecting a unix timestamp");
        }
        $this->timezone = $timezone;
        $this->timestamp = $timestamp;
    }

    /**
     * Returns a Piwik_Date objects.
     *
     * @param string|self $dateString  'today' 'yesterday' or any YYYY-MM-DD or timestamp
     * @param string $timezone    if specified, the dateString will be relative to this $timezone.
     *                                  For example, today in UTC+12 will be a timestamp in the future for UTC.
     *                                  This is different from using ->setTimezone()
     * @throws Exception
     * @return Piwik_Date
     */
    public static function factory($dateString, $timezone = null)
    {
        $invalidDateException = new Exception(Piwik_TranslateException('General_ExceptionInvalidDateFormat', array("YYYY-MM-DD, or 'today' or 'yesterday'", "strtotime", "http://php.net/strtotime")) . ": $dateString");
        if ($dateString instanceof self) {
            $dateString = $dateString->toString();
        }
        if ($dateString == 'now') {
            $date = self::now();
        } elseif ($dateString == 'today') {
            $date = self::today();
        } elseif ($dateString == 'yesterday') {
            $date = self::yesterday();
        } elseif ($dateString == 'yesterdaySameTime') {
            $date = self::yesterdaySameTime();
        } elseif (!is_int($dateString)
            && (
                // strtotime returns the timestamp for April 1st for a date like 2011-04-01,today
                // but we don't want this, as this is a date range and supposed to throw the exception
                strpos($dateString, ',') !== false
                    ||
                    ($dateString = strtotime($dateString)) === false
            )
        ) {
            throw $invalidDateException;
        } else {
            $date = new Piwik_Date($dateString);
        }
        $timestamp = $date->getTimestamp();
        // can't be doing web analytics before the 1st website
        // Tue, 06 Aug 1991 00:00:00 GMT
        if ($timestamp < 681436800) {
            throw $invalidDateException;
        }
        if (empty($timezone)) {
            return $date;
        }

        $timestamp = self::adjustForTimezone($timestamp, $timezone);
        return Piwik_Date::factory($timestamp);
    }

    /**
     * Returns the datetime of the current timestamp
     *
     * @return string
     */
    public function getDatetime()
    {
        return $this->toString(self::DATE_TIME_FORMAT);
    }

    /**
     * Returns the datetime start in UTC
     *
     * @return string
     */
    public function getDateStartUTC()
    {
        $dateStartUTC = gmdate('Y-m-d', $this->timestamp);
        $date = Piwik_Date::factory($dateStartUTC)->setTimezone($this->timezone);
        return $date->toString(self::DATE_TIME_FORMAT);
    }

    /**
     * Returns the datetime end in UTC
     *
     * @return string
     */
    public function getDateEndUTC()
    {
        $dateEndUTC = gmdate('Y-m-d 23:59:59', $this->timestamp);
        $date = Piwik_Date::factory($dateEndUTC)->setTimezone($this->timezone);
        return $date->toString(self::DATE_TIME_FORMAT);
    }

    /**
     * Returns a new date object, copy of $this, with the timezone set
     * This timezone is used to offset the UTC timestamp returned by @see getTimestamp()
     * Doesn't modify $this
     *
     * @param string $timezone  'UTC', 'Europe/London', ...
     * @return Piwik_Date
     */
    public function setTimezone($timezone)
    {
        return new Piwik_Date($this->timestamp, $timezone);
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
        if ($timezone == 'UTC') {
            return 0;
        }
        $start = substr($timezone, 0, 4);
        if ($start != 'UTC-'
            && $start != 'UTC+'
        ) {
            return false;
        }
        $offset = (float)substr($timezone, 4);
        if ($start == 'UTC-') {
            $offset = -$offset;
        }
        return $offset;
    }

    /**
     * Adjusts a UNIX timestamp in UTC to a specific timezone.
     *
     * @param int $timestamp  The UNIX timestamp to adjust.
     * @param string $timezone   The timezone to adjust to.
     * @return int  The adjusted time as seconds from EPOCH.
     */
    public static function adjustForTimezone($timestamp, $timezone)
    {
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
     * Returns the Unix timestamp of the date in UTC
     *
     * @return int
     */
    public function getTimestampUTC()
    {
        return $this->timestamp;
    }

    /**
     * Returns the unix timestamp of the date in UTC,
     * converted from the date timezone
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
        $time = date('r', $this->timestamp);
        $dtime = date_create($time);
        date_timezone_set($dtime, $dtzone);
        $dateWithTimezone = date_format($dtime, 'r');
        $dateWithoutTimezone = substr($dateWithTimezone, 0, -6);
        $timestamp = strtotime($dateWithoutTimezone);
        date_default_timezone_set('UTC');

        return (int)$timestamp;
    }

    /**
     * Returns true if the current date is older than the given $date
     *
     * @param Piwik_Date $date
     * @return bool
     */
    public function isLater(Piwik_Date $date)
    {
        return $this->getTimestamp() > $date->getTimestamp();
    }

    /**
     * Returns true if the current date is earlier than the given $date
     *
     * @param Piwik_Date $date
     * @return bool
     */
    public function isEarlier(Piwik_Date $date)
    {
        return $this->getTimestamp() < $date->getTimestamp();
    }

    /**
     * Returns the Y-m-d representation of the string.
     * You can specify the output, see the list on php.net/date
     *
     * @param string $part
     * @return string
     */
    public function toString($part = 'Y-m-d')
    {
        return date($part, $this->getTimestamp());
    }

    /**
     * @see toString()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Compares the week of the current date against the given $date
     * Returns 0 if equal, -1 if current week is earlier or 1 if current week is later
     * Example: 09.Jan.2007 13:07:25 -> compareWeek(2); -> 0
     *
     * @param Piwik_Date $date
     * @return int  0 = equal, 1 = later, -1 = earlier
     */
    public function compareWeek(Piwik_Date $date)
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
     * Compares the month of the current date against the given $date month
     * Returns 0 if equal, -1 if current month is earlier or 1 if current month is later
     * For example: 10.03.2000 -> 15.03.1950 -> 0
     *
     * @param Piwik_Date $date  Month to compare
     * @return int  0 = equal, 1 = later, -1 = earlier
     */
    public function compareMonth(Piwik_Date $date)
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
     * Returns true if current date is today
     *
     * @return bool
     */
    public function isToday()
    {
        return $this->toString('Y-m-d') === Piwik_Date::factory('today', $this->timezone)->toString('Y-m-d');
    }

    /**
     * Returns a date object set to now (same as today, except that the time is also set)
     *
     * @return Piwik_Date
     */
    public static function now()
    {
        return new Piwik_Date(time());
    }

    /**
     * Returns a date object set to today midnight
     *
     * @return Piwik_Date
     */
    public static function today()
    {
        return new Piwik_Date(strtotime(date("Y-m-d 00:00:00")));
    }

    /**
     * Returns a date object set to yesterday midnight
     *
     * @return Piwik_Date
     */
    public static function yesterday()
    {
        return new Piwik_Date(strtotime("yesterday"));
    }

    /**
     * Returns a date object set to yesterday same time of day
     *
     * @return Piwik_Date
     */
    public static function yesterdaySameTime()
    {
        return new Piwik_Date(strtotime("yesterday " . date('H:i:s')));
    }

    /**
     * Sets the time part of the date
     * Doesn't modify $this
     *
     * @param string $time  HH:MM:SS
     * @return Piwik_Date The new date with the time part set
     */
    public function setTime($time)
    {
        return new Piwik_Date(strtotime(date("Y-m-d", $this->timestamp) . " $time"), $this->timezone);
    }

    /**
     * Sets a new day
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $day  Day eg. 31
     * @return Piwik_Date  new date
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
        return new Piwik_Date($result, $this->timezone);
    }

    /**
     * Sets a new year
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $year  2010
     * @return Piwik_Date  new date
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
        return new Piwik_Date($result, $this->timezone);
    }

    /**
     * Subtracts days from the existing date object and returns a new Piwik_Date object
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $n
     * @return Piwik_Date  new date
     */
    public function subDay($n)
    {
        if ($n === 0) {
            return clone $this;
        }
        $ts = strtotime("-$n day", $this->timestamp);
        return new Piwik_Date($ts, $this->timezone);
    }

    /**
     * Subtracts weeks from the existing date object and returns a new Piwik_Date object
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $n
     * @return Piwik_Date  new date
     */
    public function subWeek($n)
    {
        return $this->subDay(7 * $n);
    }

    /**
     * Subtracts a month from the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $n
     * @return Piwik_Date  new date
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
        return new Piwik_Date($result, $this->timezone);
    }

    /**
     * Subtracts a year from the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $n
     * @return Piwik_Date  new date
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
        return new Piwik_Date($result, $this->timezone);
    }

    /**
     * Returns a localized date string, given a template.
     * Allowed tags are: %day%, %shortDay%, %longDay%, etc.
     *
     * @param string $template  string eg. %shortMonth% %longYear%
     * @return string  eg. "Aug 2009"
     */
    public function getLocalized($template)
    {
        $day = $this->toString('j');
        $dayOfWeek = $this->toString('N');
        $monthOfYear = $this->toString('n');
        $patternToValue = array(
            "%day%"        => $day,
            "%shortMonth%" => Piwik_Translate('General_ShortMonth_' . $monthOfYear),
            "%longMonth%"  => Piwik_Translate('General_LongMonth_' . $monthOfYear),
            "%shortDay%"   => Piwik_Translate('General_ShortDay_' . $dayOfWeek),
            "%longDay%"    => Piwik_Translate('General_LongDay_' . $dayOfWeek),
            "%longYear%"   => $this->toString('Y'),
            "%shortYear%"  => $this->toString('y'),
            "%time%"       => $this->toString('H:i:s')
        );
        $out = str_replace(array_keys($patternToValue), array_values($patternToValue), $template);
        return $out;
    }

    /**
     * Adds days to the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $n  Number of days to add
     * @return Piwik_Date  new date
     */
    public function addDay($n)
    {
        $ts = strtotime("+$n day", $this->timestamp);
        return new Piwik_Date($ts, $this->timezone);
    }

    /**
     * Adds hours to the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $n  Number of hours to add
     * @return Piwik_Date new date
     */
    public function addHour($n)
    {
        $ts = self::addHourTo($this->timestamp, $n);
        return new Piwik_Date($ts, $this->timezone);
    }

    /**
     * Adds N number of hours to a UNIX timestamp and returns the result.
     *
     * @param int $timestamp  The timestamp to add to.
     * @param number $n          Number of hours to add.
     * @return int  The result as a UNIX timestamp.
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
     * Substract hour to the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $n  Number of hours to substract
     * @return Piwik_Date  new date
     */
    public function subHour($n)
    {
        return $this->addHour(-$n);
    }

    /**
     * Adds period to the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $n
     * @param string $period  period to add (WEEK, DAY,...)
     * @return Piwik_Date  new date
     */
    public function addPeriod($n, $period)
    {
        if ($n < 0) {
            $ts = strtotime("$n $period", $this->timestamp);
        } else {
            $ts = strtotime("+$n $period", $this->timestamp);
        }
        return new Piwik_Date($ts, $this->timezone);
    }

    /**
     * Subtracts period from the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     *
     * @param int $n
     * @param string $period  period to sub
     * @return Piwik_Date  new date
     */
    public function subPeriod($n, $period)
    {
        return $this->addPeriod(-$n, $period);
    }
}
