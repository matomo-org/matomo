<?php
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

	public function init()
	{
		require_once "config_test.php";
		require_once(SIMPLE_TEST . 'unit_tester.php');
		require_once(SIMPLE_TEST . 'reporter.php');
		flush();
		Piwik::createConfigObject();
		Piwik_Config::getInstance()->setTestEnvironment();
		Piwik::setMaxExecutionTime(300);
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
		return Piwik_Config::getInstance()->database_tests['dbname'];
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
	
	static public $testsHelpLinks = "<ul><li><a href=\"core\">Run each unit test separately</a> or each <a href='integration/'>each integration test separately</a></li>\n
					<li><a href=\"all_tests.php\">Run all tests (unit + integration tests)</a> or <a href='all_integration_tests.php'>all integration tests</a>. <br/><i>Note: please be patient, it will take at least 5-10 minutes</i></li>\n
					<li><a href=\"all_integration_tests.php?apiTestingLevel=none&widgetTestingLevel=check_errors \">Call all Widgets tests and check for error only</a> or <a href=\"all_integration_tests.php?apiTestingLevel=none&widgetTestingLevel=compare_output \">call all Widgets and compare output</a>. <br/><i>Note: Tests are currently failing, this is a work in progress, see <a href='http://dev.piwik.org/trac/ticket/2908'>#2908</a></i></li>\n
					<li><a href=\"javascript/\">Run piwik.js Javascript unit & integration tests</a>. <br/><i>Note: the Javascript tests are not executed in Jenkins so must be run manually on major browsers after any change to piwik.js</i></li>\n
				</ul>\n";
	public function run()
	{
		$test = new GroupTest("Piwik - running '{$this->testGroupType}' tests...");

		$intro = '';
		if (!$this->databaseRequired)
		{
			$intro .= $this->getTestDatabaseInfoMessage();
		}

		$intro .= self::$testsHelpLinks . "<hr/>";
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
			Piwik_Config::getInstance()->setTestEnvironment();	
			Piwik::createDatabaseObject();
			Piwik::disconnectDatabase();
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}

