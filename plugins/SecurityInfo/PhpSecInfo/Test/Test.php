<?php
/**
 * Skeleton Test class file
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */

/**
 * require the main PhpSecInfo class
 */
require_once(PHPSECINFO_BASE_DIR.'/PhpSecInfo.php');



define ('PHPSECINFO_TEST_RESULT_OK', -1);

define ('PHPSECINFO_TEST_RESULT_NOTICE', -2);

define ('PHPSECINFO_TEST_RESULT_WARN', -4);

define ('PHPSECINFO_TEST_RESULT_ERROR', -1024);

define ('PHPSECINFO_TEST_RESULT_NOTRUN', -2048);

define ('PHPSECINFO_TEST_COMMON_TMPDIR', '/tmp');

define ('PHPSECINFO_TEST_MOREINFO_BASEURL', 'http://phpsec.org/projects/phpsecinfo/tests/');

/**
 * This is a skeleton class for PhpSecInfo tests  You should extend this to make a "group" skeleton
 * to categorize tests under, then make a subdir with your group name that contains test classes
 * extending your group skeleton class.
 * @package PhpSecInfo
 */
class PhpSecInfo_Test
{

	/**
	 * This value is used to group test results together.
	 *
	 * For example, all tests related to the mysql lib should be grouped under "mysql."
	 *
	 * @var string
	 */
	var $test_group = 'misc';


	/**
	 * This should be a <b>unique</b>, human-readable identifier for this test
	 *
	 * @var string
	 */
	var $test_name  = 'misc_test';


	/**
	 * This is the recommended value the test will be looking for
	 *
	 * @var mixed
	 */
	var $recommended_value = "bar";


	/**
	 * The result returned from the test
	 *
	 * @var integer
	 */
	var $_result = PHPSECINFO_TEST_RESULT_NOTRUN;


	/**
	 * The message corresponding to the result of the test
	 *
	 * @var string
	 */
	var $_message;


	/**
	 * the language code.  Should be a pointer to the setting in the PhpSecInfo object
	 *
	 * @var string
	 */
	var $_language = PHPSECINFO_LANG_DEFAULT;

	/**
	 * Enter description here...
	 *
	 * @var mixed
	 */
	var $current_value;

	/**
	 * This is a hash of messages that correspond to various test result levels.
	 *
	 * There are five messages, each corresponding to one of the result constants
	 * (PHPSECINFO_TEST_RESULT_OK, PHPSECINFO_TEST_RESULT_NOTICE, PHPSECINFO_TEST_RESULT_WARN,
	 * PHPSECINFO_TEST_RESULT_ERROR, PHPSECINFO_TEST_RESULT_NOTRUN)
	 *
	 *
	 * @var array array
	 */
	var $_messages = array();




	/**
	 * Constructor for Test skeleton class
	 *
	 * @return PhpSecInfo_Test
	 */
	function PhpSecInfo_Test() {
		//$this->_setTestValues();

		$this->_retrieveCurrentValue();
		//$this->setRecommendedValue();

		$this->_setMessages();
	}


	/**
	 * Determines whether or not it's appropriate to run this test (for example, if
	 * this test is for a particular library, it shouldn't be run if the lib isn't
	 * loaded).
	 *
	 * This is a terrible name, but I couldn't think of a better one atm.
	 *
	 * @return boolean
	 */
	function isTestable() {

		return true;
	}


	/**
	 * The "meat" of the test.  This is where the real test code goes.  You should override this when extending
	 *
	 * @return integer
	 */
	function _execTest() {

		return PHPSECINFO_TEST_RESULT_NOTRUN;
	}


