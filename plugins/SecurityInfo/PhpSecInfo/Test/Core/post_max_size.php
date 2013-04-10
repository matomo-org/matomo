<?php
/**
 * Test Class for post_max_size
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR . '/Test/Test_Core.php');

/**
 * The max recommended size for the post_max_size setting, in bytes
 *
 */
define ('PHPSECINFO_POST_MAXLIMIT', 1024 * 256);

/**
 * Test Class for post_max_size
 *
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_Post_Max_Size extends PhpSecInfo_Test_Core
{

    /**
     * This should be a <b>unique</b>, human-readable identifier for this test
     *
     * @var string
     */
    var $test_name = "post_max_size";

    var $recommended_value = PHPSECINFO_POST_MAXLIMIT;

    function _retrieveCurrentValue()
    {
        $this->current_value = $this->returnBytes(ini_get('post_max_size'));
    }

    /**
     * Check to see if the post_max_size setting is enabled.
     */
    function _execTest()
    {

        if ($this->current_value
            && $this->current_value <= $this->recommended_value
            && $post_max_size != -1
        ) {
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

        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'post_max_size is enabled, and appears to
				be a relatively low value');
        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', 'post_max_size is not enabled, or is set to
				a high value.  Allowing a large value may open up your server to denial-of-service attacks');
    }


}