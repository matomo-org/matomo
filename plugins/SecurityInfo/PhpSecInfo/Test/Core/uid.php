<?php
/**
 * Test class for UID
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR.'/Test/Test_Core.php');


/**
 * the minimum "safe" UID that php should be executing as.  This can vary,
 * but in general 100 seems like a good min.
 *
 */
define ('PHPSECINFO_MIN_SAFE_UID', 100);

/**
 * Test class for UID
 *
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_Uid extends PhpSecInfo_Test_Core
{

	/**
	 * This should be a <b>unique</b>, human-readable identifier for this test
	 *
	 * @var string
	 */
	var $test_name = "user_id";

	var $recommended_value = PHPSECINFO_MIN_SAFE_UID;

	/**
	 * This test only works under Unix OSes
	 *
	 * @return boolean
	 */
	function isTestable() {
		if ($this->osIsWindows()) {
			return false;
		} elseif ($this->getUnixId() === false) {
		    $this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTRUN, 'en', 'Functions required to retrieve user ID not available');
		    return false;
		}
		return true;
	}


	function _retrieveCurrentValue() {
		$id = $this->getUnixId();
        if (is_array($id)) {
            $this->current_value = $id['uid'];
        } else {
            $this->current_value = false;
        }

	}

	/**
	 * Checks the GID of the PHP process to make sure it is above PHPSECINFO_MIN_SAFE_UID
	 *
	 * @see PHPSECINFO_MIN_SAFE_UID
	 */
	function _execTest() {
		if ($this->current_value >= $this->recommended_value) {
			return PHPSECINFO_TEST_RESULT_OK;
		}

		return PHPSECINFO_TEST_RESULT_WARN;
	}


	/**
	 * Set the messages specific to this test
	 *
	 */
	function _setMessages() {
		parent::_setMessages();

		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'PHP is executing as what is probably a non-privileged user');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_WARN, 'en', 'PHP may be executing as a "privileged" user, which could be a serious security vulnerability.');
		$this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTRUN, 'en', 'This test will not run on Windows OSes');
	}


}