<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Date
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: DateObject.php 4786 2007-05-12 15:31:30Z thomas $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Include needed Date classes
 */
require_once 'Zend/Date/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Date
 * @subpackage Zend_Date_DateObject
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Date_DateObject {


    /**
     * UNIX Timestamp
     */
    private $_unixTimestamp;


    /**
     * active timezone
     */
    private $_timezone = 'UTC';
    private $_offset   = 0;
    private $_syncronised = 0;


    /**
     * Table of Monthdays
     */
    private static $_monthTable = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);


    /**
     * Table of Years
     */
    private static $_yearTable = array(
        1970 => 0,            1960 => -315619200,   1950 => -631152000,
        1940 => -946771200,   1930 => -1262304000,  1920 => -1577923200,
        1910 => -1893456000,  1900 => -2208988800,  1890 => -2524521600,
        1880 => -2840140800,  1870 => -3155673600,  1860 => -3471292800,
        1850 => -3786825600,  1840 => -4102444800,  1830 => -4417977600,
        1820 => -4733596800,  1810 => -5049129600,  1800 => -5364662400,
        1790 => -5680195200,  1780 => -5995814400,  1770 => -6311347200,
        1760 => -6626966400,  1750 => -6942499200,  1740 => -7258118400,
        1730 => -7573651200,  1720 => -7889270400,  1710 => -8204803200,
        1700 => -8520336000,  1690 => -8835868800,  1680 => -9151488000,
        1670 => -9467020800,  1660 => -9782640000,  1650 => -10098172800,
        1640 => -10413792000, 1630 => -10729324800, 1620 => -11044944000,
        1610 => -11360476800, 1600 => -11676096000);


    /**
     * Set this object to have a new UNIX timestamp.
     *
     * @param  string|integer  $timestamp  OPTIONAL timestamp; defaults to local time using time()
     * @return string|integer  old timestamp 
     * @throws Zend_Date_Exception
     */
    protected function setUnixTimestamp($timestamp = null)
    {
        $old = $this->_unixTimestamp;

        if (is_numeric($timestamp)) {
            $this->_unixTimestamp = $timestamp;
        } else if ($timestamp === null) {
            $this->_unixTimestamp = time();
        } else {
            throw new Zend_Date_Exception('\'' . $timestamp . '\' is not a valid UNIX timestamp', $timestamp);
        }

        return $old;
    }


    /**
     * Returns this object's UNIX timestamp
     * A timestamp greater then the integer range will be returned as string
     * This function does not return the timestamp as object. Use copy() instead. 
     *
     * @return  integer|string  timestamp
     */
    protected function getUnixTimestamp()
    {
        if ($this->_unixTimestamp === intval($this->_unixTimestamp)) {
            return (int) $this->_unixTimestamp;
        } else {
            return (string) $this->_unixTimestamp;
        }
    }

    /**
     * Internal function.
     * Returns time().  This method exists to allow unit tests to work-around methods that might otherwise
     * be hard-coded to use time().  For example, this makes it possible to test isYesterday() in Date.php.
     *
     * @param   integer  $sync      OPTIONAL time syncronisation value
     * @return  integer  timestamp
     */
    protected function _getTime($sync = null)
    {
        if ($sync !== null) {
            $this->_syncronised = round($sync);
        }
        return (time() + $this->_syncronised);
    }


    /**
     * Internal mktime function used by Zend_Date.
     * The timestamp returned by mktime() can exceed the precision of traditional UNIX timestamps, 
     * by allowing PHP to auto-convert to using a float value.
     *
     * Returns a timestamp relative to 1970/01/01 00:00:00 GMT/UTC.
     * DST (Summer/Winter) is depriciated since php 5.1.0.
     * Year has to be 4 digits otherwise it would be recognised as
     * year 70 AD instead of 1970 AD as expected !!
     *
     * @param  integer  $hour
     * @param  integer  $minute 
     * @param  integer  $second
     * @param  integer  $month
     * @param  integer  $day
     * @param  integer  $year
     * @param  boolean  $gmt     OPTIONAL true = other arguments are for UTC time, false = arguments are for local time/date
     * @return  integer|float  timestamp (number of seconds elapsed relative to 1970/01/01 00:00:00 GMT/UTC)
     */
    protected function mktime($hour, $minute, $second, $month, $day, $year, $gmt = false)
    {
        // complete date but in 32bit timestamp - use PHP internal
        if ((1901 < $year) and ($year < 2038)) {

            $oldzone = @date_default_timezone_get();
            // Timezone also includes DST settings, therefor substracting the GMT offset is not enough
            // We have to set the correct timezone to get the right value
            if (($this->_timezone != $oldzone) and ($gmt === false)) {
                $second -= $this->_offset;
                date_default_timezone_set($this->_timezone);
            }

            $result = ($gmt) ? @gmmktime($hour, $minute, $second, $month, $day, $year) 
                             :   @mktime($hour, $minute, $second, $month, $day, $year);
            date_default_timezone_set($oldzone);

            return $result;
        }

        // after here we are handling 64bit timestamps
        // get difference from local to gmt
        $difference = ($gmt) ? 0 
                             : $this->_offset;

        // date to integer
        $day   = intval($day);
        $month = intval($month);
        $year  = intval($year);

        // correct months > 12 and months < 1
        if ($month > 12) {
            $overlap = floor($month / 12);
            $year   += $overlap;
            $month  -= $overlap * 12;
        } else {
            $overlap = ceil((1 - $month) / 12);
            $year   -= $overlap;
            $month  += $overlap * 12;
        }

        $date = 0;
        if ($year >= 1970) {

            // Date is after UNIX epoch
            // go through leapyears
            // add months from letest given year
            for ($count = 1970; $count <= $year; $count++) {
                $leapyear = self::isYearLeapYear($count);
                if ($count < $year) {

                    $date += 365;
                    if ($leapyear === true) {
                        $date++;
                    }

                } else {

                    for ($mcount = 0; $mcount < ($month - 1); $mcount++) {
                        $date += self::$_monthTable[$mcount];
                        if (($leapyear === true) and ($mcount == 1)) {
                            $date++;
                        }

                    }
                }
            }

            $date += $day - 1;

            return (($date * 86400) + ($hour * 3600) + ($minute * 60) + $second + $difference);
        } else {
            
            // Date is before UNIX epoch
            // go through leapyears
            // add months from latest given year
            for ($count = 1969; $count >= $year; $count--) {

                $leapyear = self::isYearLeapYear($count);
                if ($count > $year)
                {
                    $date += 365;
                    if ($leapyear === true)
                        $date++;
                } else {

                    for ($mcount = 11; $mcount > ($month - 1); $mcount--) {
                        $date += self::$_monthTable[$mcount];
                        if (($leapyear === true) and ($mcount == 1)) {
                            $date++;
                        }

                    }
                }
            }

            $date += (self::$_monthTable[$month - 1] - $day);
            $date = -(($date * 86400) + (86400 - (($hour * 3600) + ($minute * 60) + $second))) + $difference;

            // gregorian correction for 5.Oct.1582
            if ($date < -12220185600) {
                $date += 864000;
            } else if ($date < -12219321600) {
                $date  = -12219321600;
            }

            return $date;
        }
    }


    /**
     * Returns true, if given $year is a leap year.
     *
     * @param  integer  $year
     * @return boolean  true, if year is leap year
     */
    protected static function isYearLeapYear($year)
    {
        // all leapyears can be divided through 4
        if (($year % 4) != 0) {
            return false;
        }

        // all leapyears can be divided through 400 
        if ($year % 400 == 0) {
            return true;
        } else if (($year > 1582) and ($year % 100 == 0)) {
            return false;
        }

        return true;
    }


    /**
     * Internal mktime function used by Zend_Date for handling 64bit timestamps.
     *
     * Returns a formatted date for a given timestamp.
     *
     * @param  string   $format     format for output
     * @param  mixed    $timestamp
     * @param  boolean  $gmt        OPTIONAL true = other arguments are for UTC time, false = arguments are for local time/date
     * @return string
     */
    protected function date($format, $timestamp = null, $gmt = false)
    {
        if ($timestamp === null) {

            $oldzone = @date_default_timezone_get();
            if ($this->_timezone != $oldzone) {
                date_default_timezone_set($this->_timezone);
            }

            $result = ($gmt) ? @gmdate($format) : @date($format);
            date_default_timezone_set($oldzone);

            return $result;
        }

        if (abs($timestamp) <= 0x7FFFFFFF) {

            $oldzone = @date_default_timezone_get();
            if ($this->_timezone != $oldzone) {
                date_default_timezone_set($this->_timezone);
            }

            $result = ($gmt) ? @gmdate($format, $timestamp) : @date($format, $timestamp);
            date_default_timezone_set($oldzone);

            return $result;
        }

        // check on false or null alone failes
        if (empty($gmt)) {
            $timestamp -= $this->_offset;
        }
        
        $date = $this->getDateParts($timestamp, true);
        $length = strlen($format);
        $output = '';
        
        for ($i = 0; $i < $length; $i++) {

            switch($format[$i]) {

                // day formats
                case 'd':  // day of month, 2 digits, with leading zero, 01 - 31
                    $output .= (($date['mday'] < 10) ? '0' . $date['mday'] : $date['mday']);
                    break;

                case 'D':  // day of week, 3 letters, Mon - Sun
                    $output .= date('D', 86400 * (3 + self::dayOfWeek($date['year'], $date['mon'], $date['mday'])));
                    break;

                case 'j':  // day of month, without leading zero, 1 - 31
                    $output .= $date['mday'];
                    break;

                case 'l':  // day of week, full string name, Sunday - Saturday
                    $output .= date('l', 86400 * (3 + self::dayOfWeek($date['year'], $date['mon'], $date['mday'])));
                    break;

                case 'N':  // ISO 8601 numeric day of week, 1 - 7
                    $day = self::dayOfWeek($date['year'], $date['mon'], $date['mday']);
                    if ($day == 0) {
                        $day = 7;
                    }
                    $output .= $day;
                    break;

                case 'S':  // english suffix for day of month, st nd rd th
                    if (($date['mday'] % 10) == 1) {
                        $output .= 'st';
                    } else if ((($date['mday'] % 10) == 2) and ($date['mday'] != 12)) {
                        $output .= 'nd';
                    } else if (($date['mday'] % 10) == 3) {
                        $output .= 'rd';
                    } else {
                        $output .= 'th';
                    }
                    break;

                case 'w':  // numeric day of week, 0 - 6
                    $output .= self::dayOfWeek($date['year'], $date['mon'], $date['mday']);
                    break;

                case 'z':  // day of year, 0 - 365
                    $output .= $date['yday'];
                    break;


                // week formats
                case 'W':  // ISO 8601, week number of year
                    $output .= $this->weekNumber($date['year'], $date['mon'], $date['mday']);
                    break;


                // month formats
                case 'F':  // string month name, january - december
                    $output .= date('F', mktime(0, 0, 0, $date['mon'], 2, 1971));
                    break;

                case 'm':  // number of month, with leading zeros, 01 - 12
                    $output .= (($date['mon'] < 10) ? '0' . $date['mon'] : $date['mon']);
                    break;

                case 'M':  // 3 letter month name, Jan - Dec
                    $output .= date('M',mktime(0, 0, 0, $date['mon'], 2, 1971));
                    break;

                case 'n':  // number of month, without leading zeros, 1 - 12
                    $output .= $date['mon'];
                    break;

                case 't':  // number of day in month
                    $output .= self::$_monthTable[$date['mon'] - 1];
                    break;


                // year formats
                case 'L':  // is leap year ?
                    $output .= (self::isYearLeapYear($date['year'])) ? '1' : '0';
                    break;

                case 'o':  // ISO 8601 year number
                    $week = $this->weekNumber($date['year'], $date['mon'], $date['mday']);
                    if (($week > 50) and ($date['mon'] == 1)) {
                        $output .= ($date['year'] - 1);
                    } else {
                        $output .= $date['year'];
                    }
                    break;

                case 'Y':  // year number, 4 digits
                    $output .= $date['year'];
                    break;

                case 'y':  // year number, 2 digits
                    $output .= substr($date['year'], strlen($date['year']) - 2, 2);
                    break;


                // time formats
                case 'a':  // lower case am/pm
                    $output .= (($date['hours'] >= 12) ? 'pm' : 'am');
                    break;

                case 'A':  // upper case am/pm
                    $output .= (($date['hours'] >= 12) ? 'PM' : 'AM');
                    break;

                case 'B':  // swatch internet time
                    $dayseconds = ($date['hours'] * 3600) + ($date['minutes'] * 60) + $date['seconds'];
                    if ($gmt === true) {
                        $dayseconds += 3600;
                    }
                    $output .= (int) (($dayseconds % 86400) / 86.4); 
                    break;

                case 'g':  // hours without leading zeros, 12h format
                    if ($date['hours'] > 12) {
                        $hour = $date['hours'] - 12;
                    } else {
                        if ($date['hours'] == 0) {
                            $hour = '12';
                        } else {
                            $hour = $date['hours'];
                        }
                    }
                    $output .= $hour;
                    break;

                case 'G':  // hours without leading zeros, 24h format
                    $output .= $date['hours'];
                    break;

                case 'h':  // hours with leading zeros, 12h format
                    if ($date['hours'] > 12) {
                        $hour = $date['hours'] - 12;
                    } else {
                        if ($date['hours'] == 0) {
                            $hour = '12';
                        } else {
                            $hour = $date['hours'];
                        }
                    }
                    $output .= (($hour < 10) ? '0'.$hour : $hour);
                    break;

                case 'H':  // hours with leading zeros, 24h format
                    $output .= (($date['hours'] < 10) ? '0' . $date['hours'] : $date['hours']);
                    break;

                case 'i':  // minutes with leading zeros
                    $output .= (($date['minutes'] < 10) ? '0' . $date['minutes'] : $date['minutes']);
                    break;

                case 's':  // seconds with leading zeros
                    $output .= (($date['seconds'] < 10) ? '0' . $date['seconds'] : $date['seconds']);
                    break;


                // timezone formats
                case 'e':  // timezone identifier
                    if ($gmt === true) {
                        $output .= gmdate('e', mktime($date['hours'], $date['minutes'], $date['seconds'], 
                                                      $date['mon'], $date['mday'], 2000));
                    } else {
                        $output .=   date('e', mktime($date['hours'], $date['minutes'], $date['seconds'], 
                                                      $date['mon'], $date['mday'], 2000));
                    }
                    break;

                case 'I':  // daylight saving time or not
                    if ($gmt === true) {
                        $output .= gmdate('I', mktime($date['hours'], $date['minutes'], $date['seconds'],
                                                      $date['mon'], $date['mday'], 2000));
                    } else {
                        $output .=   date('I', mktime($date['hours'], $date['minutes'], $date['seconds'],
                                                      $date['mon'], $date['mday'], 2000));
                    }
                    break;

                case 'O':  // difference to GMT in hours
                    $gmtstr = ($gmt === true) ? 0 : $this->_offset;
                    $output .= sprintf('%s%04d', ($gmtstr <= 0) ? '+' : '-', abs($gmtstr) / 36);
                    break;

                case 'P':  // difference to GMT with colon
                    $gmtstr = ($gmt === true) ? 0 : $this->_offset;
                    $gmtstr = sprintf('%s%04d', ($gmtstr <= 0) ? '+' : '-', abs($gmtstr) / 36);
                    $output = $output . substr($gmtstr, 0, 3) . ':' . substr($gmtstr, 3);
                    break;

                case 'T':  // timezone settings
                    if ($gmt === true) {
                        $output .= gmdate('T', mktime($date['hours'], $date['minutes'], $date['seconds'],
                                                      $date['mon'], $date['mday'], 2000));
                    } else {
                        $output .=   date('T', mktime($date['hours'], $date['minutes'], $date['seconds'],
                                                      $date['mon'], $date['mday'], 2000));
                    }
                    break;

                case 'Z':  // timezone offset in seconds
                    $output .= ($gmt === true) ? 0 : -$this->_offset;
                    break;


                // complete time formats
                case 'c':  // ISO 8601 date format
                    $difference = $this->_offset;
                    $difference = sprintf('%s%04d', ($difference <= 0) ? '+' : '-', abs($difference) / 36);
                    $output .= $date['year'] . '-'
                             . (($date['mon']     < 10) ? '0' . $date['mon']     : $date['mon'])     . '-'
                             . (($date['mday']    < 10) ? '0' . $date['mday']    : $date['mday'])    . 'T'
                             . (($date['hours']   < 10) ? '0' . $date['hours']   : $date['hours'])   . ':'
                             . (($date['minutes'] < 10) ? '0' . $date['minutes'] : $date['minutes']) . ':'
                             . (($date['seconds'] < 10) ? '0' . $date['seconds'] : $date['seconds'])
                             . $difference;
                    break;

                case 'r':  // RFC 2822 date format
                    $difference = $this->_offset;
                    $difference = sprintf('%s%04d', ($difference <= 0) ? '+' : '-', abs($difference) / 36);
                    $output .= gmdate('D', 86400 * (3 + self::dayOfWeek($date['year'], $date['mon'], $date['mday']))) . ', '
                             . (($date['mday']    < 10) ? '0' . $date['mday']    : $date['mday'])    . ' '
                             . date('M', mktime(0, 0, 0, $date['mon'], 2, 1971)) . ' '
                             . $date['year'] . ' '
                             . (($date['hours']   < 10) ? '0' . $date['hours']   : $date['hours'])   . ':'
                             . (($date['minutes'] < 10) ? '0' . $date['minutes'] : $date['minutes']) . ':'
                             . (($date['seconds'] < 10) ? '0' . $date['seconds'] : $date['seconds']) . ' '
                             . $difference;
                    break;

                case 'U':  // Unix timestamp
                    $output .= $timestamp;
                    break;


                // special formats
                case "\\":  // next letter to print with no format
                    $i++;
                    if ($i < $length) {
                        $output .= $format[$i];
                    }
                    break;

                default:  // letter is no format so add it direct
                    $output .= $format[$i];
                    break;
            }
        }

        return $output;
    }


    /**
     * Returns the day of week for a Gregorian calendar date.
     * 0 = sunday, 6 = saturday
     *
     * @param  integer  $year
     * @param  integer  $month
     * @param  integer  $day
     * @return integer  dayOfWeek
     */
    protected static function dayOfWeek($year, $month, $day)
    {
        if ((1901 < $year) and ($year < 2038)) {
            return (int) date('w', mktime(0, 0, 0, $month, $day, $year));
        }

        // gregorian correction
        $correction = 0;
        if (($year < 1582) or (($year == 1582) and (($month < 10) or (($month == 10) && ($day < 15))))) {
            $correction = 3;
        }

        if ($month > 2) {
            $month -= 2;
        } else {
            $month += 10;
            $year--;
        }

        $day  = floor((13 * $month - 1) / 5) + $day + ($year % 100) + floor(($year % 100) / 4);
        $day += floor(($year / 100) / 4) - 2 * floor($year / 100) + 77 + $correction;

        return (int) ($day - 7 * floor($day / 7));
    }


    /**
     * Internal getDateParts function for handling 64bit timestamps, similar to:
     * http://www.php.net/getdate
     *
     * Returns an array of date parts for $timestamp, relative to 1970/01/01 00:00:00 GMT/UTC.
     *
     * $fast specifies ALL date parts should be returned (slower)
     * Default is false, and excludes $dayofweek, weekday, month and timestamp from parts returned.
     *
     * @param   mixed    $timestamp
     * @param   boolean  $fast   OPTIONAL defaults to fast (false), resulting in fewer date parts
     * @return  array
     */
    protected function getDateParts($timestamp = null, $fast = null)
    {
        // actual timestamp
        if ($timestamp === null) {
            return getdate();
        }

        // 32bit timestamp
        if (abs($timestamp) <= 0x7FFFFFFF) {
            return @getdate($timestamp);
        }

        $otimestamp = $timestamp;
        $numday = 0;
        $month = 0;
        // gregorian correction
        if ($timestamp < -12219321600) {
            $timestamp -= 864000;
        }

        // timestamp lower 0
        if ($timestamp < 0) {
            $sec = 0;
            $act = 1970;

            // iterate through 10 years table, increasing speed
            foreach(self::$_yearTable as $year => $seconds) {
                if ($timestamp >= $seconds) {
                    $i = $act;
                    break;
                }
                $sec = $seconds;
                $act = $year;
            }

            $timestamp -= $sec;
            if (!isset($i)) {
                $i = $act;
            }

            // iterate the max last 10 years
            do {
                --$i;
                $day = $timestamp;

                $timestamp += 31536000;
                $leapyear = self::isYearLeapYear($i);
                if ($leapyear === true) {
                    $timestamp += 86400;
                }

                if ($timestamp >= 0) {
                    $year = $i;
                    break;
                }
            } while ($timestamp < 0);

            $secondsPerYear = 86400 * ($leapyear ? 366 : 365) + $day;

            $timestamp = $day;
            // iterate through months
            for ($i = 12; --$i >= 0;) {
                $day = $timestamp;

                $timestamp += self::$_monthTable[$i] * 86400;
                if (($leapyear === true) and ($i == 1)) {
                    $timestamp += 86400;
                }

                if ($timestamp >= 0) {
                    $month  = $i;
                    $numday = self::$_monthTable[$i];
                    if (($leapyear === true) and ($i == 1)) {
                        ++$numday;
                    }
                    break;
                }
            }

            $timestamp  = $day;
            $numberdays = $numday + ceil(($timestamp + 1) / 86400);

            $timestamp += ($numday - $numberdays + 1) * 86400;
            $hours      = floor($timestamp / 3600);
        } else {

            // iterate through years
            for ($i = 1970;;$i++) {
                $day = $timestamp;

                $timestamp -= 31536000;
                $leapyear = self::isYearLeapYear($i);
                if ($leapyear === true) {
                    $timestamp -= 86400;
                }

                if ($timestamp < 0) {
                    $year = $i;
                    break;
                }
            }

            $secondsPerYear = $day;

            $timestamp = $day;
            // iterate through months
            for ($i = 0; $i <= 11; $i++) {
                $day = $timestamp;
                $timestamp -= self::$_monthTable[$i] * 86400;

                if (($leapyear === true) and ($i == 1)) {
                    $timestamp -= 86400;
                }

                if ($timestamp < 0) {
                    $month  = $i;
                    $numday = self::$_monthTable[$i];
                    if (($leapyear === true) and ($i == 1)) {
                        ++$numday;
                    }
                    break;
                }
            }

            $timestamp  = $day;
            $numberdays = ceil(($timestamp + 1) / 86400);
            $timestamp  = $timestamp - ($numberdays - 1) * 86400;
            $hours = floor($timestamp / 3600); 
        }

        $timestamp -= $hours * 3600;

        $month  += 1;
        $minutes = floor($timestamp / 60);
        $seconds = $timestamp - $minutes * 60;

        if ($fast === true) {
            return array(
                'seconds' => $seconds,
                'minutes' => $minutes,
                'hours'   => $hours,
                'mday'    => $numberdays,
                'mon'     => $month,
                'year'    => $year,
                'yday'    => floor($secondsPerYear / 86400),
            );
        }

        $dayofweek = self::dayOfWeek($year, $month, $numberdays);

        return array(
                'seconds' => $seconds,
                'minutes' => $minutes,
                'hours'   => $hours,
                'mday'    => $numberdays,
                'wday'    => $dayofweek,
                'mon'     => $month,
                'year'    => $year,
                'yday'    => floor($secondsPerYear / 86400),
                'weekday' => gmdate('l', 86400 * (3 + $dayofweek)),
                'month'   => gmdate('F', mktime(0, 0, 0, $month, 1, 1971)),
                0         => $otimestamp
        );
    }


    /**
     * Internal getWeekNumber function for handling 64bit timestamps
     *
     * Returns the ISO 8601 week number of a given date
     *
     * @param  integer  $year
     * @param  integer  $month
     * @param  integer  $day
     * @return integer
     */
    protected function weekNumber($year, $month, $day)
    {
        if ((1901 < $year) and ($year < 2038)) {
            return (int) date('W', mktime(0, 0, 0, $month, $day, $year));
        }
        
        $dayofweek = self::dayOfWeek($year, $month, $day);
        $firstday  = self::dayOfWeek($year, 1, 1);
        if (($month == 1) and (($firstday < 1) or ($firstday > 4)) and ($day < 4)) {
            $firstday  = self::dayOfWeek($year - 1, 1, 1);
            $month     = 12;
            $day       = 31;

        } else if (($month == 12) and ((self::dayOfWeek($year + 1, 1, 1) < 5) and 
                   (self::dayOfWeek($year + 1, 1, 1) > 0))) {
            return 1;
        }

        return intval (((self::dayOfWeek($year, 1, 1) < 5) and (self::dayOfWeek($year, 1, 1) > 0)) + 
               4 * ($month - 1) + (2 * ($month - 1) + ($day - 1) + $firstday - $dayofweek + 6) * 36 / 256);
    }


    /**
     * Internal _range function
     * Sets the value $a to be in the range of [0, $b]
     * 
     * @param float $a - value to correct
     * @param float $b - maximum range to set
     */
    private function _range($a, $b) {
        while ($a < 0) {
            $a += $b;
        }
        while ($a >= $b) {
            $a -= $b;
        }
        return $a;
    }


    /**
     * Calculates the sunrise or sunset based on a location
     * 
     * @param  array  $location  Location for calculation MUST include 'latitude', 'longitude', 'horizon'
     * @param  bool   $horizon   true: sunrise; false: sunset
     * @return mixed  - false: midnight sun, integer: 
     */
    protected function calcSun($location, $horizon, $rise = false)
    {
        // timestamp within 32bit
        if (abs($this->_unixTimestamp) <= 0x7FFFFFFF) {
            if ($rise === false) {
                return date_sunset($this->_unixTimestamp, SUNFUNCS_RET_TIMESTAMP, $location['latitude'],
                                   $location['longitude'], 90 + $horizon, $this->_offset / 3600);
            }
            return date_sunrise($this->_unixTimestamp, SUNFUNCS_RET_TIMESTAMP, $location['latitude'],
                                $location['longitude'], 90 + $horizon, $this->_offset / 3600);
        }

        // self calculation - timestamp bigger than 32bit
        // fix circle values
        $quarterCircle      = 0.5 * M_PI;
        $halfCircle         =       M_PI;
        $threeQuarterCircle = 1.5 * M_PI;
        $fullCircle         = 2   * M_PI;

        // radiant conversion for coordinates
        $radLatitude  = $location['latitude']   * $halfCircle / 180;
        $radLongitude = $location['longitude']  * $halfCircle / 180;

        // get solar coordinates
        $tmpRise       = $rise ? $quarterCircle : $threeQuarterCircle;
        $radDay        = $this->date('z',$this->_unixTimestamp) + ($tmpRise - $radLongitude) / $fullCircle;

        // solar anomoly and longitude
        $solAnomoly    = $radDay * 0.017202 - 0.0574039;
        $solLongitude  = $solAnomoly + 0.0334405 * sin($solAnomoly);
        $solLongitude += 4.93289 + 3.49066E-4 * sin(2 * $solAnomoly);

        // get quadrant
        $solLongitude = $this->_range($solLongitude, $fullCircle);

        if (($solLongitude / $quarterCircle) - intval($solLongitude / $quarterCircle) == 0) {
            $solLongitude += 4.84814E-6;
        }

        // solar ascension
        $solAscension = sin($solLongitude) / cos($solLongitude);
        $solAscension = atan2(0.91746 * $solAscension, 1);

        // adjust quadrant
        if ($solLongitude > $threeQuarterCircle) {
            $solAscension += $fullCircle;
        } else if ($solLongitude > $quarterCircle) {
            $solAscension += $halfCircle;
        }

        // solar declination
        $solDeclination  = 0.39782 * sin($solLongitude);
        $solDeclination /=  sqrt(-$solDeclination * $solDeclination + 1);
        $solDeclination  = atan2($solDeclination, 1);

        $solHorizon = $horizon - sin($solDeclination) * sin($radLatitude);
        $solHorizon /= cos($solDeclination) * cos($radLatitude);

        // midnight sun, always night
        if (abs($solHorizon) > 1) {
            return false;
        }

        $solHorizon /= sqrt(-$solHorizon * $solHorizon + 1);
        $solHorizon  = $quarterCircle - atan2($solHorizon, 1);

        if ($rise) {
            $solHorizon = $fullCircle - $solHorizon;
        }

        // time calculation
        $localTime     = $solHorizon + $solAscension - 0.0172028 * $radDay - 1.73364;
        $universalTime = $localTime - $radLongitude;

        // determinate quadrant
        $universalTime = $this->_range($universalTime, $fullCircle);

        // radiant to hours
        $universalTime *= 24 / $fullCircle;

        // convert to time
        $hour = intval($universalTime);
        $universalTime    = ($universalTime - $hour) * 60;
        $min  = intval($universalTime);
        $universalTime    = ($universalTime - $min) * 60;
        $sec  = intval($universalTime);

        return $this->mktime($hour, $min, $sec, $this->date('m', $this->_unixTimestamp),
                             $this->date('j', $this->_unixTimestamp), $this->date('Y', $this->_unixTimestamp),
                             -1, true);
    }


    /**
     * Sets a new timezone for calculation of $this object's gmt offset.
     * For a list of supported timezones look here: http://php.net/timezones
     * If no timezone can be detected or the given timezone is wrong UTC will be set.
     * 
     * @param  string  $zone      OPTIONAL timezone for date calculation; defaults to date_default_timezone_get()
     * @return string  actual set timezone string
     * @throws Zend_Date_Exception
     */
    public function setTimezone($zone = null)
    {
        $oldzone = @date_default_timezone_get();
        if ($zone === null) {
            $zone = $oldzone;
        }

        // throw an error on false input, but only if the new date extension is avaiable
        if (function_exists('timezone_open')) {
            if (!@timezone_open($zone)) {
                throw new Zend_Date_Exception("timezone ($zone) is not a known timezone", $zone);
            }
        }
        // this can generate an error if the date extension is not avaiable and a false timezone is given
        $result = date_default_timezone_set($zone);
        if ($result === true) {
            $this->_offset   = mktime(0, 0, 0, 1, 2, 1970) - gmmktime(0, 0, 0, 1, 2, 1970);
            $this->_timezone = $zone;
        }
        date_default_timezone_set($oldzone);

        return $result;
    }


    /**
     * Return the timezone of $this object.
     * The timezone is initially set when the object is instantiated.
     * 
     * @return  string  actual set timezone string
     */
    public function getTimezone()
    {
        return $this->_timezone;
    }


    /**
     * Return the offset to GMT of $this object's timezone.
     * The offset to GMT is initially set when the object is instantiated using the currently,
     * in effect, default timezone for PHP functions.
     * 
     * @return  integer  seconds difference between GMT timezone and timezone when object was instantiated
     */
    public function getGmtOffset()
    {
        return $this->_offset;
    }
}
