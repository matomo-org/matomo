<?php
/**
 * Test class for Piwik Application
 *
 * @package PhpSecInfo
 * @author Piwik
 */

/**
 * require the PhpSecInfo_Test_Application class
 */
require_once(PHPSECINFO_BASE_DIR . '/Test/Test_Application.php');

/**
 * Test class for Piwik application
 *
 * Checks Piwik version
 *
 * @package PhpSecInfo
 * @author Piwik
 */
class PhpSecInfo_Test_Application_Piwik extends PhpSecInfo_Test_Application
{
    var $test_name = "Piwik";

    var $recommended_value = null;

    function _retrieveCurrentValue()
    {
        $this->current_value = Piwik_Version::VERSION;

        $this->recommended_value = Piwik_GetOption(Piwik_UpdateCheck::LATEST_VERSION);
    }

    function _execTest()
    {
        if (version_compare($this->current_value, '0.5') < 0) {
            return PHPSECINFO_TEST_RESULT_WARN;
        }

        if (empty($this->recommended_value)) {
            return PHPSECINFO_TEST_RESULT_ERROR;
        }

        if (version_compare($this->current_value, $this->recommended_value) >= 0) {
            return PHPSECINFO_TEST_RESULT_OK;
        }

        return PHPSECINFO_TEST_RESULT_NOTICE;
    }

    function _setMessages()
    {
        parent::_setMessages();

        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', "You are running Piwik " . $this->current_value . " (the latest version).");
        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', "You are running Piwik " . $this->current_value . ".  The latest version of Piwik is " . $this->recommended_value . ".");
        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_WARN, 'en', "You are running Piwik " . $this->current_value . " which is no longer supported by the Piwik developers. We recommend running the latest (stable) version of Piwik which includes numerous enhancements, bug fixes, and security fixes.");
        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_ERROR, 'en', "Unable to determine the latest version of Piwik available.");
    }
}
