<?php
/**
 * Skeleton Test class file for Cgi group
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */

/**
 * require the main PhpSecInfo class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test.php');



/**
 * This is a skeleton class for PhpSecInfo "CGI" tests
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Cgi extends PhpSecInfo_Test
{

	/**
	 * This value is used to group test results together.
	 *
	 * For example, all tests related to the mysql lib should be grouped under "mysql."
	 *
	 * @var string
	 */
	var $test_group = 'CGI';



	/**
	 * "CGI" tests should only be run if we're running as a CGI.  The best way I could think of
	 * to test this was to preg against the php_sapi_name() return value.
	 *
	 * @return boolean
	 */
	function isTestable() {
		/*if ( preg_match('/^cgi.*$/', PHP_SAPI) ) {
			return true;
		} else {
			return false;
		}*/
		return !strncmp(PHP_SAPI, 'cgi', 3);
	}


	/**
	 * Set the messages for CGI tests
	 *
	 */
	function _setMessages() {
		parent::_setMessages();

		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTRUN, 'en', "You don't seem to be using the CGI SAPI");

	}

}