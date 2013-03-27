<?php
/**
 * Test class for cgi force_redirect
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */

/**
 * require the PhpSecInfo_Test_Cgi class
 */
require_once(PHPSECINFO_BASE_DIR . '/Test/Test_Cgi.php');

/**
 * Test class for cgi force_redirect
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */
class PhpSecInfo_Test_Cgi_Force_Redirect extends PhpSecInfo_Test_Cgi
{

    /**
     * This should be a <b>unique</b>, human-readable identifier for this test
     *
     * @var string
     */
    var $test_name = "force_redirect";

    /**
     * The recommended setting value
     *
     * @var mixed
     */
    var $recommended_value = TRUE;


    function _retrieveCurrentValue()
    {
        $this->current_value = $this->getBooleanIniValue('cgi.force_redirect');
    }


    private function skipTest()
    {
        if (strpos(PHP_SAPI, 'cgi') === false) {
            return PHP_SAPI . ' SAPI for php';
        }

        // these web servers require cgi.force_redirect = 0
        $webServers = array('Microsoft-IIS', 'OmniHTTPd', 'Xitami');
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            foreach ($webServers as $webServer) {
                if (strpos($_SERVER['SERVER_SOFTWARE'], $webServer) === 0) {
                    return $_SERVER['SERVER_SOFTWARE'];
                }
            }
        }

        return false;
    }


    /**
     * Checks to see if cgi.force_redirect is enabled
     *
     */
    function _execTest()
    {
        if ($this->current_value == $this->recommended_value) {
            return PHPSECINFO_TEST_RESULT_OK;
        }

        if ($this->skipTest()) {
            return PHPSECINFO_TEST_RESULT_NOTICE;
        }

        return PHPSECINFO_TEST_RESULT_WARN;
    }


    /**
     * Set the messages specific to this test
     *
     */
    function _setMessages()
    {
        parent::_setMessages();

        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', "force_redirect is enabled, which is the recommended setting");
        $ini = ini_get_all();
        if (isset($ini['cgi.force_redirect'])) {
            $this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', "force_redirect is disabled.  In most cases, this is a security vulnerability, but it appears this is not needed because you are running " . $this->skipTest());
            $this->setMessageForResult(PHPSECINFO_TEST_RESULT_WARN, 'en', "force_redirect is disabled.  In most cases, this is a <strong>serious</strong> security vulnerability.  Unless you are absolutely sure this is not needed, enable this setting");
        } else {
            $this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', "force_redirect is disabled because php was not compiled with --enable-force-cgi-redirect.  In most cases, this is a security vulnerability, but it appears this is not needed because you are running " . $this->skipTest());
            $this->setMessageForResult(PHPSECINFO_TEST_RESULT_WARN, 'en', "force_redirect is disabled because php was not compiled with --enable-force-cgi-redirect.  In most cases, this is a <strong>serious</strong> security vulnerability.  Unless you are absolutely sure this is not needed, recompile php with --enable-force-cgi-redirect and enable cgi.force_redirect");
        }
    }
}
