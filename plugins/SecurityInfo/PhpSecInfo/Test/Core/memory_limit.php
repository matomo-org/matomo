<?php
/**
 * Test Class for memory_limit setting
 *
 * @package PhpSecInfo
 * @author  Paul Reinheimer
 * @author  Ed Finkler
 * @author  Mark Wallaert <mark@autumnweave.com>
 */


/**
 * require the PhpSecInfo_Test_Core class
 */
require_once(PHPSECINFO_BASE_DIR . '/Test/Test_Core.php');

/**
 * The max recommended size for the memory_limit setting, in bytes
 *
 */
define ('PHPSECINFO_MEMORY_LIMIT', 8 * 1024 * 1024);

/**
 * Test Class for memory_limit setting
 *
 * @package PhpSecInfo
 */
class PhpSecInfo_Test_Core_Memory_Limit extends PhpSecInfo_Test_Core
{


    /**
     * This should be a <b>unique</b>, human-readable identifier for this test
     *
     * @var string
     */
    var $test_name = "memory_limit";

    var $recommended_value = PHPSECINFO_MEMORY_LIMIT;

    function _retrieveCurrentValue()
    {
        $this->current_value = $this->returnBytes(ini_get('memory_limit'));
    }


    /**
     * Check to see if the memory_limit setting is enabled.
     *
     * Test conditions and results:
     * OK: memory_limit enabled and set to a value of 8MB or less.
     * NOTICE: memory_limit enabled and set to a value greater than 8MB.
     * WARNING: memory_limit disabled (compile time option).
     *
     * @return integer
     */
    function _execTest()
    {
        if (!$this->current_value) {
            return PHPSECINFO_TEST_RESULT_WARN;
        } else if ($this->returnBytes($this->current_value) <= PHPSECINFO_MEMORY_LIMIT) {
            return PHPSECINFO_TEST_RESULT_OK;
        }
        return PHPSECINFO_TEST_RESULT_NOTICE;
    }


    /**
     * Set the messages specific to this test
     *
     * @access    public
     * @return    null
     */
    function _setMessages()
    {
        parent::_setMessages();
        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_OK, 'en', 'memory_limit is enabled, and appears to be set
				to a realistic value.');
        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_NOTICE, 'en', 'memory_limit is set to a very high value. Are
				you sure your apps require this much memory? If not, lower the limit, as certain attacks or poor
				programming practices can lead to exhaustion of server resources. It is recommended that you set this
				to a realistic value (8M for example) from which it can be expanded as required.');
        $this->setMessageForResult(PHPSECINFO_TEST_RESULT_WARN, 'en', 'memory_limit does not appear to be enabled.  This
				leaves the server vulnerable to attacks that attempt to exhaust resources and creates an environment
				where poor programming practices can propagate unchecked.  This must be enabled at compile time by
				including the parameter "--enable-memory-limit" in the configure line.  Once enabled "memory_limit" may
				be set in php.ini to define the maximum amount of memory a script is allowed to allocate.');
    }


}