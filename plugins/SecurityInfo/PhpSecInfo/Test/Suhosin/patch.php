<?php
/**
 * Test class for Suhosin patch
 *
 * @package PhpSecInfo
 * @author Piwik
 */

/**
 * require the PhpSecInfo_Test_Suhosin class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test_Suhosin.php');

/**
 * Test class for Suhosin
 *
 * Checks for Suhosin patch which implements low-level protections against bufferoverflows or format string vulnerabilities
 *
 * @package PhpSecInfo
 * @author Piwik
 */
class PhpSecInfo_Test_Suhosin_Patch extends PhpSecInfo_Test_Suhosin
{
	var $test_name = "Suhosin patch";

	var $recommended_value = true;

	function _retrieveCurrentValue() {
		if (preg_match('/Suhosin/', $_SERVER['SERVER_SOFTWARE'])) {
			$this->current_value = true;
		} else {
			$this->current_value = false;

			$directives = array(
				'suhosin.log.phpscript',
				'suhosin.log.phpscript.is_safe',
				'suhosin.log.phpscript.name',
				'suhosin.log.sapi',
				'suhosin.log.script',
				'suhosin.log.script.name',
				'suhosin.log.syslog',
				'suhosin.log.syslog.facility',
				'suhosin.log.syslog.priority',
				'suhosin.log.use-x-forwareded-for',
			);

			$ini_all = ini_get_all();

			foreach($directives as $directive) {
				if (isset($ini_all[$directive])) {
					$this->current_value = true;
					break;
				}
			}
		}
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

		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', "You are running PHP with the Suhosin patch applied against the PHP core.  This patch implements various low-level protections against (for example) buffer overflows and format string vulnerabilities.");
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', "You are not running PHP with the Suhosin patch applied. We recommend both the patch and extension for low- and high-level protections against (for example) buffer overflows and format string vulnerabilities.");
	}
}
