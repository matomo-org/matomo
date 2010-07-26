<?php
/**
 * Main class file
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * The default language setting if none is set/retrievable
 *
 */
define ('PHPSECINFO_LANG_DEFAULT', 'en');

/**
 * a general version string to differentiate releases
 *
 */
define ('PHPSECINFO_VERSION', '0.2.2');

/**
 * a YYYYMMDD date string to indicate "build" date
 *
 */
define ('PHPSECINFO_BUILD', '20080723');

/**
 * Homepage for phpsecinfo project
 *
 */
define ('PHPSECINFO_URL', 'http://phpsecinfo.com');

/**
 * The base folder where views are stored.  Include trailing slash
 *
 */
define('PHPSECINFO_VIEW_DIR_DEFAULT', 'View/');


/**
 * The default format, used to load the proper view.
 */
define('PHPSECINFO_FORMAT_DEFAULT', 'Html');


/**
 * The base directory, used to resolve requires and includes
 */
define('PHPSECINFO_BASE_DIR', dirname(__FILE__));

/**
 * This is the main class for the phpsecinfo system.  It's responsible for
 * dynamically loading tests, running those tests, and generating the results
 * output
 *
 * Example:
 * <code>
 * <?php require_once(PHPSECINFO_BASE_DIR.'/PhpSecInfo.php'); ?>
 * <?php phpsecinfo(); ?>
 * </code>
 *
 * If you want to capture the output, or just grab the test results and display them
 * in your own way, you'll need to do slightly more work.
 *
 * Example:
 * <code>
 * require_once(PHPSECINFO_BASE_DIR.'/PhpSecInfo.php');
 * // instantiate the class
 * $psi = new PhpSecInfo();
 *
 * // load and run all tests
 * $psi->loadAndRun();
 *
 * // grab the results as a multidimensional array
 * $results = $psi->getResultsAsArray();
 * echo "<pre>"; echo print_r($results, true); echo "</pre>";
 *
 * // grab the standard results output as a string
 * $html = $psi->getOutput();
 *
 * // send it to the browser
 * echo $html;
 * </code>
 *
 *
 * The procedural function "phpsecinfo" is defined below this class.
 * @see phpsecinfo()
 *
 * @author Ed Finkler <coj@funkatron.com>
 *
 * see CHANGELOG for changes
 *
 */
class PhpSecInfo
{

	/**
	 * An array of tests to run
	 *
	 * @var array PhpSecInfo_Test
	 */
	var $tests_to_run = array();


	/**
	 * An array of results.  Each result is an associative array:
	 * <code>
	 * $result['result'] = PHPSECINFO_TEST_RESULT_NOTICE;
	 * $result['message'] = "a string describing the test results and what they mean";
	 * </code>
	 *
	 * @var array
	 */
	var $test_results = array();


	/**
	 * An array of tests that were not run
	 *
	 * <code>
	 * $result['result'] = PHPSECINFO_TEST_RESULT_NOTRUN;
	 * $result['message'] = "a string explaining why the test was not run";
	 * </code>
	 *
	 * @var array
	 */
	var $tests_not_run = array();


	/**
	 * The language code used.  Defaults to PHPSECINFO_LANG_DEFAULT, which
	 * is 'en'
	 *
	 * @var string
	 * @see PHPSECINFO_LANG_DEFAULT
	 */
	var $language = PHPSECINFO_LANG_DEFAULT;


	/**
	 * An array of integers recording the number of test results in each category.  Categories can include
	 * some or all of the PHPSECINFO_TEST_* constants.  Constants are the keys, # of results are the values.
	 *
	 * @var array
	 */
	var $result_counts = array();


	/**
	 * The number of tests that have been run
	 *
	 * @var integer
	 */
	var $num_tests_run = 0;


	/**
	 * The base directory for phpsecinfo. Set within the constructor. Paths are resolved from this.
	 * @var string
	 */
	var $_base_dir;


	/**
	 * The directory PHPSecInfo will look for views.  It defaults to the value
	 * in PHPSECINFO_VIEW_DIR_DEFAULT, but can be changed with the setViewDirectory()
	 * method.
	 *
	 * @var string
	 */
	var $_view_directory;


	/**
	 * The output format, used to load the proper view
	 *
	 * @var string
	 **/
	var $_format;

	/**
	 * Constructor
	 *
	 * @return PhpSecInfo
	 */
	function PhpSecInfo($opts = null) {
		
		$this->_base_dir = dirname(__FILE__);
		
		if ($opts) {
			if (isset($opts['view_directory'])) {
				$this->setViewDirectory($opts['view_directory']);
			} else {
				$this->setViewDirectory(dirname(__FILE__).DIRECTORY_SEPARATOR . PHPSECINFO_VIEW_DIR_DEFAULT);
			}
			
			if (isset($opts['format'])) {
				$this->setFormat($opts['format']);
			} else {
				if (strtolower(php_sapi_name()) == 'cli' ) {
					$this->setFormat('Cli');
				} else {
					$this->setFormat(PHPSECINFO_FORMAT_DEFAULT);
				}
			}
			
		} else { /* Use defaults */
			$this->setViewDirectory(dirname(__FILE__).DIRECTORY_SEPARATOR . PHPSECINFO_VIEW_DIR_DEFAULT);
			if (strtolower(php_sapi_name()) == 'cli' ) {
				$this->setFormat('Cli');
			} else {
				$this->setFormat(PHPSECINFO_FORMAT_DEFAULT);
			}
		}
	}


