<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
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
	 * Builds a Piwik_Date object
	 * 
	 * @param int timestamp
	 */
	protected function __construct( $timestamp, $timezone = 'UTC')
	{
		if(!is_int( $timestamp ))
		{
			throw new Exception("Piwik_Date is expecting a unix timestamp");
		}
		$this->timezone = $timezone;
		$this->timestamp = $timestamp ;
	}
	
	
	/**
	 * Returns a Piwik_Date objects. 
	 *
	 * @param string $strDate 'today' 'yesterday' or any YYYY-MM-DD or timestamp
	 * @param string $timezone if specified, the dateString will be relative to this $timezone. 
	 * 				For example, today in UTC+12 will be a timestamp in the future for UTC.
     *              This is different from using ->setTimezone() 
	 * @return Piwik_Date 
	 */
	static public function factory($dateString, $timezone = null)
	{
		if($dateString == 'now')
		{
			$date = self::now();
		}
		elseif($dateString == 'today') 
		{
			$date = self::today();
		}
		elseif($dateString == 'yesterday')
		{
			$date = self::yesterday();
		}
		elseif($dateString == 'yesterdaySameTime')
		{
			$date = self::yesterdaySameTime();
		}
		elseif (!is_int($dateString)
			&& ($dateString = strtotime($dateString)) === false) 
		{
			throw new Exception(Piwik_TranslateException('General_ExceptionInvalidDateFormat', array("YYYY-MM-DD, or 'today' or 'yesterday'", "strtotime", "http://php.net/strtotime")));
		}
		else
		{
			$date = new Piwik_Date($dateString);
		}
		if(is_null($timezone))
		{
			return $date;
		}
		
		// manually adjust for UTC timezones
		$utcOffset = self::extractUtcOffset($timezone);
		if($utcOffset !== false)
		{
			return $date->addHour($utcOffset);
		}
		
		date_default_timezone_set($timezone);
		$datetime = $date->getDatetime();
		date_default_timezone_set('UTC');
		
		$date = Piwik_Date::factory(strtotime($datetime));
		
		return $date;
	}
	
	/*
	 * The stored timestamp is always UTC based.
	 * The returned timestamp via getTimestamp() will have the conversion applied
	 */
	protected $timestamp = null;
	
	/*
	 * Timezone the current date object is set to.
	 * Timezone will only affect the returned timestamp via getTimestamp()
	 */
 	protected $timezone = 'UTC';

 	const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
 	
 	/**
 	 * Returns the datetime start in UTC
 	 * 
 	 * @return string
 	 */
 	function getDateStartUTC()
 	{
 		$dateStartUTC = date('Y-m-d', $this->timestamp);
 		$date = Piwik_Date::factory($dateStartUTC)->setTimezone($this->timezone);
 		return $date->toString(self::DATE_TIME_FORMAT);
 	}

 	/**
 	 * Returns the datetime of the current timestamp
 	 * 
 	 * @return string
 	 */
 	function getDatetime()
 	{
 		return $this->toString(self::DATE_TIME_FORMAT);
 	}
 	
 	/**
 	 * Returns the datetime end in UTC
 	 * 
 	 * @return string
 	 */
 	function getDateEndUTC()
 	{
 		$dateEndUTC = date('Y-m-d 23:59:59', $this->timestamp);
 		$date = Piwik_Date::factory($dateEndUTC)->setTimezone($this->timezone);
 		return $date->toString(self::DATE_TIME_FORMAT);
 	}
 	
	/**
	 * Returns a new date object, copy of $this, with the timezone set
	 * This timezone is used to offset the UTC timestamp returned by @see getTimestamp()
	 * Doesn't modify $this
	 * 
	 * @param string $timezone 'UTC', 'Europe/London', ...  
	 */
	public function setTimezone($timezone)
	{
		return new Piwik_Date($this->timestamp, $timezone);
	}
	
	/**
	 * Helper function that returns the offset in the timezone string 'UTC+14'
	 * Returns false if the timezone is not UTC+X or UTC-X
	 * 
	 * @param $timezone
	 * @return int or false
	 */
	static protected function extractUtcOffset($timezone)
	{
		if($timezone == 'UTC')
		{
			return 0;
		}
		$start = substr($timezone, 0, 4);
		if($start != 'UTC-' 
			&& $start != 'UTC+')
		{
			return false;
		}
		$offset = (float)substr($timezone, 4);
		if($start == 'UTC-') {
			$offset = -$offset;
		}
		return $offset;
	}
	
	/**
	 * Returns the unix timestamp of the date in UTC, 
	 * converted from the date timezone
	 *
	 * @return int
	 */
	public function getTimestamp()
	{
		$utcOffset = self::extractUtcOffset($this->timezone);
		if($utcOffset !== false) {
			return (int)($this->timestamp - $utcOffset * 3600);
		}
		// @fixme
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
	public function isLater( Piwik_Date $date)
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
     * @return integer  0 = equal, 1 = later, -1 = earlier
     */
    public function compareWeek(Piwik_Date $date)
    {
		$currentWeek = date('W', $this->getTimestamp());
		$toCompareWeek = date('W', $date->getTimestamp());
		if( $currentWeek == $toCompareWeek)
		{
			return 0;
		}
		if( $currentWeek < $toCompareWeek)
		{
			return -1;
		}
		return 1;
    }
    
    /**
     * Compares the month of the current date against the given $date month
     * Returns 0 if equal, -1 if current month is earlier or 1 if current month is later
     * For example: 10.03.2000 -> 15.03.1950 -> 0
     *
     * @param  Piwik_Date  $month   Month to compare
     * @return integer  0 = equal, 1 = later, -1 = earlier
     */
	function compareMonth( Piwik_Date $date )
	{
		$currentMonth = date('n', $this->getTimestamp());
		$toCompareMonth = date('n', $date->getTimestamp());
		if( $currentMonth == $toCompareMonth)
		{
			return 0;
		}
		if( $currentMonth < $toCompareMonth)
		{
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
	static public function now()
	{
		return new Piwik_date(time());
	}
	
	/**
	 * Returns a date object set to today midnight
	 * 
	 * @return Piwik_Date
	 */
	static public function today()
	{
		return new Piwik_Date(strtotime(date("Y-m-d 00:00:00")));
	}
	
	/**
	 * Returns a date object set to yesterday midnight
	 * 
	 * @return Piwik_Date
	 */
	static public function yesterday()
	{
		return new Piwik_Date(strtotime("yesterday"));
	}
	
	/**
	 * Returns a date object set to yesterday same time of day
	 * 
	 * @return Piwik_Date
	 */
	static public function yesterdaySameTime()
	{
		return new Piwik_Date(strtotime("yesterday ".date('H:i:s')));
	}
	
	/**
	 * Sets the time part of the date
	 * Doesn't modify $this
	 * 
	 * @param string $time HH:MM:SS
	 * @return Piwik_Date The new date with the time part set
	 */
	public function setTime($time)
	{
		return new Piwik_Date( strtotime( date("Y-m-d", $this->timestamp) . " $time"), $this->timezone);
	}
	
    /**
     * Sets a new day
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @param int Day eg. 31
     * @return Piwik_Date  new date
     */
	public function setDay( $day )
	{
		$ts = $this->timestamp;
		$result = mktime( 
						date('H', $ts),
						date('i', $ts),
						date('s', $ts),
						date('n', $ts),
						1,
						date('Y', $ts)
					);
		return new Piwik_Date( $result, $this->timezone );
	}
	
    /**
     * Sets a new year
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @param int 2010
     * @return Piwik_Date  new date
     */
	public function setYear( $year )
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
		return new Piwik_Date( $result, $this->timezone );
	}
	
    /**
     * Subtracts days from the existing date object and returns a new Piwik_Date object
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @return Piwik_Date  new date
     */
    public function subDay( $n )
    {
    	if($n === 0) 
    	{
    		return clone $this;
    	}
    	$ts = strtotime("-$n day", $this->timestamp);
		return new Piwik_Date( $ts, $this->timezone );
    }
    
    /**
     * Subtracts a month from the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @return Piwik_Date  new date
     */
    public function subMonth( $n )
    {
    	if($n === 0) 
    	{
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
		return new Piwik_Date( $result, $this->timezone );
    }
	
	/**
	 * Returns a localized date string, given a template. 
	 * Allowed tags are: %day%, %shortDay%, %longDay%, etc.
	 * 
	 * @param $template string eg. %shortMonth% %longYear%
	 * @return string eg. "Aug 2009"
	 */
	public function getLocalized($template)
	{
		$day = $this->toString('j');
		$dayOfWeek = $this->toString('N');
		$monthOfYear = $this->toString('n');
		$patternToValue = array(
			"%day%" => $day,
			"%shortMonth%" => Piwik_Translate('General_ShortMonth_'.$monthOfYear),
			"%longMonth%" => Piwik_Translate('General_LongMonth_'.$monthOfYear),
			"%shortDay%" => Piwik_Translate('General_ShortDay_'.$dayOfWeek),
			"%longDay%" => Piwik_Translate('General_LongDay_'.$dayOfWeek),
			"%longYear%" => $this->toString('Y'),
			"%shortYear%" => $this->toString('y'),
			"%time%" => $this->toString('H:i:s')
		);
		$out = str_replace(array_keys($patternToValue), array_values($patternToValue), $template);
		return $out;
	}

    /**
     * Adds days to the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @param int Number of days to add
     * @return  Piwik_Date new date
     */
	public function addDay( $n )
	{
		$ts = strtotime("+$n day", $this->timestamp);
		return new Piwik_Date( $ts, $this->timezone );
	}
	
    /**
     * Adds hours to the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @param int Number of hours to add
     * @return  Piwik_Date new date
     */
	public function addHour( $n )
	{
		$minutes = 0;
		if($n != round($n))
		{
			$minutes = abs($n - floor($n)) * 60;
			$n = floor($n);
		}
		if($n > 0 ) 
		{
			$n = '+'.$n;
		}
		$ts = strtotime("$n hour $minutes minutes", $this->timestamp);
		return new Piwik_Date( $ts, $this->timezone );
	}

	/**
	 * Substract hour to the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @param int Number of hours to substract
     * @return  Piwik_Date new date
     */
	public function subHour( $n )
	{
		return $this->addHour(-$n);
	}
}
