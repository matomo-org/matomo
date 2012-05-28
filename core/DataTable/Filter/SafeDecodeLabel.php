<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_SafeDecodeLabel extends Piwik_DataTable_Filter
{
	private $columnToDecode;
	static private $outputHtml = true;

	/**
	 * @param Piwik_DataTable $table
	 */
	public function __construct( $table )
	{
		parent::__construct($table);
		$this->columnToDecode = 'label';
	}

	/**
	 * Decodes the given value
	 *
	 * @param string  $value
	 * @return mixed|string
	 */
	static public function filterValue($value)
	{
		$value = htmlspecialchars_decode( urldecode($value), ENT_QUOTES);
		if(self::$outputHtml)
		{
			$value = htmlspecialchars($value, ENT_QUOTES);
		}
		return $value;
	}

	/**
	 * Decodes all columns of the given data table
	 *
	 * @param Piwik_DataTable  $table
	 */
	public function filter($table)
	{
		foreach($table->getRows() as $row)
		{
			$value = $row->getColumn($this->columnToDecode);
			if($value !== false)
			{
				$value = self::filterValue($value);
				$row->setColumn($this->columnToDecode,$value);
				
				$this->filterSubTable($row);
			}
		}
	}
	
}
