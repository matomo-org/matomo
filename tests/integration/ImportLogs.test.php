<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Tests the log importer.
 */
class Test_Piwik_Integration_ImportLogs extends Test_Integration_Facade
{
	protected $dateTime = '2010-03-06 11:22:33';
	protected $idSite = null;
	protected $idGoal = null;
	
	public function getApiToTest()
	{
		return array(
		//FIXME!
//			array('all', array('idSite' => $this->idSite, 'date' => '2012-08-09', 'periods' => 'month')),
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'ImportLogs';
	}
	
	public function setUp()
	{
		parent::setUp();
		$this->idSite = $this->createWebsite($this->dateTime);
		
		// for conversion testing
		$this->idGoal = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'all', 'url', 'http', 'contains', false, 5);
	}
	
	
	/**
	 * Logs a couple visits for Aug 9, Aug 10, Aug 11 of 2012.
	 */
	protected function trackVisits()
	{
		//FIXMETODO
		return;
		$pwd = Zend_Registry::get('config')->superuser->password;
		if(strlen($pwd) != 32) $pwd = md5($pwd);

		$token_auth = Piwik_UsersManager_API::getInstance()->getTokenAuth(Zend_Registry::get('config')->superuser->login, $pwd);
		$python = Piwik_Common::isWindows() ? "C:\Python27\python.exe" : 'python';
		$cmd = $python . ' "'
			 . PIWIK_INCLUDE_PATH.'/misc/log-analytics/import_logs.py" ' # script loc
//			 . '-ddd ' // debug
			 . '--url="'.$this->getRootUrl().'tests/PHPUnit/proxy/" ' # proxy so that piwik uses test config files
			 . '--idsite='.$this->idSite.' '
			 . '--token-auth="'.$token_auth.'" '
			 . '--recorders=4 '
			 . '--enable-http-errors '
			 . '--enable-http-redirects '
			 . '--enable-static '
			 . '--enable-bots "'
			 . PIWIK_INCLUDE_PATH.'/tests/resources/fake_logs.log" ' # log file
			 . '2>&1'
			 ;
	    echo $cmd;
	    
		exec($cmd, $output, $result);
		if ($result !== 0)
		{
			echo "<pre>command: $cmd\nresult: $result\noutput: ".implode("\n", $output)."</pre>";
			throw new Exception("log importer failed");
		}
	}
}
