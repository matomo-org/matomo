<?php
/**
 * Test class for Suhosin extension
 *
 * @package PhpSecInfo
 * @author Piwik
 */

/**
 * require the PhpSecInfo_Test_Suhosin class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test_Suhosin.php');

/**
 * Test class for Suhosin extension
 *
 * Checks for suhosin extension
 *
 * @package PhpSecInfo
 * @author Piwik
 */
class PhpSecInfo_Test_Suhosin_Extension extends PhpSecInfo_Test_Suhosin
{
	var $test_name = "Suhosin extension";

	var $recommended_value = true;

	function _retrieveCurrentValue() {
		$this->current_value = extension_loaded('suhosin');
	}

	function _execTest() {
		if ( $this->current_value === true ) {
			return PHPSECINFO_TEST_RESULT_OK;
		} else {
			return PHPSECINFO_TEST_RESULT_NOTICE;
		}
	}

	function _setMessages() {
		parent::_setMessages();

		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', "You are running PHP with the Suhosin extension loaded. This extension provides high-level runtime protections, and additional filtering and logging features.");
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', "You are not running PHP with the Suhosin extension loaded. We recommend both the patch and extension for low- and high-level protections including transparent cookie encryption and remote inclusion vulnerabilities.");
	}
}
