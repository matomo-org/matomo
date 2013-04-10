<?php
/**
 * Test Class for magic_quotes_gpc
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR . '/Test/Test_Core.php');

/**
 * Test Class for magic_quotes_gpc
 *
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_Magic_Quotes_GPC extends PhpSecInfo_Test_Core
{
    /**
     * This should be a <b>unique</b>, human-readable identifier for this test
     *
     * @var string
     */
    var $test_name = "magic_quotes_gpc";


    var $recommended_value = FALSE;


    function _retrieveCurrentValue()
    {
        $this->current_value = $this->getBooleanIniValue('magic_quotes_gpc');
    }


    /**
     * magic_quotes_gpc has been removed since PHP 6.0
     *
     * @return boolean
     */
    function isTestable()
    {
        return version_compare(PHP_VERSION, '6', '<');
    }


    /**
     * Checks to see if allow_url_fopen is enabled
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


        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTRUN, 'en', 'You are running PHP 6 or later and magic_quotes_gpc has been removed');
        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'magic_quotes_gpc is disabled, which is the recommended setting');
        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', 'magic_quotes_gpc is enabled.  This
				feature is inconsistent in blocking attacks, and can in some cases cause data loss with
				uploaded files.  You should <i>not</i> rely on magic_quotes_gpc to block attacks.  It is
				recommended that magic_quotes_gpc be disabled, and input filtering be handled by your PHP
				scripts');
    }


}