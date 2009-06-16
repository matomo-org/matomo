<?php
    /**
     *	Base include file for SimpleTest
     *	@package	SimpleTest
     *	@subpackage	UnitTester
     *	@version	$Id$
     */

    /**#@+
     * Includes SimpleTest files and defined the root constant
     * for dependent libraries.
     */
    require_once(dirname(__FILE__) . '/errors.php');
    require_once(dirname(__FILE__) . '/compatibility.php');
    require_once(dirname(__FILE__) . '/scorer.php');
    require_once(dirname(__FILE__) . '/expectation.php');
    require_once(dirname(__FILE__) . '/dumper.php');
    if (! defined('SIMPLE_TEST')) {
        define('SIMPLE_TEST', dirname(__FILE__) . '/');
    }
    /**#@-*/

    /**
     *    This is called by the class runner to run a
     *    single test method. Will also run the setUp()
     *    and tearDown() methods.
	 *	  @package SimpleTest
	 *	  @subpackage UnitTester
     */
    class SimpleInvoker {
        var $_test_case;

        /**
         *    Stashes the test case for later.
         *    @param SimpleTestCase $test_case  Test case to run.
         */
        function SimpleInvoker(&$test_case) {
            $this->_test_case = &$test_case;
        }

        /**
         *    Accessor for test case being run.
         *    @return SimpleTestCase    Test case.
         *    @access public
         */
        function &getTestCase() {
            return $this->_test_case;
        }

        /**
         *    Runs test level set up. Used for changing
         *    the mechanics of base test cases.
         *    @param string $method    Test method to call.
         *    @access public
         */
        function before($method) {
            $this->_test_case->before($method);
        }

        /**
         *    Invokes a test method and buffered with setUp()
         *    and tearDown() calls.
         *    @param string $method    Test method to call.
         *    @access public
         */
        function invoke($method) {
            $this->_test_case->setUp();
            $this->_test_case->$method();
            $this->_test_case->tearDown();
        }

        /**
         *    Runs test level clean up. Used for changing
         *    the mechanics of base test cases.
         *    @param string $method    Test method to call.
         *    @access public
         */
        function after($method) {
            $this->_test_case->after($method);
        }
    }

    /**
     *    Do nothing decorator. Just passes the invocation
     *    straight through.
	 *	  @package SimpleTest
	 *	  @subpackage UnitTester
     */
    class SimpleInvokerDecorator {
        var $_invoker;

        /**
         *    Stores the invoker to wrap.
         *    @param SimpleInvoker $invoker  Test method runner.
         */
        function SimpleInvokerDecorator(&$invoker) {
            $this->_invoker = &$invoker;
        }

        /**
         *    Accessor for test case being run.
         *    @return SimpleTestCase    Test case.
         *    @access public
         */
        function &getTestCase() {
            return $this->_invoker->getTestCase();
        }

        /**
         *    Runs test level set up. Used for changing
         *    the mechanics of base test cases.
         *    @param string $method    Test method to call.
         *    @access public
         */
        function before($method) {
            $this->_invoker->before($method);
        }

        /**
         *    Invokes a test method and buffered with setUp()
         *    and tearDown() calls.
         *    @param string $method    Test method to call.
         *    @access public
         */
        function invoke($method) {
            $this->_invoker->invoke($method);
        }

        /**
         *    Runs test level clean up. Used for changing
         *    the mechanics of base test cases.
         *    @param string $method    Test method to call.
         *    @access public
         */
        function after($method) {
            $this->_invoker->after($method);
        }
    }
?>
