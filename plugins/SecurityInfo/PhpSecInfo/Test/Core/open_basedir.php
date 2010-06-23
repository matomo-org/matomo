<?php
/**
 * Test Class for open_basedir
 * 
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test_Core.php');

/**
 * Test Class for open_basedir
 * 
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_Open_Basedir extends PhpSecInfo_Test_Core
{

	/**
	 * This should be a <b>unique</b>, human-readable identifier for this test
	 *
	 * @var string
	 */
	var $test_name = "open_basedir";

	var $recommended_value = TRUE;

	
	function _retrieveCurrentValue() {
		$this->current_value = $this->getBooleanIniValue('open_basedir');
	}
	
	
	/**
	 * Checks to see if allow_url_fopen is enabled
	 *
	 */
	function _execTest() {
		if ($this->current_value == $this->recommended_value) {
			return PHPSECINFO_TEST_RESULT_OK;
		}

		return PHPSECINFO_TEST_RESULT_NOTICE;
	}
		
	
	/**
	 * Set the messages specific to this test
	 *
	 */
	function _setMessages() {
		parent::_setMessages();
		
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'open_basedir is enabled, which is the
				recommended setting. Keep in mind that other web applications not written in PHP will not
				be restricted by this setting.');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', 'open_basedir is disabled.  When
					this is enabled, only files that are in the
					given directory/directories and their subdirectories can be read by PHP scripts.
					You should consider turning this on.  Keep in mind that other web applications not
					written in PHP will not be restricted by this setting.');
	}
	

}