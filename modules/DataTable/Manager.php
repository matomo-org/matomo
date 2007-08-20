<?php

class Piwik_DataTable_Manager
{
	static private $instance = null;
	protected function __construct()
	{}
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	protected $tables = array();
	protected $count = 0;
	
	function addTable( $table )
	{
		$this->tables[] = $table;
		$this->count++;
		return $this->count;
	}
	
	function getTable( $idTable )
	{
		// the array tables is indexed at 0 
		// but the index is computed as the count() of the array after inserting the table
		$idTable -= 1;
		
		if(!isset($this->tables[$idTable]))
		{
			throw new Exception("The request table $idTable couldn't be found.");
		}
		
		return $this->tables[$idTable];
	}
	
	function deleteAll()
	{
		foreach($this->tables as $key => $table)
		{
			unset($this->tables[$key]);
		}
		$this->tables = array();
	}
	function count()
	{
		return count($this->tables);
	}
}
?>
