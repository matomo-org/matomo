<?php
/**
 * Skeleton Test class file for Suhosin group
 *
 * @package PhpSecInfo
 * @author Anthon Pang
 */

/**
 * require the main PhpSecInfo class
 */
require_once(PHPSECINFO_BASE_DIR . '/Test/Test.php');


/**
 * This is a skeleton class for PhpSecInfo "Suhosin" tests
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Suhosin extends PhpSecInfo_Test
{

    /**
     * This value is used to group test results together.
     *
     * For example, all tests related to the mysql lib should be grouped under "mysql."
     *
     * @var string
     */
    var $test_group = 'Suhosin';


    /**
     * "Suhosin" tests should pretty much be always testable, so the default is just to return true
     *
     * @return boolean
     */
    function isTestable()
    {
        if (version_compare(PHP_VERSION, '5.3.9') >= 0) {
            return false;
        }
        return true;
    }

    function getMoreInfoURL()
    {
        if ($tn = $this->getTestName()) {
            return 'http://www.hardened-php.net/suhosin/index.html';
        } else {
            return false;
        }
    }
}
