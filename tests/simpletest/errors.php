<?php
/**
 *	base include file for SimpleTest
 *	@package	SimpleTest
 *	@subpackage	UnitTester
 *	@version	$Id: errors.php,v 1.29 2007/07/04 00:42:05 lastcraft Exp $
 */

/**
 * @ignore - PHP5 compatibility fix.
 */
if (! defined('E_STRICT')) {
	define('E_STRICT', 2048);
}

/**#@+
 * Includes SimpleTest files.
 */
require_once dirname(__FILE__) . '/invoker.php';
require_once dirname(__FILE__) . '/test_case.php';
require_once dirname(__FILE__) . '/expectation.php';
/**#@-*/

/**
 *    Extension that traps errors into an error queue.
 *	  @package SimpleTest
 *	  @subpackage UnitTester
 */
class SimpleErrorTrappingInvoker extends SimpleInvokerDecorator {

	/**
	 *    Stores the invoker to wrap.
	 *    @param SimpleInvoker $invoker  Test method runner.
	 */
	function SimpleErrorTrappingInvoker(&$invoker) {
		$this->SimpleInvokerDecorator($invoker);
	}

	/**
	 *    Invokes a test method and dispatches any
	 *    untrapped errors. Called back from
	 *    the visiting runner.
	 *    @param string $method    Test method to call.
	 *    @access public
	 */
	function invoke($method) {
		$queue = &$this->_createErrorQueue();
		set_error_handler('SimpleTestErrorHandler');
		parent::invoke($method);
		restore_error_handler();
		$queue->tally();
	}
	
	/**
	 *	  Wires up the error queue for a single test.
	 *	  @return SimpleErrorQueue    Queue connected to the test.
	 *	  @access private
	 */
	function &_createErrorQueue() {
		$context = &SimpleTest::getContext();
		$test = &$this->getTestCase();
		$queue = &$context->get('SimpleErrorQueue');
		$queue->setTestCase($test);
		return $queue;
	}
}

/**
 *    Error queue used to record trapped
 *    errors.
 *	  @package	SimpleTest
 *	  @subpackage	UnitTester
 */
class SimpleErrorQueue {
	var $_queue;
	var $_expectation_queue;
	var $_test;
	var $_using_expect_style = false;

	/**
	 *    Starts with an empty queue.
	 */
	function SimpleErrorQueue() {
		$this->clear();
	}

	/**
	 *    Discards the contents of the error queue.
	 *    @access public
	 */
	function clear() {
		$this->_queue = array();
		$this->_expectation_queue = array();
	}

	/**
	 *    Sets the currently running test case.
	 *    @param SimpleTestCase $test    Test case to send messages to.
	 *    @access public
	 */
	function setTestCase(&$test) {
		$this->_test = &$test;
	}

	/**
	 *    Sets up an expectation of an error. If this is
	 *    not fulfilled at the end of the test, a failure
	 *    will occour. If the error does happen, then this
	 *    will cancel it out and send a pass message.
	 *    @param SimpleExpectation $expected    Expected error match.
	 *    @param string $message                Message to display.
	 *    @access public
	 */
	function expectError($expected, $message) {
		$this->_using_expect_style = true;
		array_push($this->_expectation_queue, array($expected, $message));
	}

	/**
	 *    Adds an error to the front of the queue.
	 *    @param integer $severity       PHP error code.
	 *    @param string $content         Text of error.
	 *    @param string $filename        File error occoured in.
	 *    @param integer $line           Line number of error.
	 *    @access public
	 */
	function add($severity, $content, $filename, $line) {
		$content = str_replace('%', '%%', $content);
		if ($this->_using_expect_style) {
			$this->_testLatestError($severity, $content, $filename, $line);
		} else {
			array_push(
					$this->_queue,
					array($severity, $content, $filename, $line));
		}
	}
	
	/**
	 *	  Any errors still in the queue are sent to the test
	 *	  case. Any unfulfilled expectations trigger failures.
	 *	  @access public
	 */
	function tally() {
		while (list($severity, $message, $file, $line) = $this->extract()) {
			$severity = $this->getSeverityAsString($severity);
			$this->_test->error($severity, $message, $file, $line);
		}
		while (list($expected, $message) = $this->_extractExpectation()) {
			$this->_test->assert($expected, false, "%s -> Expected error not caught");
		}
	}

