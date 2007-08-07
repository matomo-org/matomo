<?php
class Piwik_Config extends Zend_Config_Ini
{
	function __construct($pathIniFile = null)
	{
		if(is_null($pathIniFile))
		{	
			$pathIniFile = PIWIK_INCLUDE_PATH . '/config/config.ini.php';
		}
		parent::__construct($pathIniFile, null, true);
		
		Zend_Registry::set('config', $this);
		
		$this->setPrefixTables();
	}
	
	public function setTestEnvironment()
	{
		$this->database = $this->database_tests;
		$this->log = $this->log_tests;
		$this->setPrefixTables();
	}
	
	private function setPrefixTables()
	{		
		Zend_Registry::set('tablesPrefix', $this->database->tables_prefix);
	}
}
?>
