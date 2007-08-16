<?php

class Piwik_Archive_Processing_Record_Manager
{
	protected $records = array();
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
	public function registerRecord( $record )
	{
		$this->records[] = $record;
	}
	public function unregister( $deleteRecord )
	{
		foreach($this->records as $key=> $record)
		{
			if($record->name == $deleteRecord->name)
			{
				unset($this->records[$key]);
				return;
			}
		}
	}
	public function toString()
	{
		$str = '';
		foreach($this->records as $record)
		{
			$str .= $record . "<br>\n";
		}
		return $str;
	}
	public function __toString()
	{
		return $this->toString();
	}
	public function getRecords()
	{
		return $this->records;
	}
}

abstract class Piwik_Archive_Processing_Record
{
	public $name;
	public $value;
	
	function __construct( $name, $value)
	{
		$this->name = $name;
		$this->value = $value;
		Piwik_Archive_Processing_Record_Manager::getInstance()->registerRecord($this);
	}
	public function delete()
	{
		Piwik_Archive_Processing_Record_Manager::getInstance()->unregister($this);
	}
}

class Piwik_Archive_Processing_Record_Numeric extends Piwik_Archive_Processing_Record
{	
	function __construct( $name, $value)
	{
		parent::__construct( $name, $value );
	}
	
	public function __toString()
	{
		return $this->name ." = ". $this->value;
	}
}


class Piwik_Archive_Processing_Record_Blob extends Piwik_Archive_Processing_Record
{
	public $name;
	public $value;
	function __construct( $name, $value)
	{
		$value = gzcompress($value);
		parent::__construct( $name, $value );
	}
	public function __toString()
	{
		return $this->name ." = BLOB";//". gzuncompress($this->value);
	}
}


class Piwik_Archive_Processing_Record_Blob_Array extends Piwik_Archive_Processing_Record
{
	public $name;
	public $value;
	function __construct( $name, $aValue)
	{		
		foreach($aValue as $id => $value)
		{
			// for the parent Table we keep the name
			// for example for the Table of searchEngines we keep the name 'referer_search_engine'
			// but for the child table of 'Google' which has the ID = 9 the name would be 'referer_search_engine_9'
			if($id == 0)
			{
				$newName = $name;
			}
			else
			{
				$newName = $name . '_' . $id;
			}
			$record = new Piwik_Archive_Processing_Record_Blob( $newName,  $value );
		}
	}
	public function __toString()
	{
		throw new Exception( 'Not valid' );
	}
	public function delete()
	{
		throw new Exception( 'Not valid' );
	}
	
}


?>
