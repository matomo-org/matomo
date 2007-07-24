<?php
class Piwik_Config extends Zend_Config_Ini
{
	function __construct()
	{
		$pathIniFile = PIWIK_INCLUDE_PATH . '/config/config.ini';

		parent::__construct($pathIniFile, null, true);
		
		Zend_Registry::set('config', $this);
		
		$this->setPrefixTables();
	}
	
	public function setTestEnvironment()
	{
		$this->database = $this->database_tests;
		$this->setPrefixTables();
	}
	
	public function setPrefixTables()
	{		
		Zend_Registry::set('tablesPrefix', $this->database->tables_prefix);
	}
}
?>
