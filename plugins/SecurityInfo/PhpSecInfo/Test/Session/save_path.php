<?php
/**
 * Test class for session save_path
 *
 * @package PhpSecInfo
 * @author Thomas CORBIERE <thomas@votre-grandeur-celeste.com>
 */

/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test_Session.php');

/**
 * Test class for session save_path
 *
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Session_Save_Path extends PhpSecInfo_Test_Session
{

	/**
	 * This should be a <b>unique</b>, human-readable identifier for this test
	 *
	 * @var string
	 */
	var $test_name = "save_path";

	var $recommended_value = "A non-world readable/writable directory";

	function _retrieveCurrentValue() {
		$this->current_value = ini_get('session.save_path');

		if( empty($this->current_value) ) {
			if (function_exists("sys_get_temp_dir")) {
		    	$this->current_value = sys_get_temp_dir();
			} else {
				$this->current_value = $this->sys_get_temp_dir();
			}
		}
	}


	/**
	 * We are disabling this function on Windows OSes right now until
	 * we can be certain of the proper way to check world-readability
	 *
	 * @return unknown
	 */
	function isTestable() {
		if ($this->osIsWindows()) {
			return FALSE;
		} else {
			return TRUE;
		}
	}


	/**
	 * Check if session.save_path matches PHPSECINFO_TEST_COMMON_TMPDIR, or is word-writable
	 *
	 * This is still unix-specific, and it's possible that for now
	 * this test should be disabled under Windows builds.
	 *
	 * @see PHPSECINFO_TEST_COMMON_TMPDIR
	 */
	function _execTest() {

		$perms = fileperms($this->current_value);

		if ($this->current_value
			&& !preg_match("|".PHPSECINFO_TEST_COMMON_TMPDIR."/?|", $this->current_value)
			&& ! ($perms & 0x0004)
			&& ! ($perms & 0x0002) ) {
			return PHPSECINFO_TEST_RESULT_OK;
		}

		// rewrite current_value to display perms
		$this->current_value .= " (".substr(sprintf('%o', $perms), -4).")";

		return PHPSECINFO_TEST_RESULT_NOTICE;
	}

	/**
	 * Set the messages specific to this test
	 *
	 */
	function _setMessages() {
		parent::_setMessages();

		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTRUN, 'en', 'Test not run -- currently disabled on Windows OSes');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'save_path is enabled, which is the
						recommended setting. Make sure your save_path path is not world-readable');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', 'save_path is disabled, or is set to a
						common world-writable directory.  This typically allows other users on this server
						to access session files. You should set	save_path to a non-world-readable directory');
	}

}
