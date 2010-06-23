<?php
/**
 * Skeleton Test class file for Session group
 * 
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * require the main PhpSecInfo class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test.php');



/**
 * This is a skeleton class for PhpSecInfo "Session" tests
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Session extends PhpSecInfo_Test
{
	
	/**
	 * This value is used to group test results together.
	 * 
	 * For example, all tests related to the mysql lib should be grouped under "mysql."
	 *
	 * @var string
	 */
	var $test_group = 'Session';

	
	/**
	 * "Session" tests should pretty much be always testable, so the default is
	 * just to return true
	 * 
	 * @return boolean
	 */
	function isTestable() {
		
		return true;
	}

	
}