	/**
	 * This function loads up result messages into the $this->_messages array.
	 *
	 * Using this method rather than setting $this->_messages directly allows result
	 * messages to be inherited.  This is broken out into a separate function rather
	 * than the constructor for ease of extension purposes (php4 is whack, man).
	 *
	 */
	function _setMessages() {
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK,		'en', 'This setting should be safe');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE,	'en', 'This could potentially be a security issue');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_WARN,	'en', 'This setting may be a serious security problem');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_ERROR,	'en', 'There was an error running this test');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTRUN,	'en', 'This test cannot be run');
	}


	/**
	 * Placeholder - extend for tests
	 *
	 */
	function _retrieveCurrentValue() {
		$this->current_value = "foo";
	}



	/**
	 * This is the wrapper that executes the test and sets the result code and message
	 */
	function test() {
		$result = $this->_execTest();
		$this->_setResult($result);

	}



	/**
	 * Retrieves the result
	 *
	 * @return integer
	 */
	function getResult() {
		return $this->_result;
	}




	/**
	 * Retrieves the message for the current result
	 *
	 * @return string
	 */
	function getMessage() {
		if (!isset($this->_message) || empty($this->_message)) {
			$this->_setMessage($this->_result, $this->_language);
		}

		return $this->_message;
	}



	/**
	 * Sets the message for a given result code and language
	 *
	 * <code>
	 * $this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTRUN,	'en', 'This test cannot be run');
	 * </code>
	 *
	 * @param integer $result_code
	 * @param string $language_code
	 * @param string $message
	 *
	 */
	function setMessageForResult($result_code, $language_code, $message) {

		if ( !isset($this->_messages[$result_code]) ) {
			$this->_messages[$result_code] = array();
		}

		if ( !is_array($this->_messages[$result_code]) ) {
			$this->_messages[$result_code] = array();
		}

		$this->_messages[$result_code][$language_code] = $message;

	}




	/**
	 * returns the current value.  This function should be used to access the
	 * value for display.  All values are cast as strings
	 *
	 * @return string
	 */
	function getCurrentTestValue() {
		return $this->getStringValue($this->current_value);
	}

	/**
	 * returns the recommended value.  This function should be used to access the
	 * value for display.  All values are cast as strings
	 *
	 * @return string
	 */
	function getRecommendedTestValue() {
		return $this->getStringValue($this->recommended_value);
	}


	/**
	 * Sets the result code
	 *
	 * @param integer $result_code
	 */
	function _setResult($result_code) {
		$this->_result = $result_code;
	}


	/**
	 * Sets the $this->_message variable based on the passed result and language codes
	 *
	 * @param integer $result_code
	 * @param string $language_code
	 */
	function _setMessage($result_code, $language_code) {
		$messages = $this->_messages[$result_code];
		$message  = $messages[$language_code];
		$this->_message = $message;
	}


	/**
	 * Returns a link to a page with detailed information about the test
	 *
	 * URL is formatted as PHPSECINFO_TEST_MOREINFO_BASEURL + testName
	 *
	 * @see PHPSECINFO_TEST_MOREINFO_BASEURL
	 *
	 * @return string|boolean
	 */
	function getMoreInfoURL() {
		if ($tn = $this->getTestName()) {
			return PHPSECINFO_TEST_MOREINFO_BASEURL.strtolower("{$tn}.html");
		} else {
			return false;
		}
	}




	/**
	 * This retrieves the name of this test.
	 *
	 * If a name has not been set, this returns a formatted version of the class name.
	 *
	 * @return string
	 */
	function getTestName() {
		if (isset($this->test_name) && !empty($this->test_name)) {
			return $this->test_name;
		} else {
			return ucwords(
			str_replace('_', ' ',
			get_class($this)
			)
			);
		}

	}


	/**
	 * sets the test name
	 *
	 * @param string $test_name
	 */
	function setTestName($test_name) {
		$this->test_name = $test_name;
	}


	/**
	 * Returns the test group this test belongs to
	 *
	 * @return string
	 */
	function getTestGroup() {
		return $this->test_group;
	}


	/**
	 * sets the test group
	 *
	 * @param string $test_group
	 */
	function setTestGroup($test_group) {
		$this->test_group = $test_group;
	}


	/**
	 * This function takes the shorthand notation used in memory limit settings for PHP
	 * and returns the byte value.  Totally stolen from http://us3.php.net/manual/en/function.ini-get.php
	 *
	 * <code>
	 * echo 'post_max_size in bytes = ' . $this->return_bytes(ini_get('post_max_size'));
	 * </code>
	 *
	 * @link http://php.net/manual/en/function.ini-get.php
	 * @param string $val
	 * @return integer
	 */
	function returnBytes($val) {
		$val = trim($val);

		if ( (int)$val === 0 ) {
			return 0;
		}

		$last = strtolower($val{strlen($val)-1});
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}


	/**
	 * This just does the usual PHP string casting, except for
	 * the boolean FALSE value, where the string "0" is returned
	 * instead of an empty string
	 *
	 * @param mixed $val
	 * @return string
	 */
	function getStringValue($val) {
		if ($val === FALSE) {
			return "0";
		} else {
			return (string)$val;
		}
	}


	/**
	 * This method converts the several possible return values from
	 * allegedly "boolean" ini settings to proper booleans
	 *
	 * Properly converted input values are: 'off', 'on', 'false', 'true', '', '0', '1'
	 * (the last two might not be neccessary, but I'd rather be safe)
	 *
	 * If the ini_value doesn't match any of those, the value is returned as-is.
	 *
	 * @param string $ini_key   the ini_key you need the value of
	 * @return boolean|mixed
	 */
	function getBooleanIniValue($ini_key) {

		$ini_val = ini_get($ini_key);

		switch ( strtolower($ini_val) ) {

			case 'off':
				return false;
				break;
			case 'on':
				return true;
				break;
			case 'false':
				return false;
				break;
			case 'true':
				return true;
				break;
			case '0':
				return false;
				break;
			case '1':
				return true;
				break;
			case '':
				return false;
				break;
			default:
				return $ini_val;

		}

	}

	/**
	 * sys_get_temp_dir provides some temp dir detection capability
	 * that is lacking in versions of PHP that do not have the
	 * sys_get_temp_dir() function
	 *
	 * @return string|NULL
	 */
	function sys_get_temp_dir() {
		// Try to get from environment variable
		$vars = array('TMP', 'TMPDIR', 'TEMP');
		foreach($vars as $var) {
			$tmp = getenv($var);
			if ( !empty($tmp) ) {
				return realpath( $tmp );
			}
		}
		return NULL;
	}


	/**
	 * A quick function to determine whether we're running on Windows.
	 * Uses the PHP_OS constant.
	 *
	 * @return boolean
	 */
	function osIsWindows() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Returns an array of data returned from the UNIX 'id' command
	 *
	 * includes uid, username, gid, groupname, and groups (if "exec"
	 * is enabled). Groups is an array of all the groups the user
	 * belongs to.  Keys are the group ids, values are the group names.
	 *
	 * returns FALSE if no suitable function is available to retrieve
	 * the data
	 *
	 * @return array|boolean
	 */
	function getUnixId() {

		if ($this->osIsWindows()) {
			return false;
		}

		$success = false;


		if (function_exists("exec") && !PhpSecInfo_Test::getBooleanIniValue('safe_mode')) {
			$id_raw = exec('id');
			// uid=1000(coj) gid=1000(coj) groups=1000(coj),1001(admin)
			preg_match( "|uid=(\d+)\((\S+)\)\s+gid=(\d+)\((\S+)\)\s+groups=(.+)|i",
						$id_raw,
						$matches);

			if (!$matches) {
				/**
				 * for some reason the output from 'id' wasn't as we expected.
				 * return false so the test doesn't run.
				 */
				$success = false;
			} else {
				$id_data = array(	'uid'=>$matches[1],
									'username'=>$matches[2],
									'gid'=>$matches[3],
									'group'=>$matches[4] );

				$groups = array();
				if ($matches[5]) {
					$gs = $matches[5];
					$gs = explode(',', $gs);
					foreach ($gs as $groupstr) {
						if (preg_match("/(\d+)\(([^\)]+)\)/", $groupstr, $subs)) {
							$groups[$subs[1]] = $subs[2];
						} else {
							$groups[$groupstr] = '';
						}
					}
					ksort($groups);
				}
				$id_data['groups'] = $groups;
				$success = true;
			}

		}

		if (!$success && function_exists("posix_getpwuid") && function_exists("posix_geteuid")
			&& function_exists('posix_getgrgid') && function_exists('posix_getgroups') ) {
			$data = posix_getpwuid( posix_getuid() );
			$id_data['uid'] = $data['uid'];
			$id_data['username'] = $data['name'];
			$id_data['gid'] = $data['gid'];
			//$group_data = posix_getgrgid( posix_getegid() );
			//$id_data['group'] = $group_data['name'];
			$id_data['groups'] = array();
			$groups = posix_getgroups();
			foreach ( $groups as $gid ) {
				//$group_data = posix_getgrgid(posix_getgid());
				$id_data['groups'][$gid] = '<unknown>';
			}
			$success = true;
		}

		if ($success) {
			return $id_data;
		} else {
			return false;
		}
	}

}
