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
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_SafeDecodeLabel extends Piwik_DataTable_Filter
{
	private $columnToDecode;
	private $outputHtml;
	public function __construct( $table, $outputHTML = true )
	{
		parent::__construct($table);
		$this->columnToDecode = 'label';
		$this->outputHtml = (bool)$outputHTML;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $row)
		{
			$value = $row->getColumn($this->columnToDecode);
			if($value !== false)
			{
				$value = htmlspecialchars_decode(
										urldecode($value),
										ENT_QUOTES);
				if($this->outputHtml)
				{
					$value = htmlspecialchars($value, ENT_QUOTES);
				}
				$row->setColumn($this->columnToDecode,$value);
			}
		}
	}
}
