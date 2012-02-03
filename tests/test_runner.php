<?php
flush();
require_once "config_test.php";
Piwik::createConfigObject();
Zend_Registry::get('config')->disableSavingConfigurationFileUpdates();
Piwik::setMaxExecutionTime(300);

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');

class TestRunner
{
	private $testGroupType;
	private $dirsToGlob = array();
	
	private $databaseRequired = false;
	
	private $introAppend = '';
	
	public function __construct($testGroupType)
	{
		$this->testGroupType = $testGroupType;
	}

	public function getNoBrowserErrorMessage()
	{
		return "ERROR: You do not appear to be running the tests via your browser. "
			 . "Some tests require that you run with a few system variables set (PATH_INFO or "
			 . " REQUEST_URI) so that Piwik can call itself via http to test the Tracker APIs.\n";
	}
	
	public function getTestDatabaseInfoMessage()
	{
		return "<p>Some tests require database access. The database used for tests is different from your normal " 
			 . "Piwik database. You may need to create this database ; you can edit the settings for the unit tests"
			 . " database access in your config file /config/global.ini.php</p><p><b>The database used in your tests "
			 . "is called \"" . $this->getTestDatabaseName() . "\". Create it if necessary.</b></p>\n";

	}
	
	public function getTestDatabaseName()
	{
		return Zend_Registry::get('config')->database_tests->dbname;
	}

	public function requireBrowser()
	{
		if (!$this->isBrowserPresent())
		{
			echo $this->getNoBrowserErrorMessage();
			exit;
		}
	}
	
	public function requireDatabase()
	{
		if (!$this->isTestDatabasePresent())
		{
			echo "ERROR: The test database (set as '{$this->getTestDatabaseName()}' in /config/global.ini.php) has "
			   . "not been created. Database access is required for the {$this->testGroupType} tests. Please create it.";
		    exit;
		}
		
		$this->databaseRequired = true;
	}
	
	public function setTestDirectories($dirs)
	{
		$this->dirsToGlob = $dirs;
	}
	
	public function run()
	{
		$test = new GroupTest("Piwik - running '{$this->testGroupType}' tests...");

		$intro = "<h2>Piwik - {$this->testGroupType} tests</h2>\n";

		if (!$this->databaseRequired)
		{
			$intro .= $this->getTestDatabaseInfoMessage();
		}

		$intro .= "<p><a href=\"core\">Run the tests by module</a></p>\n<hr/>\n";
		$intro .= $this->introAppend;

		$toInclude = array();
		foreach ($this->dirsToGlob as $dir)
		{
			$toInclude = array_merge($toInclude, Piwik::globr(PIWIK_INCLUDE_PATH . $dir, '*.test.php'));
		}
		
		// if present, make sure Database.test.php is first
		$idx = array_search(PIWIK_INCLUDE_PATH . '/tests/core/Database.test.php', $toInclude);
		if ($idx !== FALSE)
		{
			unset($toInclude[$idx]);
			array_unshift($toInclude, PIWIK_INCLUDE_PATH . '/tests/core/Database.test.php');
		}

		foreach($toInclude as $file)
		{
			$test->addFile($file);
		}

		$result = $test->run(new HtmlTimerReporter($intro));
		if (SimpleReporter::inCli()) {
			exit($result ? 0 : 1);
		}
	}
	
	public function appendIntro($text)
	{
		$this->introAppend .= $text;
	}

	public function isBrowserPresent()
	{
		return isset($_SERVER['HTTP_HOST'])
			   && (isset($_SERVER['PATH_INFO'])
				   || isset($_SERVER['REQUEST_URI'])
				   || isset($_SERVER['SCRIPT_NAME']));
	}
	
	public function isTestDatabasePresent()
	{
		try {
			Piwik::createConfigObject();
			Zend_Registry::get('config')->setTestEnvironment();	
			Piwik_Tracker_Config::getInstance()->setTestEnvironment();
			Piwik::createDatabaseObject();
			Piwik::disconnectDatabase();
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}

