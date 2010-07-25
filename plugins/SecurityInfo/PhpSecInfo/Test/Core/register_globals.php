<?php
/**
 * Test Class for register_globals
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test_Core.php');


/**
 * Test Class for register_globals
 *
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_Register_Globals extends PhpSecInfo_Test_Core
{

	/**
	 * This should be a <b>unique</b>, human-readable identifier for this test
	 *
	 * @var string
	 */
	var $test_name = "register_globals";


	var $recommended_value = FALSE;


	function _retrieveCurrentValue() {
		$this->current_value = $this->getBooleanIniValue('register_globals');
	}


	/**
	 * register_globals has been removed since PHP 6.0
	 *
	 * @return boolean
	 */
	function isTestable() {
		return version_compare(phpversion(), '6', '<') ;
	}



	/**
	 * Checks to see if allow_url_fopen is enabled
	 *
	 */
	function _execTest() {
		if ($this->current_value == $this->recommended_value) {
			return PHPSECINFO_TEST_RESULT_OK;
		}

		return PHPSECINFO_TEST_RESULT_WARN;
	}


	/**
	 * Set the messages specific to this test
	 *
	 */
	function _setMessages() {
		parent::_setMessages();

		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTRUN, 'en', 'You are running PHP 6 or later and register_globals has been removed');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'register_globals is disabled, which is the recommended setting');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_WARN, 'en', 'register_globals is enabled.  This could be a serious security risk.  You should disable register_globals immediately');
	}


}