	/**
	 *    Tests the error against the most recent expected
	 *    error.
	 *    @param integer $severity       PHP error code.
	 *    @param string $content         Text of error.
	 *    @param string $filename        File error occoured in.
	 *    @param integer $line           Line number of error.
	 *    @access private
	 */
	function _testLatestError($severity, $content, $filename, $line) {
		if ($expectation = $this->_extractExpectation()) {
			list($expected, $message) = $expectation;
			$this->_test->assert($expected, $content, sprintf(
					$message,
					"%s -> PHP error [$content] severity [" .
							$this->getSeverityAsString($severity) .
							"] in [$filename] line [$line]"));
		} else {
			$this->_test->error($severity, $content, $filename, $line);
		}
	}

	/**
	 *    Pulls the earliest error from the queue.
	 *    @return  mixed    False if none, or a list of error
	 *                		information. Elements are: severity
	 *                		as the PHP error code, the error message,
	 *                		the file with the error, the line number
	 *               	 	and a list of PHP super global arrays.
	 *    @access public
	 */
	function extract() {
		if (count($this->_queue)) {
			return array_shift($this->_queue);
		}
		return false;
	}

	/**
	 *	  Pulls the earliest expectation from the queue.
	 *	  @return     SimpleExpectation    False if none.
	 *    @access private
	 */
	function _extractExpectation() {
		if (count($this->_expectation_queue)) {
			return array_shift($this->_expectation_queue);
		}
		return false;
	}

	/**
	 *    @deprecated
	 */
	function assertNoErrors($message) {
		return $this->_test->assert(
				new TrueExpectation(),
				count($this->_queue) == 0,
				sprintf($message, 'Should be no errors'));
	}

	/**
	 *    @deprecated
	 */
	function assertError($expected, $message) {
		if (count($this->_queue) == 0) {
			$this->_test->fail(sprintf($message, 'Expected error not found'));
			return false;
		}
		list($severity, $content, $file, $line) = $this->extract();
		$severity = $this->getSeverityAsString($severity);
		return $this->_test->assert(
				$expected,
				$content,
				sprintf($message, "Expected PHP error [$content] severity [$severity] in [$file] line [$line]"));
	}

	/**
	 *    Converts an error code into it's string
	 *    representation.
	 *    @param $severity  PHP integer error code.
	 *    @return           String version of error code.
	 *    @access public
	 *    @static
	 */
	function getSeverityAsString($severity) {
		static $map = array(
				E_STRICT => 'E_STRICT',
				E_ERROR => 'E_ERROR',
				E_WARNING => 'E_WARNING',
				E_PARSE => 'E_PARSE',
				E_NOTICE => 'E_NOTICE',
				E_CORE_ERROR => 'E_CORE_ERROR',
				E_CORE_WARNING => 'E_CORE_WARNING',
				E_COMPILE_ERROR => 'E_COMPILE_ERROR',
				E_COMPILE_WARNING => 'E_COMPILE_WARNING',
				E_USER_ERROR => 'E_USER_ERROR',
				E_USER_WARNING => 'E_USER_WARNING',
				E_USER_NOTICE => 'E_USER_NOTICE');
		if (version_compare(phpversion(), '5.2.0', '>=')) {
			$map[E_RECOVERABLE_ERROR] = 'E_RECOVERABLE_ERROR';
		}
		return $map[$severity];
	}
}

/**
 *    Error handler that simply stashes any errors into the global
 *    error queue. Simulates the existing behaviour with respect to
 *    logging errors, but this feature may be removed in future.
 *    @param $severity        PHP error code.
 *    @param $message         Text of error.
 *    @param $filename        File error occoured in.
 *    @param $line            Line number of error.
 *    @param $super_globals   Hash of PHP super global arrays.
 *    @static
 *    @access public
 */
function SimpleTestErrorHandler($severity, $message, $filename = null, $line = null, $super_globals = null, $mask = null) {
    $severity = $severity & error_reporting();
	if ($severity) {
		restore_error_handler();
		if (ini_get('log_errors')) {
			$label = SimpleErrorQueue::getSeverityAsString($severity);
			error_log("$label: $message in $filename on line $line");
		}
		$context = &SimpleTest::getContext();
		$queue = &$context->get('SimpleErrorQueue');
		$queue->add($severity, $message, $filename, $line);
		set_error_handler('SimpleTestErrorHandler');
	}
	return true;
}
?>