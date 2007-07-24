<?php
    /**
     *	Base include file for SimpleTest
     *	@package	SimpleTest
     *	@subpackage	UnitTester
     *	@version	$Id: test_case.php,v 1.44 2007/06/09 08:38:09 pachanga Exp $
     */

    /**#@+
     * Includes SimpleTest files and defined the root constant
     * for dependent libraries.
     */
    require_once(dirname(__FILE__) . '/invoker.php');
    require_once(dirname(__FILE__) . '/errors.php');
    require_once(dirname(__FILE__) . '/compatibility.php');
    require_once(dirname(__FILE__) . '/scorer.php');
    require_once(dirname(__FILE__) . '/expectation.php');
    require_once(dirname(__FILE__) . '/dumper.php');
    require_once(dirname(__FILE__) . '/simpletest.php');
    if (version_compare(phpversion(), '5') >= 0) {
        require_once(dirname(__FILE__) . '/exceptions.php');
        require_once(dirname(__FILE__) . '/reflection_php5.php');
    } else {
        require_once(dirname(__FILE__) . '/reflection_php4.php');
    }
    if (! defined('SIMPLE_TEST')) {
        /**
         * @ignore
         */
        define('SIMPLE_TEST', dirname(__FILE__) . DIRECTORY_SEPARATOR);
    }
    /**#@-*/

    /**
     *    Basic test case. This is the smallest unit of a test
     *    suite. It searches for
     *    all methods that start with the the string "test" and
     *    runs them. Working test cases extend this class.
	 *    @package		SimpleTest
	 *    @subpackage	UnitTester
     */
    class SimpleTestCase {
        var $_label = false;
        var $_reporter;
        var $_observers;
		var $_should_skip = false;

        /**
         *    Sets up the test with no display.
         *    @param string $label    If no test name is given then
         *                            the class name is used.
         *    @access public
         */
        function SimpleTestCase($label = false) {
            if ($label) {
                $this->_label = $label;
            }
        }

        /**
         *    Accessor for the test name for subclasses.
         *    @return string           Name of the test.
         *    @access public
         */
        function getLabel() {
            return $this->_label ? $this->_label : get_class($this);
        }

        /**
         *    This is a placeholder for skipping tests. In this
         *    method you place skipIf() and skipUnless() calls to
         *    set the skipping state.
         *    @access public
         */
        function skip() {
        }

        /**
         *    Will issue a message to the reporter and tell the test
         *    case to skip if the incoming flag is true.
         *    @param string $should_skip    Condition causing the tests to be skipped.
         *    @param string $message    	Text of skip condition.
         *    @access public
         */
        function skipIf($should_skip, $message = '%s') {
			if ($should_skip && ! $this->_should_skip) {
				$this->_should_skip = true;
				$message = sprintf($message, 'Skipping [' . get_class($this) . ']');
				$this->_reporter->paintSkip($message . $this->getAssertionLine());
			}
        }

        /**
         *    Will issue a message to the reporter and tell the test
         *    case to skip if the incoming flag is false.
         *    @param string $shouldnt_skip  Condition causing the tests to be run.
         *    @param string $message    	Text of skip condition.
         *    @access public
         */
        function skipUnless($shouldnt_skip, $message = false) {
			$this->skipIf(! $shouldnt_skip, $message);
        }

        /**
         *    Used to invoke the single tests.
         *    @return SimpleInvoker        Individual test runner.
         *    @access public
         */
        function &createInvoker() {
            $invoker = &new SimpleErrorTrappingInvoker(new SimpleInvoker($this));
            if (version_compare(phpversion(), '5') >= 0) {
                $invoker = &new SimpleExceptionTrappingInvoker($invoker);
            }
            return $invoker;
        }

        /**
         *    Uses reflection to run every method within itself
         *    starting with the string "test" unless a method
         *    is specified.
         *    @param SimpleReporter $reporter    Current test reporter.
         *    @return boolean                    True if all tests passed.
         *    @access public
         */
        function run(&$reporter) {
			$context = &SimpleTest::getContext();
			$context->setTest($this);
			$context->setReporter($reporter);
            $this->_reporter = &$reporter;
            $started = false;
            foreach ($this->getTests() as $method) {
                if ($reporter->shouldInvoke($this->getLabel(), $method)) {
                    $this->skip();
                    if ($this->_should_skip) {
                        break;
                    }
                    if (! $started) {
                        $reporter->paintCaseStart($this->getLabel());
                        $started = true;
                    }
                    $invoker = &$this->_reporter->createInvoker($this->createInvoker());
                    $invoker->before($method);
                    $invoker->invoke($method);
                    $invoker->after($method);
                }
            }
            if ($started) {
                $reporter->paintCaseEnd($this->getLabel());
            }
            unset($this->_reporter);
            return $reporter->getStatus();
        }

        /**
         *    Gets a list of test names. Normally that will
         *    be all internal methods that start with the
         *    name "test". This method should be overridden
         *    if you want a different rule.
         *    @return array        List of test names.
         *    @access public
         */
        function getTests() {
            $methods = array();
            foreach (get_class_methods(get_class($this)) as $method) {
                if ($this->_isTest($method)) {
                    $methods[] = $method;
                }
            }
            return $methods;
        }

        /**
         *    Tests to see if the method is a test that should
         *    be run. Currently any method that starts with 'test'
         *    is a candidate unless it is the constructor.
         *    @param string $method        Method name to try.
         *    @return boolean              True if test method.
         *    @access protected
         */
        function _isTest($method) {
            if (strtolower(substr($method, 0, 4)) == 'test') {
                return ! SimpleTestCompatibility::isA($this, strtolower($method));
            }
            return false;
        }

        /**
         *    Announces the start of the test.
         *    @param string $method    Test method just started.
         *    @access public
         */
        function before($method) {
            $this->_reporter->paintMethodStart($method);
            $this->_observers = array();
        }

        /**
         *    Sets up unit test wide variables at the start
         *    of each test method. To be overridden in
         *    actual user test cases.
         *    @access public
         */
        function setUp() {
        }

        /**
         *    Clears the data set in the setUp() method call.
         *    To be overridden by the user in actual user test cases.
         *    @access public
         */
        function tearDown() {
        }

        /**
         *    Announces the end of the test. Includes private clean up.
         *    @param string $method    Test method just finished.
         *    @access public
         */
        function after($method) {
            for ($i = 0; $i < count($this->_observers); $i++) {
                $this->_observers[$i]->atTestEnd($method, $this);
            }
            $this->_reporter->paintMethodEnd($method);
        }

        /**
         *    Sets up an observer for the test end.
         *    @param object $observer    Must have atTestEnd()
         *                               method.
         *    @access public
         */
        function tell(&$observer) {
            $this->_observers[] = &$observer;
        }

        /**
         *    @deprecated
         */
        function pass($message = "Pass") {
            if (! isset($this->_reporter)) {
                trigger_error('Can only make assertions within test methods');
            }
            $this->_reporter->paintPass(
                    $message . $this->getAssertionLine());
            return true;
        }

        /**
         *    Sends a fail event with a message.
         *    @param string $message        Message to send.
         *    @access public
         */
        function fail($message = "Fail") {
            if (! isset($this->_reporter)) {
                trigger_error('Can only make assertions within test methods');
            }
            $this->_reporter->paintFail(
                    $message . $this->getAssertionLine());
            return false;
        }

        /**
         *    Formats a PHP error and dispatches it to the
         *    reporter.
         *    @param integer $severity  PHP error code.
         *    @param string $message    Text of error.
         *    @param string $file       File error occoured in.
         *    @param integer $line      Line number of error.
         *    @access public
         */
        function error($severity, $message, $file, $line) {
            if (! isset($this->_reporter)) {
                trigger_error('Can only make assertions within test methods');
            }
            $this->_reporter->paintError(
                    "Unexpected PHP error [$message] severity [$severity] in [$file line $line]");
        }

        /**
         *    Formats an exception and dispatches it to the
         *    reporter.
         *    @param Exception $exception    Object thrown.
         *    @access public
         */
        function exception($exception) {
            $this->_reporter->paintException($exception);
        }

        /**
         *    @deprecated
         */
        function signal($type, &$payload) {
            if (! isset($this->_reporter)) {
                trigger_error('Can only make assertions within test methods');
            }
            $this->_reporter->paintSignal($type, $payload);
        }

        /**
         *    Runs an expectation directly, for extending the
         *    tests with new expectation classes.
         *    @param SimpleExpectation $expectation  Expectation subclass.
         *    @param mixed $compare               Value to compare.
         *    @param string $message                 Message to display.
         *    @return boolean                        True on pass
         *    @access public
         */
        function assert(&$expectation, $compare, $message = '%s') {
            if ($expectation->test($compare)) {
                return $this->pass(sprintf(
                        $message,
                        $expectation->overlayMessage($compare, $this->_reporter->getDumper())));
            } else {
                return $this->fail(sprintf(
                        $message,
                        $expectation->overlayMessage($compare, $this->_reporter->getDumper())));
            }
        }

        /**
         *	  @deprecated
         */
        function assertExpectation(&$expectation, $compare, $message = '%s') {
        	return $this->assert($expectation, $compare, $message);
        }

        /**
         *    Uses a stack trace to find the line of an assertion.
         *    @return string           Line number of first assert*
         *                             method embedded in format string.
         *    @access public
         */
        function getAssertionLine() {
            $trace = new SimpleStackTrace(array('assert', 'expect', 'pass', 'fail', 'skip'));
            return $trace->traceMethod();
        }

        /**
         *    Sends a formatted dump of a variable to the
         *    test suite for those emergency debugging
         *    situations.
         *    @param mixed $variable    Variable to display.
         *    @param string $message    Message to display.
         *    @return mixed             The original variable.
         *    @access public
         */
        function dump($variable, $message = false) {
            $dumper = $this->_reporter->getDumper();
            $formatted = $dumper->dump($variable);
            if ($message) {
                $formatted = $message . "\n" . $formatted;
            }
            $this->_reporter->paintFormattedMessage($formatted);
            return $variable;
        }

        /**
         *    @deprecated
         */
        function sendMessage($message) {
            $this->_reporter->PaintMessage($message);
        }

        /**
         *    Accessor for the number of subtests.
         *    @return integer           Number of test cases.
         *    @access public
         *    @static
         */
        function getSize() {
            return 1;
        }
    }

    /**
     *  Helps to extract test cases automatically from a file.
     */
    class SimpleFileLoader {

        /**
         *    Builds a test suite from a library of test cases.
         *    The new suite is composed into this one.
         *    @param string $test_file        File name of library with
         *                                    test case classes.
         *    @return TestSuite               The new test suite.
         *    @access public
         */
        function &load($test_file) {
            $existing_classes = get_declared_classes();
            include_once($test_file);
            $classes = $this->selectRunnableTests(
                    array_diff(get_declared_classes(), $existing_classes));
            $suite = &$this->createSuiteFromClasses($test_file, $classes);
            return $suite;
        }

        /**
         *    Calculates the incoming test cases. Skips abstract
         *    and ignored classes.
         *    @param array $candidates   Candidate classes.
         *    @return array              New classes which are test
         *                               cases that shouldn't be ignored.
         *    @access public
         */
        function selectRunnableTests($candidates) {
            $classes = array();
            foreach ($candidates as $class) {
                if (TestSuite::getBaseTestCase($class)) {
                    $reflection = new SimpleReflection($class);
                    if ($reflection->isAbstract()) {
                        SimpleTest::ignore($class);
                    }
                    $classes[] = $class;
                }
            }
            return $classes;
        }

        /**
         *    Builds a test suite from a class list.
         *    @param string $title       Title of new group.
         *    @param array $classes      Test classes.
         *    @return TestSuite          Group loaded with the new
         *                               test cases.
         *    @access public
         */
        function &createSuiteFromClasses($title, $classes) {
            if (count($classes) == 0) {
                $suite = &new BadTestSuite($title, "No runnable test cases in [$title]");
                return $suite;
            }
            SimpleTest::ignoreParentsIfIgnored($classes);
            $suite = &new TestSuite($title);
            foreach ($classes as $class) {
                if (! SimpleTest::isIgnored($class)) {
                    $suite->addTestClass($class);
                }
            }
            return $suite;
        }
    }

    /**
     *    This is a composite test class for combining
     *    test cases and other RunnableTest classes into
     *    a group test.
	 *    @package		SimpleTest
	 *    @subpackage	UnitTester
     */
    class TestSuite {
        var $_label;
        var $_test_cases;

        /**
         *    Sets the name of the test suite.
         *    @param string $label    Name sent at the start and end
         *                            of the test.
         *    @access public
         */
        function TestSuite($label = false) {
            $this->_label = $label;
            $this->_test_cases = array();
        }

        /**
         *    Accessor for the test name for subclasses. If the suite
		 *    wraps a single test case the label defaults to the name of that test.
         *    @return string           Name of the test.
         *    @access public
         */
        function getLabel() {
			if (! $this->_label) {
				return ($this->getSize() == 1) ?
                        get_class($this->_test_cases[0]) : get_class($this);
			} else {
				return $this->_label;
			}
        }

        /**
         *    @deprecated
         */
        function addTestCase(&$test_case) {
            $this->_test_cases[] = &$test_case;
        }

        /**
         *    @deprecated
         */
        function addTestClass($class) {
            if (TestSuite::getBaseTestCase($class) == 'testsuite') {
                $this->_test_cases[] = &new $class();
            } else {
                $this->_test_cases[] = $class;
            }
        }

        /**
         *    Adds a test into the suite by instance or class. The class will
         *    be instantiated if it's a test suite.
         *    @param SimpleTestCase $test_case  Suite or individual test
         *                                      case implementing the
         *                                      runnable test interface.
         *    @access public
         */
        function add(&$test_case) {
            if (! is_string($test_case)) {
                $this->_test_cases[] = &$test_case;
            } elseif (TestSuite::getBaseTestCase($class) == 'testsuite') {
                $this->_test_cases[] = &new $class();
            } else {
                $this->_test_cases[] = $class;
            }
        }

        /**
         *    @deprecated
         */
        function addTestFile($test_file) {
            $this->addFile($test_file);
        }

        /**
         *    Builds a test suite from a library of test cases.
         *    The new suite is composed into this one.
         *    @param string $test_file        File name of library with
         *                                    test case classes.
         *    @access public
         */
        function addFile($test_file) {
            $extractor = new SimpleFileLoader();
            $this->add($extractor->load($test_file));
        }

        /**
         *    Delegates to a visiting collector to add test
         *    files.
         *    @param string $path                  Path to scan from.
         *    @param SimpleCollector $collector    Directory scanner.
         *    @access public
         */
        function collect($path, &$collector) {
            $collector->collect($this, $path);
        }

        /**
         *    Invokes run() on all of the held test cases, instantiating
         *    them if necessary.
         *    @param SimpleReporter $reporter    Current test reporter.
         *    @access public
         */
        function run(&$reporter) {
            $reporter->paintGroupStart($this->getLabel(), $this->getSize());
            for ($i = 0, $count = count($this->_test_cases); $i < $count; $i++) {
                if (is_string($this->_test_cases[$i])) {
                    $class = $this->_test_cases[$i];
                    $test = &new $class();
                    $test->run($reporter);
                    unset($test);
                } else {
                    $this->_test_cases[$i]->run($reporter);
                }
            }
            $reporter->paintGroupEnd($this->getLabel());
            return $reporter->getStatus();
        }

        /**
         *    Number of contained test cases.
         *    @return integer     Total count of cases in the group.
         *    @access public
         */
        function getSize() {
            $count = 0;
            foreach ($this->_test_cases as $case) {
                if (is_string($case)) {
                    $count++;
                } else {
                    $count += $case->getSize();
                }
            }
            return $count;
        }

        /**
         *    Test to see if a class is derived from the
         *    SimpleTestCase class.
         *    @param string $class     Class name.
         *    @access public
         *    @static
         */
        function getBaseTestCase($class) {
            while ($class = get_parent_class($class)) {
                $class = strtolower($class);
                if ($class == 'simpletestcase' || $class == 'testsuite') {
                    return $class;
                }
            }
            return false;
        }
    }

    /**
	 *    @package		SimpleTest
	 *    @subpackage	UnitTester
     *    @deprecated
     */
    class GroupTest extends TestSuite { }

    /**
     *    This is a failing group test for when a test suite hasn't
     *    loaded properly.
	 *    @package		SimpleTest
	 *    @subpackage	UnitTester
     */
    class BadTestSuite {
        var $_label;
        var $_error;

        /**
         *    Sets the name of the test suite and error message.
         *    @param string $label    Name sent at the start and end
         *                            of the test.
         *    @access public
         */
        function BadTestSuite($label, $error) {
            $this->_label = $label;
            $this->_error = $error;
        }

        /**
         *    Accessor for the test name for subclasses.
         *    @return string           Name of the test.
         *    @access public
         */
        function getLabel() {
            return $this->_label;
        }

        /**
         *    Sends a single error to the reporter.
         *    @param SimpleReporter $reporter    Current test reporter.
         *    @access public
         */
        function run(&$reporter) {
            $reporter->paintGroupStart($this->getLabel(), $this->getSize());
            $reporter->paintFail('Bad TestSuite [' . $this->getLabel() .
                    '] with error [' . $this->_error . ']');
            $reporter->paintGroupEnd($this->getLabel());
            return $reporter->getStatus();
        }

        /**
         *    Number of contained test cases. Always zero.
         *    @return integer     Total count of cases in the group.
         *    @access public
         */
        function getSize() {
            return 0;
        }
    }

    /**
	 *    @package		SimpleTest
	 *    @subpackage	UnitTester
     *    @deprecated
     */
    class BadGroupTest extends BadTestSuite { }
?>