	/**
	 * recurses through the Test subdir and includes classes in each test group subdir,
	 * then builds an array of classnames for the tests that will be run
	 *
	 */
	function loadTests() {

		$test_root = dir(dirname(__FILE__).DIRECTORY_SEPARATOR.'Test');

		//echo "<pre>"; echo print_r($test_root, true); echo "</pre>";

		while (false !== ($entry = $test_root->read())) {
			if ( is_dir($test_root->path.DIRECTORY_SEPARATOR.$entry) && !preg_match('~^(\.|_vti)(.*)$~', $entry) ) {
				$test_dirs[] = $entry;
			}
		}
		//echo "<pre>"; echo print_r($test_dirs, true); echo "</pre>";

		// include_once all files in each test dir
		foreach ($test_dirs as $test_dir) {
			$this_dir = dir($test_root->path.DIRECTORY_SEPARATOR.$test_dir);

			while (false !== ($entry = $this_dir->read())) {
				if (!is_dir($this_dir->path.DIRECTORY_SEPARATOR.$entry)) {
					include_once $this_dir->path.DIRECTORY_SEPARATOR.$entry;
					$classNames[] = "PhpSecInfo_Test_".$test_dir."_".basename($entry, '.php');
				}
			}

		}

		// modded this to not throw a PHP5 STRICT notice, although I don't like passing by value here
		$this->tests_to_run = $classNames;
	}


	/**
	 * This runs the tests in the tests_to_run array and
	 * places returned data in the following arrays/scalars:
	 * - $this->test_results
	 * - $this->result_counts
	 * - $this->num_tests_run
	 * - $this->tests_not_run;
	 *
	 */
	function runTests() {
		// initialize a bunch of arrays
		$this->test_results  = array();
		$this->result_counts = array();
		$this->result_counts[PHPSECINFO_TEST_RESULT_NOTRUN] = 0;
		$this->num_tests_run = 0;

		foreach ($this->tests_to_run as $testClass) {

			/**
			 * @var $test PhpSecInfo_Test
			 */
			$test = new $testClass();

			if ($test->isTestable()) {
				$test->test();
				$rs = array(	'result' => $test->getResult(),
							'message' => $test->getMessage(),
							'value_current' => $test->getCurrentTestValue(),
							'value_recommended' => $test->getRecommendedTestValue(),
							'moreinfo_url' => $test->getMoreInfoURL(),
						);
				$this->test_results[$test->getTestGroup()][$test->getTestName()] = $rs;

				// initialize if not yet set
				if (!isset ($this->result_counts[$rs['result']]) ) {
					$this->result_counts[$rs['result']] = 0;
				}

				$this->result_counts[$rs['result']]++;
				$this->num_tests_run++;
			} else {
				$rs = array(	'result' => $test->getResult(),
							'message' => $test->getMessage(),
							'value_current' => NULL,
							'value_recommended' => NULL,
							'moreinfo_url' => $test->getMoreInfoURL(),
						);
				$this->result_counts[PHPSECINFO_TEST_RESULT_NOTRUN]++;
				$this->tests_not_run[$test->getTestGroup()."::".$test->getTestName()] = $rs;
			}
		}
	}


	/**
	 * This is the main output method.  The look and feel mimics phpinfo()
	 *
	 */
	function renderOutput($page_title="Security Information About PHP") {
		/**
		 * We need to use PhpSecInfo_Test::getBooleanIniValue() below
		 * @see PhpSecInfo_Test::getBooleanIniValue()
		 */
		if (!class_exists('PhpSecInfo_Test')) {
			include( dirname(__FILE__).DIRECTORY_SEPARATOR.'Test'.DIRECTORY_SEPARATOR.'Test.php');
		}
		$this->loadView($this->_format);
	}


	/**
	 * This is a helper method that makes it easy to output tables of test results
	 * for a given test group
	 *
	 * @param string $group_name
	 * @param array $group_results
	 */
	function _outputRenderTable($group_name, $group_results) {

		// exit out if $group_results was empty or not an array.  This sorta seems a little hacky...
		if (!is_array($group_results) || sizeof($group_results) < 1) {
			return false;
		}

		ksort($group_results);

		$this->loadView($this->_format.'/Result', array('group_name'=>$group_name, 'group_results'=>$group_results));

		return true;
	}



