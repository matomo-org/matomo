<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

/**
 * Add a new detail to the table based on the value resulting 
 * from a callback function with the parameter being another detail's value
 * 
 * For example for the searchEngine we have a "details" information that gives 
 * the URL of the search engine. We use this URL to add a new "details" that gives 
 * the path of the logo for this search engine URL (which has the format URL.png). 

 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_AddConstantDetail extends Piwik_DataTable_Filter
{
	private $detailToRead;
	private $functionToApply;
	private $detailToAdd;
	
	public function __construct( $table, $detailName, $detailValue )
	{
		parent::__construct($table);
		$this->name = $detailName;
		$this->value = $detailValue;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $row)
		{
			$row->addDetail($this->name, $this->value);
		}
	}
}

