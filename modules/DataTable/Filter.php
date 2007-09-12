<?php
/**
 * A filter is applied instantly to a given DataTable and can 
 * - remove rows 
 * - change columns values
 * - add/remove columns or details
 * - add/remove/edit sub DataTable associated to some rows
 * - whatever your imagination wants :)
 * 
 * The concept is very simple: the filter is given the DataTable 
 * and can do whatever is necessary on the data (in the filter() method).
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter
 */
abstract class Piwik_DataTable_Filter
{
	protected $table;
	
	public function __construct($table)
	{
		if(!($table instanceof Piwik_DataTable))
		{
			throw new Exception("The filter accepts only a Piwik_DataTable object.");
		}
		$this->table = $table;
	}
	
	abstract protected function filter();
}

require_once "DataTable/Filter/ColumnCallback.php";
require_once "DataTable/Filter/ColumnCallbackAddDetail.php";
require_once "DataTable/Filter/ColumnCallbackReplace.php";
require_once "DataTable/Filter/DetailCallbackAddDetail.php";
require_once "DataTable/Filter/Empty.php";
require_once "DataTable/Filter/ExcludeLowPopulation.php";
require_once "DataTable/Filter/Limit.php";
require_once "DataTable/Filter/Pattern.php";
require_once "DataTable/Filter/PatternRecursive.php";
require_once "DataTable/Filter/ReplaceColumnNames.php";
require_once "DataTable/Filter/Sort.php";
