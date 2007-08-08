<?php
Zend_Loader::loadClass('Zend_Date');
class Piwik_Date extends Zend_Date
{
	public function __construct( $strDate )
	{
		Zend_Date::setOptions(array('format_type' => 'php'));
		parent::__construct( $strDate, 'YYYY-MM-dd', 'en');
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
}
?>
