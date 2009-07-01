<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Helper
 */

/**
 * Date object widely used in Piwik.
 * 
 * @package Piwik_Helper
 */
class Piwik_Date
{
	/**
	 * Returns a Piwik_Date objects. 
	 * Accepts strings 'today' 'yesterday' or any YYYY-MM-DD or timestamp
	 *
	 * @param string $strDate
	 * @return Piwik_Date
	 */
	static public function factory($dateString)
	{
		if($dateString == 'today') 
		{
			return self::today();
		}
		if($dateString == 'yesterday')
		{
			return self::yesterday();
		}
		if (!is_int($dateString)
			&& ($dateString = strtotime($dateString)) === false) 
		{
			throw new Exception("Date format must be: YYYY-MM-DD, or 'today' or 'yesterday' or any keyword supported by the strtotime function (see http://php.net/strtotime for more information)");
		}
		return new Piwik_Date($dateString);
	}
	
	protected $timestamp = null;

	/**
	 * Returns the unix timestamp of the date
	 *
	 * @return int
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}
	
	/**
	 * Builds a Piwik_Date object
	 * 
	 * @param int timestamp
	 */
	protected function __construct( $date )
	{
		if(!is_int( $date ))
		{
			throw new Exception("Piwik_Date is expecting a unix timestamp");
		}
		$this->timestamp =  $date ;
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
		return new Piwik_Date( strtotime( $this->get("j F Y") . " $time"));
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
     * Sets a new day
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @param int Day eg. 31
     * @return Piwik_Date  new date
     */
	public function setDay( $day )
	{
		$ts = $this->getTimestamp();
		$result = mktime( 
						date('H', $ts),
						date('i', $ts),
						date('s', $ts),
						date('n', $ts),
						1,
						date('Y', $ts)
					);
		return new Piwik_Date( $result );
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
		$ts = $this->getTimestamp();
		$result = mktime( 
						date('H', $ts),
						date('i', $ts),
						date('s', $ts),
						date('n', $ts),
						date('j', $ts),
						$year
					);
		return new Piwik_Date( $result );
	}
	


    /**
     * Subtracts days from the existing date object and returns a new Piwik_Date object
     * Doesn't modify $this
     * 
     * Returned is the new date object
     * @return Piwik_Date  new date
     */
    public function subDay( $n )
    {
    	if($n === 0) 
    	{
    		return clone $this;
    	}
    	$ts = strtotime("-$n day", $this->getTimestamp());
		return new Piwik_Date( $ts );
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
		$ts = $this->getTimestamp();
		$result = mktime( 
						date('H', $ts),
						date('i', $ts),
						date('s', $ts),
						date('n', $ts) - $n,
						1, // we set the day to 1
						date('Y', $ts)
					);
		return new Piwik_Date( $result );
    }
    
    /**
     * Returns a representation of a date or datepart
     *
     * @param  string  OPTIONAL Part of the date to return, if null the timestamp is returned
     * @return integer|string  date or datepart
     */
	public function get($part = null)
	{
		if(is_null($part))
		{
			return $this->getTimestamp();
		}
		return date($part, $this->getTimestamp());
	}
	
	/**
	 * Returns a localized date string, given a template. 
	 * Allowed tags are: %day%, %shortDay%, %longDay%, etc.
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
			"%time%" => $this->toString('H:i:s T')
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
		$ts = strtotime("+$n day", $this->getTimestamp());
		return new Piwik_Date( $ts );
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
		return $this->get('Y-m-d') === date('Y-m-d', time());
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
	 * @return Piwik_Date
	 */
	static public function yesterday()
	{
		return new Piwik_Date(strtotime("yesterday"));
	}
}
