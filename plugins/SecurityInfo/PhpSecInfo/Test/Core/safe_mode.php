<?php
/**
 * Test Class for safe_mode
 *
 * @package PhpSecInfo
 * @author Piwik
 */


/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test_Core.php');


/**
 * Test Class for safe_mode
 *
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_Safe_Mode extends PhpSecInfo_Test_Core
{

	/**
	 * This should be a <b>unique</b>, human-readable identifier for this test
	 *
	 * @var string
	 */
	var $test_name = "safe_mode";


	var $recommended_value = true;


	function _retrieveCurrentValue() {
		$this->current_value = $this->getBooleanIniValue('safe_mode');
	}


	/**
	 * safe_mode has been removed since PHP 6.0
	 *
	 * @return boolean
	 */
	function isTestable() {
		return version_compare(PHP_VERSION, '6', '<') ;
	}



	/**
	 * Checks to see if safe_mode is enabled
	 *
	 */
	function _execTest() {
		if ($this->current_value == $this->recommended_value) {
			return PHPSECINFO_TEST_RESULT_OK;
		}

		return PHPSECINFO_TEST_RESULT_NOTICE;
	}


	/**
	 * Set the messages specific to this test
	 *
	 */
	function _setMessages() {
		parent::_setMessages();

		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTRUN, 'en', 'You are running PHP 6 or later and safe_mode has been removed');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'safe_mode is enabled. Your application should not depend on this configuration setting being set because it is deprecated in PHP 5 and removed in PHP 6.');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', 'safe_mode is disabled. Despite its flaws, enabling safe_mode may offer some additional protection.');
	}


}
