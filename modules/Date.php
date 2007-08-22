<?php
Zend_Loader::loadClass('Zend_Date');
class Piwik_Date extends Zend_Date
{
	public function __construct( $strDate )
	{
		Zend_Date::setOptions(array('format_type' => 'php'));
		$strDate = date('Y-m-d', strtotime($strDate));
		parent::__construct( $strDate, 'YYYY-MM-dd', 'en');
		$this->strDate = $strDate;
	}

	public function toString($part = 'Y-m-d')
	{
		return parent::toString($part);
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
?>
