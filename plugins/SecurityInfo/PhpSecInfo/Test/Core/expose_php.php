<?php
/**
 * Test class for expose_php
 * 
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test_Core.php');

/**
 * Test class for expose_php
 * 
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_Expose_Php extends PhpSecInfo_Test_Core
{

	/**
	 * This should be a <b>unique</b>, human-readable identifier for this test
	 *
	 * @var string
	 */
	var $test_name = "expose_php";
	
	var $recommended_value = FALSE;
	
	function _retrieveCurrentValue() {
		$this->current_value =  $this->returnBytes(ini_get('expose_php'));
	}
					
	/**
	 * Checks to see if expose_php is enabled
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
		
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'expose_php is disabled, which is the recommended setting');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', 'expose_php is enabled.  This adds
				the PHP "signature" to the web server header, including the PHP version number.  This
				could attract attackers looking for vulnerable versions of PHP');
	}
	

}