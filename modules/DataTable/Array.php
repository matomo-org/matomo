<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Simple.php 168 2008-01-14 05:26:43Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * The DataTable_Array is a way to store an array of dataTable
 * 
 * @package Piwik_DataTable
 */
class Piwik_DataTable_Array
{
	protected $array = array();
	protected $nameKey = 'defaultKeyName';
	
	public function getNameKey()
	{
		return $this->nameKey;
	}
	public function setNameKey($name)
	{
		$this->nameKey = $name;
	}
	
	public function queueFilter( $className, $parameters = array() )
	{
		foreach($this->array as $table)
		{
			$table->queueFilter($className, $parameters);
		}
	}
	
	public function applyQueuedFilters()
	{
		foreach($this->array as $table)
		{
			$table->applyQueuedFilters();
		}
	}
	
	public function getArray()
	{
		return $this->array;
	}
	
	public function addTable( Piwik_DataTable $table, $label )
	{
		$this->array[$label] = $table;
	}
	
	public function __toString()
	{
		$renderer = new Piwik_DataTable_Renderer_Console($this);
		return (string)$renderer;
	}
	
}


