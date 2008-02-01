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
 * 
 * 
 * @package Piwik_Helper
 */
class Piwik_Date
{
	/**
	 * @param Timestamp date OR string format 2007-01-31
	 */
	public function __construct(  $date  )
	{
		if(is_int( $date ))
		{
			$this->timestamp =  $date ;
		}
		else
		{
			if (($timestamp = strtotime($date)) === false) 
			{
				throw new Exception("The date '$date' is not correct. The date format is YYYY-MM-DD or you can also use magic keywords such as 'today' or 'yesterday' or any keyword supported by the strtotime function (see http://php.net/strtotime for more information)");
			}
			$this->timestamp = $timestamp;
		}
	}
	
	public function getTimestamp()
	{
		return $this->timestamp;
	}
	
	public function isLater( Piwik_Date $date)
	{
		return $this->getTimestamp() > $date->getTimestamp();
	}
	public function isEarlier(Piwik_Date $date)
	{
		return $this->getTimestamp() < $date->getTimestamp();
	}
	
	public function toString($part = 'Y-m-d')
	{
		return date($part, $this->getTimestamp());
	}
	
	public function __toString()
	{
		return $this->toString();
	}

    /**
     * Sets a new day
     * Returned is the new date object
     * 
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
     * Compares only the week part, returning the difference
     * Returned is the new date object
     * Returns if equal, earlier or later
     * Example: 09.Jan.2007 13:07:25 -> compareWeek(2); -> 0
     *
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
     * Subtracts days from the existing date object.

     * Returned is the new date object
     * @return Piwik_Date  new date
     */
    public function subDay( $n )
    {
    	$ts = strtotime("-$n day", $this->getTimestamp());
		return new Piwik_Date( $ts );
    }
    
    /**
     * Subtracts a month from the existing date object.
     * Returned is the new date object
     * 
     * @return Piwik_Date  new date
     */
    public function subMonth( $n )
    {
//    	$ts = strtotime("-$n months", $this->getTimestamp());
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
     * @param  string  $part    OPTIONAL Part of the date to return, if null the timestamp is returned
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
     * Adds days to the existing date object.
     * Returned is the new date object
     * @return  Piwik_Date new date
     */
	public function addDay( $n )
	{
		$ts = strtotime("+$n day", $this->getTimestamp());
		return new Piwik_Date( $ts );
	}
	
    /**
     * Compares the month with the existing date object, ignoring other date parts.
     * For example: 10.03.2000 -> 15.03.1950 -> true
     * Returns if equal, earlier or later
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
	 * Returns a date object set to today midnight
	 */
	static public function today()
	{
		$date = new Piwik_Date(date("Y-m-d"));
		return $date;
	}
	/**
	 * Returns a date object set to yesterday midnight
	 */
	static public function yesterday()
	{
		$date = new Piwik_Date(date("Y-m-d", strtotime("yesterday")));
		return $date;
	}
	
	static public function factory($strDate)
	{
		switch($strDate)
		{
			case 'today': return self::today(); break;
			case 'yesterday': return self::yesterday(); break;
			default: return new Piwik_Date($strDate); break;
		}
	}
}