	/**
	 * This outputs a table containing a summary of the test results (counts and % in each result type)
	 *
	 * @see PHPSecInfo::_outputRenderTable()
	 * @see PHPSecInfo::_outputGetResultTypeFromCode()
	 */
	function _outputRenderStatsTable() {

		foreach($this->result_counts as $code=>$val) {
			if ($code != PHPSECINFO_TEST_RESULT_NOTRUN) {
				$percentage = round($val/$this->num_tests_run * 100,2);
				$result_type = $this->_outputGetResultTypeFromCode($code);
				$stats[$result_type] = array( 'count' => $val,
											'result' => $code,
											'message' => "$val out of {$this->num_tests_run} ($percentage%)");
			}
		}

		$this->_outputRenderTable('Test Results Summary', $stats);

	}



	/**
	 * This outputs a table containing a summary or test that were not executed, and the reasons why they were skipped
	 *
	 * @see PHPSecInfo::_outputRenderTable()
	 */
	function _outputRenderNotRunTable() {

		$this->_outputRenderTable('Tests Not Run', $this->tests_not_run);

	}




	/**
	 * This is a helper function that returns a CSS class corresponding to
	 * the result code the test returned.  This allows us to color-code
	 * results
	 *
	 * @param integer $code
	 * @return string
	 */
	function _outputGetCssClassFromResult($code) {

		switch ($code) {
			case PHPSECINFO_TEST_RESULT_OK:
				return 'value-ok';
				break;

			case PHPSECINFO_TEST_RESULT_NOTICE:
				return 'value-notice';
				break;

			case PHPSECINFO_TEST_RESULT_WARN:
				return 'value-warn';
				break;

			case PHPSECINFO_TEST_RESULT_NOTRUN:
				return 'value-notrun';
				break;

			case PHPSECINFO_TEST_RESULT_ERROR:
				return 'value-error';
				break;

			default:
				return 'value-notrun';
				break;
		}

	}



	/**
	 * This is a helper function that returns a label string corresponding to
	 * the result code the test returned.  This is mainly used for the Test
	 * Results Summary table.
	 *
	 * @see PHPSecInfo::_outputRenderStatsTable()
	 * @param integer $code
	 * @return string
	 */
	function _outputGetResultTypeFromCode($code) {

		switch ($code) {
			case PHPSECINFO_TEST_RESULT_OK:
				return 'Pass';
				break;

			case PHPSECINFO_TEST_RESULT_NOTICE:
				return 'Notice';
				break;

			case PHPSECINFO_TEST_RESULT_WARN:
				return 'Warning';
				break;

			case PHPSECINFO_TEST_RESULT_NOTRUN:
				return 'Not Run';
				break;

			case PHPSECINFO_TEST_RESULT_ERROR:
				return 'Error';
				break;

			default:
				return 'Invalid Result Code';
				break;
		}

	}


	/**
	 * Loads and runs all the tests
	 *
	 * As loading, then running, is a pretty common process, this saves a extra method call
	 *
	 * @since 0.1.1
	 *
	 */
	function loadAndRun() {
		$this->loadTests();
		$this->runTests();
	}


	/**
	 * returns an associative array of test data.  Four keys are set:
	 * - test_results  (array)
	 * - tests_not_run (array)
	 * - result_counts (array)
	 * - num_tests_run (integer)
	 *
	 * note that this must be called after tests are loaded and run
	 *
	 * @since 0.1.1
	 * @return array
	 */
	function getResultsAsArray() {
		$results = array();

		$results['test_results'] = $this->test_results;
		$results['tests_not_run'] = $this->tests_not_run;
		$results['result_counts'] = $this->result_counts;
		$results['num_tests_run'] = $this->num_tests_run;

		return $results;
	}



	/**
	 * returns the standard output as a string instead of echoing it to the browser
	 *
	 * note that this must be called after tests are loaded and run
	 *
	 * @since 0.1.1
	 *
	 * @return string
	 */
	function getOutput() {
		ob_start();
		$this->renderOutput();
		$output = ob_get_clean();
		return $output;
	}


	/**
	 * A very, very simple "view" system
	 *
	 */
	function loadView($view_name, $data=null) {
		if ($data != null) {
			extract($data);
		}

		$view_file = $this->getViewDirectory().$view_name.".php";

		if ( file_exists($view_file) && is_readable($view_file) ) {
			ob_start();
			include $view_file;
			echo ob_get_clean();
		} else {
			user_error("The view '{$view_file}' either does not exist or is not readable", E_USER_WARNING);
		}


	}


	/**
	 * Returns the current view directory
	 *
	 * @return string
	 */
	function getViewDirectory() {
		return $this->_view_directory;
	}


	/**
	 * Sets the directory that PHPSecInfo will look in for views
	 *
	 * @param string $newdir
	 */
	function setViewDirectory($newdir) {
		$this->_view_directory = $newdir;
	}




	function getFormat() {
		return $this->_format;
	}


	function setFormat($format) {
		$this->_format = $format;
	}

}




/**
 * A globally-available function that runs the tests and creates the result page
 *
 */
function phpsecinfo() {
	// modded this to not throw a PHP5 STRICT notice, although I don't like passing by value here
	$psi = new PhpSecInfo();
	$psi->loadAndRun();
	$psi->renderOutput();
}

