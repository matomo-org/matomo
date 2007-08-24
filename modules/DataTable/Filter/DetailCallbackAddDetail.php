<?php

/**
 * Add a new detail to the table based on the value resulting 
 * from a callback function with the parameter being another detail's value
 * 
 * For example for the searchEngine we have a "details" information that gives 
 * the URL of the search engine. We use this URL to add a new "details" that gives 
 * the path of the logo for this search engine URL. 
 */
class Piwik_DataTable_Filter_DetailCallbackAddDetail extends Piwik_DataTable_Filter
{
	private $detailToRead;
	private $functionToApply;
	private $detailToAdd;
	
	public function __construct( $table, $detailToRead, $detailToAdd, $functionToApply )
	{
		parent::__construct($table);
		$this->functionToApply = $functionToApply;
		$this->detailToRead = $detailToRead;
		$this->detailToAdd = $detailToAdd;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			$oldValue = $row->getDetail($this->detailToRead);
			$newValue = call_user_func( $this->functionToApply, $oldValue);
			$row->addDetail($this->detailToAdd, $newValue);
		}
	}
}

