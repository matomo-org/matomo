<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: MetadataCallbackAddMetadata.php 515 2008-06-08 20:03:21Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * Add a new metadata to the table based on the value resulting 
 * from a callback function with the parameter being another metadata value
 * 
 * For example for the searchEngine we have a "metadata" information that gives 
 * the URL of the search engine. We use this URL to add a new "metadata" that gives 
 * the path of the logo for this search engine URL (which has the format URL.png). 
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_MetadataCallbackAddMetadata extends Piwik_DataTable_Filter
{
	private $metadataToRead;
	private $functionToApply;
	private $metadataToAdd;
	
	public function __construct( $table, $metadataToRead, $metadataToAdd, $functionToApply )
	{
		parent::__construct($table);
		$this->functionToApply = $functionToApply;
		$this->metadataToRead = $metadataToRead;
		$this->metadataToAdd = $metadataToAdd;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			$oldValue = $row->getMetadata($this->metadataToRead);
			$newValue = call_user_func( $this->functionToApply, $oldValue);
			$row->addMetadata($this->metadataToAdd, $newValue);
		}
	}
}

