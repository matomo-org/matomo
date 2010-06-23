<?php
/**
 * Test Class for file_uploads
 * 
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test_Core.php');

/**
 * Test Class for file_uploads
 * 
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_File_Uploads extends PhpSecInfo_Test_Core
{

	/**
	 * This should be a <b>unique</b>, human-readable identifier for this test
	 *
	 * @var string
	 */
	var $test_name = "file_uploads";

	var $recommended_value = FALSE;
	
	function _retrieveCurrentValue() {
		$this->current_value =  $this->returnBytes(ini_get('file_uploads'));
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
		
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'file_uploads are disabled.  Unless you\'re sure you need them, this is the recommended setting');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', 'file_uploads are enabled.  If you do not require file upload capability, consider disabling them.');
	}
	

}