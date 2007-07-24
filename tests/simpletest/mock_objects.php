<?php
/**
 *	base include file for SimpleTest
 *	@package	SimpleTest
 *	@subpackage	MockObjects
 *	@version	$Id: mock_objects.php,v 1.108 2007/07/07 00:31:03 lastcraft Exp $
 */

/**#@+
 * include SimpleTest files
 */
require_once(dirname(__FILE__) . '/expectation.php');
require_once(dirname(__FILE__) . '/simpletest.php');
require_once(dirname(__FILE__) . '/dumper.php');
if (version_compare(phpversion(), '5') >= 0) {
    require_once(dirname(__FILE__) . '/reflection_php5.php');
} else {
    require_once(dirname(__FILE__) . '/reflection_php4.php');
}
/**#@-*/

/**
 * Default character simpletest will substitute for any value
 */
if (! defined('MOCK_ANYTHING')) {
    define('MOCK_ANYTHING', '*');
}

/**
 *    Parameter comparison assertion.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class ParametersExpectation extends SimpleExpectation {
    var $_expected;

    /**
     *    Sets the expected parameter list.
     *    @param array $parameters  Array of parameters including
     *                              those that are wildcarded.
     *                              If the value is not an array
     *                              then it is considered to match any.
     *    @param string $message    Customised message on failure.
     *    @access public
     */
    function ParametersExpectation($expected = false, $message = '%s') {
        $this->SimpleExpectation($message);
        $this->_expected = $expected;
    }

    /**
     *    Tests the assertion. True if correct.
     *    @param array $parameters     Comparison values.
     *    @return boolean              True if correct.
     *    @access public
     */
    function test($parameters) {
        if (! is_array($this->_expected)) {
            return true;
        }
        if (count($this->_expected) != count($parameters)) {
            return false;
        }
        for ($i = 0; $i < count($this->_expected); $i++) {
            if (! $this->_testParameter($parameters[$i], $this->_expected[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
     *    Tests an individual parameter.
     *    @param mixed $parameter    Value to test.
     *    @param mixed $expected     Comparison value.
     *    @return boolean            True if expectation
     *                               fulfilled.
     *    @access private
     */
    function _testParameter($parameter, $expected) {
        $comparison = $this->_coerceToExpectation($expected);
        return $comparison->test($parameter);
    }

    /**
     *    Returns a human readable test message.
     *    @param array $comparison   Incoming parameter list.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($parameters) {
        if ($this->test($parameters)) {
            return "Expectation of " . count($this->_expected) .
                    " arguments of [" . $this->_renderArguments($this->_expected) .
                    "] is correct";
        } else {
            return $this->_describeDifference($this->_expected, $parameters);
        }
    }

    /**
     *    Message to display if expectation differs from
     *    the parameters actually received.
     *    @param array $expected      Expected parameters as list.
     *    @param array $parameters    Actual parameters received.
     *    @return string              Description of difference.
     *    @access private
     */
    function _describeDifference($expected, $parameters) {
        if (count($expected) != count($parameters)) {
            return "Expected " . count($expected) .
                    " arguments of [" . $this->_renderArguments($expected) .
                    "] but got " . count($parameters) .
                    " arguments of [" . $this->_renderArguments($parameters) . "]";
        }
        $messages = array();
        for ($i = 0; $i < count($expected); $i++) {
            $comparison = $this->_coerceToExpectation($expected[$i]);
            if (! $comparison->test($parameters[$i])) {
                $messages[] = "parameter " . ($i + 1) . " with [" .
                        $comparison->overlayMessage($parameters[$i], $this->_getDumper()) . "]";
            }
        }
        return "Parameter expectation differs at " . implode(" and ", $messages);
    }

    /**
     *    Creates an identical expectation if the
     *    object/value is not already some type
     *    of expectation.
     *    @param mixed $expected      Expected value.
     *    @return SimpleExpectation   Expectation object.
     *    @access private
     */
    function _coerceToExpectation($expected) {
        if (SimpleExpectation::isExpectation($expected)) {
            return $expected;
        }
        return new IdenticalExpectation($expected);
    }

    /**
     *    Renders the argument list as a string for
     *    messages.
     *    @param array $args    Incoming arguments.
     *    @return string        Simple description of type and value.
     *    @access private
     */
    function _renderArguments($args) {
        $descriptions = array();
        if (is_array($args)) {
            foreach ($args as $arg) {
                $dumper = &new SimpleDumper();
                $descriptions[] = $dumper->describeValue($arg);
            }
        }
        return implode(', ', $descriptions);
    }
}

/**
 *    Confirms that the number of calls on a method is as expected.
 *	@package	SimpleTest
 *	@subpackage	MockObjects
 */
class CallCountExpectation extends SimpleExpectation {
    var $_method;
    var $_count;

    /**
     *    Stashes the method and expected count for later
     *    reporting.
     *    @param string $method    Name of method to confirm against.
     *    @param integer $count    Expected number of calls.
     *    @param string $message   Custom error message.
     */
    function CallCountExpectation($method, $count, $message = '%s') {
        $this->_method = $method;
        $this->_count = $count;
        $this->SimpleExpectation($message);
    }

    /**
     *    Tests the assertion. True if correct.
     *    @param integer $compare     Measured call count.
     *    @return boolean             True if expected.
     *    @access public
     */
    function test($compare) {
        return ($this->_count == $compare);
    }

    /**
     *    Reports the comparison.
     *    @param integer $compare     Measured call count.
     *    @return string              Message to show.
     *    @access public
     */
    function testMessage($compare) {
        return 'Expected call count for [' . $this->_method .
                '] was [' . $this->_count .
                '] got [' . $compare . ']';
    }
}

/**
 *    Confirms that the number of calls on a method is as expected.
 *	@package	SimpleTest
 *	@subpackage	MockObjects
 */
class MinimumCallCountExpectation extends SimpleExpectation {
    var $_method;
    var $_count;

    /**
     *    Stashes the method and expected count for later
     *    reporting.
     *    @param string $method    Name of method to confirm against.
     *    @param integer $count    Minimum number of calls.
     *    @param string $message   Custom error message.
     */
    function MinimumCallCountExpectation($method, $count, $message = '%s') {
        $this->_method = $method;
        $this->_count = $count;
        $this->SimpleExpectation($message);
    }

    /**
     *    Tests the assertion. True if correct.
     *    @param integer $compare     Measured call count.
     *    @return boolean             True if enough.
     *    @access public
     */
    function test($compare) {
        return ($this->_count <= $compare);
    }

    /**
     *    Reports the comparison.
     *    @param integer $compare     Measured call count.
     *    @return string              Message to show.
     *    @access public
     */
    function testMessage($compare) {
        return 'Minimum call count for [' . $this->_method .
                '] was [' . $this->_count .
                '] got [' . $compare . ']';
    }
}

/**
 *    Confirms that the number of calls on a method is as expected.
 *	@package	SimpleTest
 *	@subpackage	MockObjects
 */
class MaximumCallCountExpectation extends SimpleExpectation {
    var $_method;
    var $_count;

    /**
     *    Stashes the method and expected count for later
     *    reporting.
     *    @param string $method    Name of method to confirm against.
     *    @param integer $count    Minimum number of calls.
     *    @param string $message   Custom error message.
     */
    function MaximumCallCountExpectation($method, $count, $message = '%s') {
        $this->_method = $method;
        $this->_count = $count;
        $this->SimpleExpectation($message);
    }

    /**
     *    Tests the assertion. True if correct.
     *    @param integer $compare     Measured call count.
     *    @return boolean             True if not over.
     *    @access public
     */
    function test($compare) {
        return ($this->_count >= $compare);
    }

    /**
     *    Reports the comparison.
     *    @param integer $compare     Measured call count.
     *    @return string              Message to show.
     *    @access public
     */
    function testMessage($compare) {
        return 'Maximum call count for [' . $this->_method .
                '] was [' . $this->_count .
                '] got [' . $compare . ']';
    }
}

/**
 *    Retrieves values and references by searching the
 *    parameter lists until a match is found.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class CallMap {
    var $_map;

    /**
     *    Creates an empty call map.
     *    @access public
     */
    function CallMap() {
        $this->_map = array();
    }

    /**
     *    Stashes a value against a method call.
     *    @param array $parameters    Arguments including wildcards.
     *    @param mixed $value         Value copied into the map.
     *    @access public
     */
    function addValue($parameters, $value) {
        $this->addReference($parameters, $value);
    }

    /**
     *    Stashes a reference against a method call.
     *    @param array $parameters    Array of arguments (including wildcards).
     *    @param mixed $reference     Array reference placed in the map.
     *    @access public
     */
    function addReference($parameters, &$reference) {
        $place = count($this->_map);
        $this->_map[$place] = array();
        $this->_map[$place]["params"] = new ParametersExpectation($parameters);
        $this->_map[$place]["content"] = &$reference;
    }

    /**
     *    Searches the call list for a matching parameter
     *    set. Returned by reference.
     *    @param array $parameters    Parameters to search by
     *                                without wildcards.
     *    @return object              Object held in the first matching
     *                                slot, otherwise null.
     *    @access public
     */
    function &findFirstMatch($parameters) {
        $slot = $this->_findFirstSlot($parameters);
        if (!isset($slot)) {
            $null = null;
            return $null;
        }
        return $slot["content"];
    }

    /**
     *    Searches the call list for a matching parameter
     *    set. True if successful.
     *    @param array $parameters    Parameters to search by
     *                                without wildcards.
     *    @return boolean             True if a match is present.
     *    @access public
     */
    function isMatch($parameters) {
        return ($this->_findFirstSlot($parameters) != null);
    }

    /**
     *    Searches the map for a matching item.
     *    @param array $parameters    Parameters to search by
     *                                without wildcards.
     *    @return array               Reference to slot or null.
     *    @access private
     */
    function &_findFirstSlot($parameters) {
        $count = count($this->_map);
        for ($i = 0; $i < $count; $i++) {
            if ($this->_map[$i]["params"]->test($parameters)) {
                return $this->_map[$i];
            }
        }
        $null = null;
        return $null;
    }
}

/**
 *    An empty collection of methods that can have their
 *    return values set and expectations made of the
 *    calls upon them. The mock will assert the
 *    expectations against it's attached test case in
 *    addition to the server stub behaviour.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class SimpleMock {
    var $_wildcard = MOCK_ANYTHING;
    var $_is_strict = true;
    var $_returns;
    var $_return_sequence;
    var $_call_counts;
    var $_expected_counts;
    var $_max_counts;
    var $_expected_args;
    var $_expected_args_at;

    /**
     *    Creates an empty return list and expectation list.
     *    All call counts are set to zero.
     */
    function SimpleMock() {
        $this->_returns = array();
        $this->_return_sequence = array();
        $this->_call_counts = array();
        $this->_expected_counts = array();
        $this->_max_counts = array();
        $this->_expected_args = array();
        $this->_expected_args_at = array();
        $test = &$this->_getCurrentTestCase();
        $test->tell($this);
    }
    
    /**
     *    Disables a name check when setting expectations.
     *    This hack is needed for the partial mocks.
     *    @access public
     */
    function disableExpectationNameChecks() {
        $this->_is_strict = false;
    }

    /**
     *    Finds currently running test.
     *    @return SimpeTestCase    Current test case.
     *    @access protected
     */
    function &_getCurrentTestCase() {
        $context = &SimpleTest::getContext();
        return $context->getTest();
    }

    /**
     *    Die if bad arguments array is passed
     *    @param mixed $args     The arguments value to be checked.
     *    @param string $task    Description of task attempt.
     *    @return boolean        Valid arguments
     *    @access private
     */
    function _checkArgumentsIsArray($args, $task) {
        if (! is_array($args)) {
            trigger_error(
                "Cannot $task as \$args parameter is not an array",
                E_USER_ERROR);
        }
    }

    /**
     *    Triggers a PHP error if the method is not part
     *    of this object.
     *    @param string $method        Name of method.
     *    @param string $task          Description of task attempt.
     *    @access protected
     */
    function _dieOnNoMethod($method, $task) {
        if ($this->_is_strict && ! method_exists($this, $method)) {
            trigger_error(
                    "Cannot $task as no ${method}() in class " . get_class($this),
                    E_USER_ERROR);
        }
    }

    /**
     *    Replaces wildcard matches with wildcard
     *    expectations in the argument list.
     *    @param array $args      Raw argument list.
     *    @return array           Argument list with
     *                            expectations.
     *    @access private
     */
    function _replaceWildcards($args) {
        if ($args === false) {
            return false;
        }
        for ($i = 0; $i < count($args); $i++) {
            if ($args[$i] === $this->_wildcard) {
                $args[$i] = new AnythingExpectation();
            }
        }
        return $args;
    }

    /**
     *    Adds one to the call count of a method.
     *    @param string $method        Method called.
     *    @param array $args           Arguments as an array.
     *    @access protected
     */
    function _addCall($method, $args) {
        if (!isset($this->_call_counts[$method])) {
            $this->_call_counts[$method] = 0;
        }
        $this->_call_counts[$method]++;
    }

    /**
     *    Fetches the call count of a method so far.
     *    @param string $method        Method name called.
     *    @return                      Number of calls so far.
     *    @access public
     */
    function getCallCount($method) {
        $this->_dieOnNoMethod($method, "get call count");
        $method = strtolower($method);
        if (! isset($this->_call_counts[$method])) {
            return 0;
        }
        return $this->_call_counts[$method];
    }

    /**
     *    Sets a return for a parameter list that will
     *    be passed by value for all calls to this method.
     *    @param string $method       Method name.
     *    @param mixed $value         Result of call passed by value.
     *    @param array $args          List of parameters to match
     *                                including wildcards.
     *    @access public
     */
    function setReturnValue($method, $value, $args = false) {
        $this->_dieOnNoMethod($method, "set return value");
        $args = $this->_replaceWildcards($args);
        $method = strtolower($method);
        if (! isset($this->_returns[$method])) {
            $this->_returns[$method] = new CallMap();
        }
        $this->_returns[$method]->addValue($args, $value);
    }

    /**
     *    Sets a return for a parameter list that will
     *    be passed by value only when the required call count
     *    is reached.
     *    @param integer $timing   Number of calls in the future
     *                             to which the result applies. If
     *                             not set then all calls will return
     *                             the value.
     *    @param string $method    Method name.
     *    @param mixed $value      Result of call passed by value.
     *    @param array $args       List of parameters to match
     *                             including wildcards.
     *    @access public
     */
    function setReturnValueAt($timing, $method, $value, $args = false) {
        $this->_dieOnNoMethod($method, "set return value sequence");
        $args = $this->_replaceWildcards($args);
        $method = strtolower($method);
        if (! isset($this->_return_sequence[$method])) {
            $this->_return_sequence[$method] = array();
        }
        if (! isset($this->_return_sequence[$method][$timing])) {
            $this->_return_sequence[$method][$timing] = new CallMap();
        }
        $this->_return_sequence[$method][$timing]->addValue($args, $value);
    }

    /**
     *    Sets a return for a parameter list that will
     *    be passed by reference for all calls.
     *    @param string $method       Method name.
     *    @param mixed $reference     Result of the call will be this object.
     *    @param array $args          List of parameters to match
     *                                including wildcards.
     *    @access public
     */
    function setReturnReference($method, &$reference, $args = false) {
        $this->_dieOnNoMethod($method, "set return reference");
        $args = $this->_replaceWildcards($args);
        $method = strtolower($method);
        if (! isset($this->_returns[$method])) {
            $this->_returns[$method] = new CallMap();
        }
        $this->_returns[$method]->addReference($args, $reference);
    }

    /**
     *    Sets a return for a parameter list that will
     *    be passed by value only when the required call count
     *    is reached.
     *    @param integer $timing    Number of calls in the future
     *                              to which the result applies. If
     *                              not set then all calls will return
     *                              the value.
     *    @param string $method     Method name.
     *    @param mixed $reference   Result of the call will be this object.
     *    @param array $args        List of parameters to match
     *                              including wildcards.
     *    @access public
     */
    function setReturnReferenceAt($timing, $method, &$reference, $args = false) {
        $this->_dieOnNoMethod($method, "set return reference sequence");
        $args = $this->_replaceWildcards($args);
        $method = strtolower($method);
        if (! isset($this->_return_sequence[$method])) {
            $this->_return_sequence[$method] = array();
        }
        if (! isset($this->_return_sequence[$method][$timing])) {
            $this->_return_sequence[$method][$timing] = new CallMap();
        }
        $this->_return_sequence[$method][$timing]->addReference($args, $reference);
    }

    /**
     *    Sets up an expected call with a set of
     *    expected parameters in that call. All
     *    calls will be compared to these expectations
     *    regardless of when the call is made.
     *    @param string $method        Method call to test.
     *    @param array $args           Expected parameters for the call
     *                                 including wildcards.
     *    @param string $message       Overridden message.
     *    @access public
     */
    function expect($method, $args, $message = '%s') {
        $this->_dieOnNoMethod($method, 'set expected arguments');
        $this->_checkArgumentsIsArray($args, 'set expected arguments');
        $args = $this->_replaceWildcards($args);
        $message .= Mock::getExpectationLine();
        $this->_expected_args[strtolower($method)] =
                new ParametersExpectation($args, $message);
    }

    /**
     *    @deprecated
     */
    function expectArguments($method, $args, $message = '%s') {
        return $this->expect($method, $args, $message);
    }

    /**
     *    Sets up an expected call with a set of
     *    expected parameters in that call. The
     *    expected call count will be adjusted if it
     *    is set too low to reach this call.
     *    @param integer $timing    Number of calls in the future at
     *                              which to test. Next call is 0.
     *    @param string $method     Method call to test.
     *    @param array $args        Expected parameters for the call
     *                              including wildcards.
     *    @param string $message    Overridden message.
     *    @access public
     */
    function expectAt($timing, $method, $args, $message = '%s') {
        $this->_dieOnNoMethod($method, 'set expected arguments at time');
        $this->_checkArgumentsIsArray($args, 'set expected arguments at time');
        $args = $this->_replaceWildcards($args);
        if (! isset($this->_expected_args_at[$timing])) {
            $this->_expected_args_at[$timing] = array();
        }
        $method = strtolower($method);
        $message .= Mock::getExpectationLine();
        $this->_expected_args_at[$timing][$method] =
                new ParametersExpectation($args, $message);
    }

    /**
     *    @deprecated
     */
    function expectArgumentsAt($timing, $method, $args, $message = '%s') {
        return $this->expectAt($timing, $method, $args, $message);
    }

    /**
     *    Sets an expectation for the number of times
     *    a method will be called. The tally method
     *    is used to check this.
     *    @param string $method        Method call to test.
     *    @param integer $count        Number of times it should
     *                                 have been called at tally.
     *    @param string $message       Overridden message.
     *    @access public
     */
    function expectCallCount($method, $count, $message = '%s') {
        $this->_dieOnNoMethod($method, 'set expected call count');
        $message .= Mock::getExpectationLine();
        $this->_expected_counts[strtolower($method)] =
                new CallCountExpectation($method, $count, $message);
    }

    /**
     *    Sets the number of times a method may be called
     *    before a test failure is triggered.
     *    @param string $method        Method call to test.
     *    @param integer $count        Most number of times it should
     *                                 have been called.
     *    @param string $message       Overridden message.
     *    @access public
     */
    function expectMaximumCallCount($method, $count, $message = '%s') {
        $this->_dieOnNoMethod($method, 'set maximum call count');
        $message .= Mock::getExpectationLine();
        $this->_max_counts[strtolower($method)] =
                new MaximumCallCountExpectation($method, $count, $message);
    }

    /**
     *    Sets the number of times to call a method to prevent
     *    a failure on the tally.
     *    @param string $method      Method call to test.
     *    @param integer $count      Least number of times it should
     *                               have been called.
     *    @param string $message     Overridden message.
     *    @access public
     */
    function expectMinimumCallCount($method, $count, $message = '%s') {
        $this->_dieOnNoMethod($method, 'set minimum call count');
        $message .= Mock::getExpectationLine();
        $this->_expected_counts[strtolower($method)] =
                new MinimumCallCountExpectation($method, $count, $message);
    }

    /**
     *    Convenience method for barring a method
     *    call.
     *    @param string $method        Method call to ban.
     *    @param string $message       Overridden message.
     *    @access public
     */
    function expectNever($method, $message = '%s') {
        $this->expectMaximumCallCount($method, 0, $message);
    }

    /**
     *    Convenience method for a single method
     *    call.
     *    @param string $method     Method call to track.
     *    @param array $args        Expected argument list or
     *                              false for any arguments.
     *    @param string $message    Overridden message.
     *    @access public
     */
    function expectOnce($method, $args = false, $message = '%s') {
        $this->expectCallCount($method, 1, $message);
        if ($args !== false) {
            $this->expect($method, $args, $message);
        }
    }

    /**
     *    Convenience method for requiring a method
     *    call.
     *    @param string $method       Method call to track.
     *    @param array $args          Expected argument list or
     *                                false for any arguments.
     *    @param string $message      Overridden message.
     *    @access public
     */
    function expectAtLeastOnce($method, $args = false, $message = '%s') {
        $this->expectMinimumCallCount($method, 1, $message);
        if ($args !== false) {
            $this->expect($method, $args, $message);
        }
    }

    /**
     *    @deprecated
     */
    function tally() {
    }

    /**
     *    Receives event from unit test that the current
     *    test method has finished. Totals up the call
     *    counts and triggers a test assertion if a test
     *    is present for expected call counts.
     *    @param string $test_method      Current method name.
     *    @param SimpleTestCase $test     Test to send message to.
     *    @access public
     */
    function atTestEnd($test_method, &$test) {
        foreach ($this->_expected_counts as $method => $expectation) {
            $test->assert($expectation, $this->getCallCount($method));
        }
        foreach ($this->_max_counts as $method => $expectation) {
            if ($expectation->test($this->getCallCount($method))) {
                $test->assert($expectation, $this->getCallCount($method));
            }
        }
    }

    /**
     *    Returns the expected value for the method name
     *    and checks expectations. Will generate any
     *    test assertions as a result of expectations
     *    if there is a test present.
     *    @param string $method       Name of method to simulate.
     *    @param array $args          Arguments as an array.
     *    @return mixed               Stored return.
     *    @access private
     */
    function &_invoke($method, $args) {
        $method = strtolower($method);
        $step = $this->getCallCount($method);
        $this->_addCall($method, $args);
        $this->_checkExpectations($method, $args, $step);
        $result = &$this->_getReturn($method, $args, $step);
        return $result;
    }
    /**
     *    Finds the return value matching the incoming
     *    arguments. If there is no matching value found
     *    then an error is triggered.
     *    @param string $method      Method name.
     *    @param array $args         Calling arguments.
     *    @param integer $step       Current position in the
     *                               call history.
     *    @return mixed              Stored return.
     *    @access protected
     */
    function &_getReturn($method, $args, $step) {
        if (isset($this->_return_sequence[$method][$step])) {
            if ($this->_return_sequence[$method][$step]->isMatch($args)) {
                $result = &$this->_return_sequence[$method][$step]->findFirstMatch($args);
                return $result;
            }
        }
        if (isset($this->_returns[$method])) {
            $result = &$this->_returns[$method]->findFirstMatch($args);
            return $result;
        }
        $null = null;
        return $null;
    }

    /**
     *    Tests the arguments against expectations.
     *    @param string $method        Method to check.
     *    @param array $args           Argument list to match.
     *    @param integer $timing       The position of this call
     *                                 in the call history.
     *    @access private
     */
    function _checkExpectations($method, $args, $timing) {
        $test = &$this->_getCurrentTestCase();
        if (isset($this->_max_counts[$method])) {
            if (! $this->_max_counts[$method]->test($timing + 1)) {
                $test->assert($this->_max_counts[$method], $timing + 1);
            }
        }
        if (isset($this->_expected_args_at[$timing][$method])) {
            $test->assert(
                    $this->_expected_args_at[$timing][$method],
                    $args,
                    "Mock method [$method] at [$timing] -> %s");
        } elseif (isset($this->_expected_args[$method])) {
            $test->assert(
                    $this->_expected_args[$method],
                    $args,
                    "Mock method [$method] -> %s");
        }
    }
}

/**
 *    Static methods only service class for code generation of
 *    mock objects.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class Mock {

    /**
     *    Factory for mock object classes.
     *    @access public
     */
    function Mock() {
        trigger_error('Mock factory methods are static.');
    }

    /**
     *    Clones a class' interface and creates a mock version
     *    that can have return values and expectations set.
     *    @param string $class         Class to clone.
     *    @param string $mock_class    New class name. Default is
     *                                 the old name with "Mock"
     *                                 prepended.
     *    @param array $methods        Additional methods to add beyond
     *                                 those in the cloned class. Use this
     *                                 to emulate the dynamic addition of
     *                                 methods in the cloned class or when
     *                                 the class hasn't been written yet.
     *    @static
     *    @access public
     */
    function generate($class, $mock_class = false, $methods = false) {
        $generator = new MockGenerator($class, $mock_class);
        return $generator->generateSubclass($methods);
    }

    /**
     *    Generates a version of a class with selected
     *    methods mocked only. Inherits the old class
     *    and chains the mock methods of an aggregated
     *    mock object.
     *    @param string $class            Class to clone.
     *    @param string $mock_class       New class name.
     *    @param array $methods           Methods to be overridden
     *                                    with mock versions.
     *    @static
     *    @access public
     */
    function generatePartial($class, $mock_class, $methods) {
        $generator = new MockGenerator($class, $mock_class);
        return $generator->generatePartial($methods);
    }

    /**
     *    Uses a stack trace to find the line of an assertion.
     *    @access public
     *    @static
     */
    function getExpectationLine() {
        $trace = new SimpleStackTrace(array('expect'));
        return $trace->traceMethod();
    }
}

/**
 *	  @package	SimpleTest
 *	  @subpackage	MockObjects
 *    @deprecated
 */
class Stub extends Mock {
}

/**
 *    Service class for code generation of mock objects.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class MockGenerator {
    var $_class;
    var $_mock_class;
    var $_mock_base;
    var $_reflection;

    /**
     *    Builds initial reflection object.
     *    @param string $class        Class to be mocked.
     *    @param string $mock_class   New class with identical interface,
     *                                but no behaviour.
     */
    function MockGenerator($class, $mock_class) {
        $this->_class = $class;
        $this->_mock_class = $mock_class;
        if (! $this->_mock_class) {
            $this->_mock_class = 'Mock' . $this->_class;
        }
        $this->_mock_base = SimpleTest::getMockBaseClass();
        $this->_reflection = new SimpleReflection($this->_class);
    }

    /**
     *    Clones a class' interface and creates a mock version
     *    that can have return values and expectations set.
     *    @param array $methods        Additional methods to add beyond
     *                                 those in th cloned class. Use this
     *                                 to emulate the dynamic addition of
     *                                 methods in the cloned class or when
     *                                 the class hasn't been written yet.
     *    @access public
     */
    function generate($methods) {
        if (! $this->_reflection->classOrInterfaceExists()) {
            return false;
        }
        $mock_reflection = new SimpleReflection($this->_mock_class);
        if ($mock_reflection->classExistsSansAutoload()) {
            return false;
        }
        $code = $this->_createClassCode($methods ? $methods : array());
        return eval("$code return \$code;");
    }
    
    /**
     *    Subclasses a class and overrides every method with a mock one
     *    that can have return values and expectations set. Chains
     *    to an aggregated SimpleMock.
     *    @param array $methods        Additional methods to add beyond
     *                                 those in the cloned class. Use this
     *                                 to emulate the dynamic addition of
     *                                 methods in the cloned class or when
     *                                 the class hasn't been written yet.
     *    @access public
     */
    function generateSubclass($methods) {
        if (! $this->_reflection->classOrInterfaceExists()) {
            return false;
        }
        $mock_reflection = new SimpleReflection($this->_mock_class);
        if ($mock_reflection->classExistsSansAutoload()) {
            return false;
        }
        if ($this->_reflection->isInterface() || $this->_reflection->hasFinal()) {
            $code = $this->_createClassCode($methods ? $methods : array());
            return eval("$code return \$code;");
        } else {
            $code = $this->_createSubclassCode($methods ? $methods : array());
            return eval("$code return \$code;");
        }
    }

    /**
     *    Generates a version of a class with selected
     *    methods mocked only. Inherits the old class
     *    and chains the mock methods of an aggregated
     *    mock object.
     *    @param array $methods           Methods to be overridden
     *                                    with mock versions.
     *    @access public
     */
    function generatePartial($methods) {
        if (! $this->_reflection->classExists($this->_class)) {
            return false;
        }
        $mock_reflection = new SimpleReflection($this->_mock_class);
        if ($mock_reflection->classExistsSansAutoload()) {
            trigger_error('Partial mock class [' . $this->_mock_class . '] already exists');
            return false;
        }
        $code = $this->_extendClassCode($methods);
        return eval("$code return \$code;");
    }

    /**
     *    The new mock class code as a string.
     *    @param array $methods          Additional methods.
     *    @return string                 Code for new mock class.
     *    @access private
     */
    function _createClassCode($methods) {
        $implements = '';
        $interfaces = $this->_reflection->getInterfaces();
        if (function_exists('spl_classes')) {
            $interfaces = array_diff($interfaces, array('Traversable'));
        }
        if (count($interfaces) > 0) {
            $implements = 'implements ' . implode(', ', $interfaces);
        }
        $code = "class " . $this->_mock_class . " extends " . $this->_mock_base . " $implements {\n";
        $code .= "    function " . $this->_mock_class . "() {\n";
        $code .= "        \$this->" . $this->_mock_base . "();\n";
        $code .= "    }\n";
        if (in_array('__construct', $this->_reflection->getMethods())) {
            $code .= "    " . $this->_reflection->getSignature('__construct') . " {\n";
            $code .= "        \$this->" . $this->_mock_base . "();\n";
            $code .= "    }\n";
        }
        $code .= $this->_createHandlerCode($methods);
        $code .= "}\n";
        return $code;
    }

    /**
     *    The new mock class code as a string. The mock will
     *    be a subclass of the original mocked class.
     *    @param array $methods          Additional methods.
     *    @return string                 Code for new mock class.
     *    @access private
     */
    function _createSubclassCode($methods) {
        $code  = "class " . $this->_mock_class . " extends " . $this->_class . " {\n";
        $code .= "    var \$_mock;\n";
        $code .= $this->_addMethodList(array_merge($methods, $this->_reflection->getMethods()));
        $code .= "\n";
        $code .= "    function " . $this->_mock_class . "() {\n";
        $code .= "        \$this->_mock = &new " . $this->_mock_base . "();\n";
        $code .= "        \$this->_mock->disableExpectationNameChecks();\n";
        $code .= "    }\n";
        $code .= $this->_chainMockReturns();
        $code .= $this->_chainMockExpectations();
        $code .= $this->_overrideMethods($this->_reflection->getMethods());
        $code .= $this->_createNewMethodCode($methods);
        $code .= "}\n";
        return $code;
    }

    /**
     *    The extension class code as a string. The class
     *    composites a mock object and chains mocked methods
     *    to it.
     *    @param array  $methods       Mocked methods.
     *    @return string               Code for a new class.
     *    @access private
     */
    function _extendClassCode($methods) {
        $code  = "class " . $this->_mock_class . " extends " . $this->_class . " {\n";
        $code .= "    var \$_mock;\n";
        $code .= $this->_addMethodList($methods);
        $code .= "\n";
        $code .= "    function " . $this->_mock_class . "() {\n";
        $code .= "        \$this->_mock = &new " . $this->_mock_base . "();\n";
        $code .= "        \$this->_mock->disableExpectationNameChecks();\n";
        $code .= "    }\n";
        $code .= $this->_chainMockReturns();
        $code .= $this->_chainMockExpectations();
        $code .= $this->_overrideMethods($methods);
        $code .= "}\n";
        return $code;
    }

    /**
     *    Creates code within a class to generate replaced
     *    methods. All methods call the _invoke() handler
     *    with the method name and the arguments in an
     *    array.
     *    @param array $methods    Additional methods.
     *    @access private
     */
    function _createHandlerCode($methods) {
        $code = '';
        $methods = array_merge($methods, $this->_reflection->getMethods());
        foreach ($methods as $method) {
            if ($this->_isConstructor($method)) {
                continue;
            }
            $mock_reflection = new SimpleReflection($this->_mock_base);
            if (in_array($method, $mock_reflection->getMethods())) {
                continue;
            }
            $code .= "    " . $this->_reflection->getSignature($method) . " {\n";
            $code .= "        \$args = func_get_args();\n";
            $code .= "        \$result = &\$this->_invoke(\"$method\", \$args);\n";
            $code .= "        return \$result;\n";
            $code .= "    }\n";
        }
        return $code;
    }

    /**
     *    Creates code within a class to generate a new
     *    methods. All methods call the _invoke() handler
     *    on the internal mock with the method name and
     *    the arguments in an array.
     *    @param array $methods    Additional methods.
     *    @access private
     */
    function _createNewMethodCode($methods) {
        $code = '';
        foreach ($methods as $method) {
            if ($this->_isConstructor($method)) {
                continue;
            }
            $mock_reflection = new SimpleReflection($this->_mock_base);
            if (in_array($method, $mock_reflection->getMethods())) {
                continue;
            }
            $code .= "    " . $this->_reflection->getSignature($method) . " {\n";
            $code .= "        \$args = func_get_args();\n";
            $code .= "        \$result = &\$this->_mock->_invoke(\"$method\", \$args);\n";
            $code .= "        return \$result;\n";
            $code .= "    }\n";
        }
        return $code;
    }

    /**
     *    Tests to see if a special PHP method is about to
     *    be stubbed by mistake.
     *    @param string $method    Method name.
     *    @return boolean          True if special.
     *    @access private
     */
    function _isConstructor($method) {
        return in_array(
                strtolower($method),
                array('__construct', '__destruct'));
    }

    /**
     *    Creates a list of mocked methods for error checking.
     *    @param array $methods       Mocked methods.
     *    @return string              Code for a method list.
     *    @access private
     */
    function _addMethodList($methods) {
        return "    var \$_mocked_methods = array('" .
                implode("', '", array_map('strtolower', $methods)) .
                "');\n";
    }

    /**
     *    Creates code to abandon the expectation if not mocked.
     *    @param string $alias       Parameter name of method name.
     *    @return string             Code for bail out.
     *    @access private
     */
    function _bailOutIfNotMocked($alias) {
        $code  = "        if (! in_array(strtolower($alias), \$this->_mocked_methods)) {\n";
        $code .= "            trigger_error(\"Method [$alias] is not mocked\");\n";
        $code .= "            \$null = null;\n";
        $code .= "            return \$null;\n";
        $code .= "        }\n";
        return $code;
    }

    /**
     *    Creates source code for chaining to the composited
     *    mock object.
     *    @return string           Code for mock set up.
     *    @access private
     */
    function _chainMockReturns() {
        $code  = "    function setReturnValue(\$method, \$value, \$args = false) {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->setReturnValue(\$method, \$value, \$args);\n";
        $code .= "    }\n";
        $code .= "    function setReturnValueAt(\$timing, \$method, \$value, \$args = false) {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->setReturnValueAt(\$timing, \$method, \$value, \$args);\n";
        $code .= "    }\n";
        $code .= "    function setReturnReference(\$method, &\$ref, \$args = false) {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->setReturnReference(\$method, \$ref, \$args);\n";
        $code .= "    }\n";
        $code .= "    function setReturnReferenceAt(\$timing, \$method, &\$ref, \$args = false) {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->setReturnReferenceAt(\$timing, \$method, \$ref, \$args);\n";
        $code .= "    }\n";
        return $code;
    }

    /**
     *    Creates source code for chaining to an aggregated
     *    mock object.
     *    @return string                 Code for expectations.
     *    @access private
     */
    function _chainMockExpectations() {
        $code  = "    function expect(\$method, \$args = false, \$msg = '%s') {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->expect(\$method, \$args, \$msg);\n";
        $code .= "    }\n";
        $code .= "    function expectArguments(\$method, \$args = false, \$msg = '%s') {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->expectArguments(\$method, \$args, \$msg);\n";
        $code .= "    }\n";
        $code .= "    function expectAt(\$timing, \$method, \$args = false, \$msg = '%s') {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->expectArgumentsAt(\$timing, \$method, \$args, \$msg);\n";
        $code .= "    }\n";
        $code .= "    function expectArgumentsAt(\$timing, \$method, \$args = false, \$msg = '%s') {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->expectArgumentsAt(\$timing, \$method, \$args, \$msg);\n";
        $code .= "    }\n";
        $code .= "    function expectCallCount(\$method, \$count) {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->expectCallCount(\$method, \$count, \$msg = '%s');\n";
        $code .= "    }\n";
        $code .= "    function expectMaximumCallCount(\$method, \$count, \$msg = '%s') {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->expectMaximumCallCount(\$method, \$count, \$msg = '%s');\n";
        $code .= "    }\n";
        $code .= "    function expectMinimumCallCount(\$method, \$count, \$msg = '%s') {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->expectMinimumCallCount(\$method, \$count, \$msg = '%s');\n";
        $code .= "    }\n";
        $code .= "    function expectNever(\$method) {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->expectNever(\$method);\n";
        $code .= "    }\n";
        $code .= "    function expectOnce(\$method, \$args = false, \$msg = '%s') {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->expectOnce(\$method, \$args, \$msg);\n";
        $code .= "    }\n";
        $code .= "    function expectAtLeastOnce(\$method, \$args = false, \$msg = '%s') {\n";
        $code .= $this->_bailOutIfNotMocked("\$method");
        $code .= "        \$this->_mock->expectAtLeastOnce(\$method, \$args, \$msg);\n";
        $code .= "    }\n";
        $code .= "    function tally() {\n";
        $code .= "    }\n";
        return $code;
    }

    /**
     *    Creates source code to override a list of methods
     *    with mock versions.
     *    @param array $methods    Methods to be overridden
     *                             with mock versions.
     *    @return string           Code for overridden chains.
     *    @access private
     */
    function _overrideMethods($methods) {
        $code = "";
        foreach ($methods as $method) {
            if ($this->_isConstructor($method)) {
                continue;
            }
            $code .= "    " . $this->_reflection->getSignature($method) . " {\n";
            $code .= "        \$args = func_get_args();\n";
            $code .= "        \$result = &\$this->_mock->_invoke(\"$method\", \$args);\n";
            $code .= "        return \$result;\n";
            $code .= "    }\n";
        }
        return $code;
    }
}
?>
