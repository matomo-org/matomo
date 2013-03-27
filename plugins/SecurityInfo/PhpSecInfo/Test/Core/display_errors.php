<?php
/**
 * Test class for display_errors
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR . '/Test/Test_Core.php');

/**
 * Test class for display_errors
 *
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_Display_Errors extends PhpSecInfo_Test_Core
{

    /**
     * This should be a <b>unique</b>, human-readable identifier for this test
     *
     * @var string
     */
    var $test_name = "display_errors";

    var $recommended_value = FALSE;

    function _retrieveCurrentValue()
    {
        $this->current_value = $this->getBooleanIniValue('display_errors');
    }


    /**
     * Checks to see if display_errors is enabled
     *
     */
    function _execTest()
    {
        if ($this->current_value == $this->recommended_value) {
            return PHPSECINFO_TEST_RESULT_OK;
        }

        return PHPSECINFO_TEST_RESULT_NOTICE;
    }


    /**
     * Set the messages specific to this test
     *
     */
    function _setMessages()
    {
        parent::_setMessages();

        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'display_errors is disabled, which is the recommended setting');
        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', 'display_errors is enabled.  This is not recommended on "production" servers, as it could reveal sensitive information.  You should consider disabling this feature');
    }


}