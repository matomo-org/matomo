<?php
/**
 * Test Class for upload_tmp_dir
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */

/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test_Core.php');

/**
 * Test Class for upload_tmp_dir
 *
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_Upload_Tmp_Dir extends PhpSecInfo_Test_Core
{

	/**
	 * This should be a <b>unique</b>, human-readable identifier for this test
	 *
	 * @var string
	 */
	var $test_name = "upload_tmp_dir";

	var $recommended_value = "A non-world readable/writable directory";

	function _retrieveCurrentValue() {
		$this->current_value =  ini_get('upload_tmp_dir');

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
	 * Check if upload_tmp_dir matches PHPSECINFO_TEST_COMMON_TMPDIR, or is word-writable
	 *
	 * This is still unix-specific, and it's possible that for now
	 * this test should be disabled under Windows builds.
	 *
	 * @see PHPSECINFO_TEST_COMMON_TMPDIR
	 */
	function _execTest() {

		$perms = @fileperms($this->current_value);
		if ($perms === false) {
			return PHPSECINFO_TEST_RESULT_WARN;
		} else if ($this->current_value
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
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'upload_tmp_dir is enabled, which is the
						recommended setting. Make sure your upload_tmp_dir path is not world-readable');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_WARN, 'en', 'unable to retrieve file permissions on upload_tmp_dir');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', 'upload_tmp_dir is disabled, or is set to a
						common world-writable directory.  This typically allows other users on this server
						to access temporary copies of files uploaded via your PHP scripts.  You should set
						upload_tmp_dir to a non-world-readable directory');
	}

}