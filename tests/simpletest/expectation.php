<?php
    /**
     *    base include file for SimpleTest
     *    @package    SimpleTest
     *    @subpackage    UnitTester
     *    @version    $Id: expectation.php,v 1.50 2007/06/09 08:35:54 pachanga Exp $
     */

    /**#@+
     *    include other SimpleTest class files
     */
    require_once(dirname(__FILE__) . '/dumper.php');
    require_once(dirname(__FILE__) . '/compatibility.php');
    /**#@-*/

    /**
     *    Assertion that can display failure information.
     *    Also includes various helper methods.
     *    @package SimpleTest
     *    @subpackage UnitTester
     *    @abstract
     */
    class SimpleExpectation {
        var $_dumper = false;
        var $_message;

        /**
         *    Creates a dumper for displaying values and sets
         *    the test message.
         *    @param string $message    Customised message on failure.
         */
        function SimpleExpectation($message = '%s') {
            $this->_message = $message;
        }

        /**
         *    Tests the expectation. True if correct.
         *    @param mixed $compare        Comparison value.
         *    @return boolean              True if correct.
         *    @access public
         *    @abstract
         */
        function test($compare) {
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         *    @abstract
         */
        function testMessage($compare) {
        }

        /**
         *    Overlays the generated message onto the stored user
         *    message. An additional message can be interjected.
         *    @param mixed $compare        Comparison value.
         *    @param SimpleDumper $dumper  For formatting the results.
         *    @return string               Description of success
         *                                 or failure.
         *    @access public
         */
        function overlayMessage($compare, $dumper) {
            $this->_dumper = $dumper;
            return sprintf($this->_message, $this->testMessage($compare));
        }

        /**
         *    Accessor for the dumper.
         *    @return SimpleDumper    Current value dumper.
         *    @access protected
         */
        function &_getDumper() {
            if (! $this->_dumper) {
                $dumper = &new SimpleDumper();
                return $dumper;
            }
            return $this->_dumper;
        }

        /**
         *    Test to see if a value is an expectation object.
         *    A useful utility method.
         *    @param mixed $expectation    Hopefully an Epectation
         *                                 class.
         *    @return boolean              True if descended from
         *                                 this class.
         *    @access public
         *    @static
         */
        function isExpectation($expectation) {
            return is_object($expectation) &&
                    SimpleTestCompatibility::isA($expectation, 'SimpleExpectation');
        }
    }

    /**
     *    A wildcard expectation always matches.
     *    @package SimpleTest
     *    @subpackage MockObjects
     */
    class AnythingExpectation extends SimpleExpectation {

        /**
         *    Tests the expectation. Always true.
         *    @param mixed $compare  Ignored.
         *    @return boolean        True.
         *    @access public
         */
        function test($compare) {
            return true;
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            $dumper = &$this->_getDumper();
            return 'Anything always matches [' . $dumper->describeValue($compare) . ']';
        }
    }

    /**
     *    An expectation that never matches.
     *    @package SimpleTest
     *    @subpackage MockObjects
     */
    class FailedExpectation extends SimpleExpectation {

        /**
         *    Tests the expectation. Always false.
         *    @param mixed $compare  Ignored.
         *    @return boolean        True.
         *    @access public
         */
        function test($compare) {
            return false;
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of failure.
         *    @access public
         */
        function testMessage($compare) {
            $dumper = &$this->_getDumper();
            return 'Failed expectation never matches [' . $dumper->describeValue($compare) . ']';
        }
    }

    /**
     *    An expectation that passes on boolean true.
     *    @package SimpleTest
     *    @subpackage MockObjects
     */
    class TrueExpectation extends SimpleExpectation {

        /**
         *    Tests the expectation.
         *    @param mixed $compare  Should be true.
         *    @return boolean        True on match.
         *    @access public
         */
        function test($compare) {
            return (boolean)$compare;
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            $dumper = &$this->_getDumper();
            return 'Expected true, got [' . $dumper->describeValue($compare) . ']';
        }
    }

    /**
     *    An expectation that passes on boolean false.
     *    @package SimpleTest
     *    @subpackage MockObjects
     */
    class FalseExpectation extends SimpleExpectation {

        /**
         *    Tests the expectation.
         *    @param mixed $compare  Should be false.
         *    @return boolean        True on match.
         *    @access public
         */
        function test($compare) {
            return ! (boolean)$compare;
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            $dumper = &$this->_getDumper();
            return 'Expected false, got [' . $dumper->describeValue($compare) . ']';
        }
    }

    /**
     *    Test for equality.
     *    @package SimpleTest
     *    @subpackage UnitTester
     */
    class EqualExpectation extends SimpleExpectation {
        var $_value;

        /**
         *    Sets the value to compare against.
         *    @param mixed $value        Test value to match.
         *    @param string $message     Customised message on failure.
         *    @access public
         */
        function EqualExpectation($value, $message = '%s') {
            $this->SimpleExpectation($message);
            $this->_value = $value;
        }

        /**
         *    Tests the expectation. True if it matches the
         *    held value.
         *    @param mixed $compare        Comparison value.
         *    @return boolean              True if correct.
         *    @access public
         */
        function test($compare) {
            return (($this->_value == $compare) && ($compare == $this->_value));
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            if ($this->test($compare)) {
                return "Equal expectation [" . $this->_dumper->describeValue($this->_value) . "]";
            } else {
                return "Equal expectation fails " .
                        $this->_dumper->describeDifference($this->_value, $compare);
            }
        }

        /**
         *    Accessor for comparison value.
         *    @return mixed       Held value to compare with.
         *    @access protected
         */
        function _getValue() {
            return $this->_value;
        }
    }

    /**
     *    Test for inequality.
     *    @package SimpleTest
     *    @subpackage UnitTester
     */
    class NotEqualExpectation extends EqualExpectation {

        /**
         *    Sets the value to compare against.
         *    @param mixed $value       Test value to match.
         *    @param string $message    Customised message on failure.
         *    @access public
         */
        function NotEqualExpectation($value, $message = '%s') {
            $this->EqualExpectation($value, $message);
        }

        /**
         *    Tests the expectation. True if it differs from the
         *    held value.
         *    @param mixed $compare        Comparison value.
         *    @return boolean              True if correct.
         *    @access public
         */
        function test($compare) {
            return ! parent::test($compare);
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            $dumper = &$this->_getDumper();
            if ($this->test($compare)) {
                return "Not equal expectation passes " .
                        $dumper->describeDifference($this->_getValue(), $compare);
            } else {
                return "Not equal expectation fails [" .
                        $dumper->describeValue($this->_getValue()) .
                        "] matches";
            }
        }
    }

    /**
     *    Test for being within a range.
     *    @package SimpleTest
     *    @subpackage UnitTester
     */
    class WithinMarginExpectation extends SimpleExpectation {
        var $_upper;
        var $_lower;

        /**
         *    Sets the value to compare against and the fuzziness of
         *    the match. Used for comparing floating point values.
         *    @param mixed $value        Test value to match.
         *    @param mixed $margin       Fuzziness of match.
         *    @param string $message     Customised message on failure.
         *    @access public
         */
        function WithinMarginExpectation($value, $margin, $message = '%s') {
            $this->SimpleExpectation($message);
            $this->_upper = $value + $margin;
            $this->_lower = $value - $margin;
        }

        /**
         *    Tests the expectation. True if it matches the
         *    held value.
         *    @param mixed $compare        Comparison value.
         *    @return boolean              True if correct.
         *    @access public
         */
        function test($compare) {
            return (($compare <= $this->_upper) && ($compare >= $this->_lower));
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            if ($this->test($compare)) {
                return $this->_withinMessage($compare);
            } else {
                return $this->_outsideMessage($compare);
            }
        }

        /**
         *    Creates a the message for being within the range.
         *    @param mixed $compare        Value being tested.
         *    @access private
         */
        function _withinMessage($compare) {
            return "Within expectation [" . $this->_dumper->describeValue($this->_lower) . "] and [" .
                    $this->_dumper->describeValue($this->_upper) . "]";
        }

        /**
         *    Creates a the message for being within the range.
         *    @param mixed $compare        Value being tested.
         *    @access private
         */
        function _outsideMessage($compare) {
            if ($compare > $this->_upper) {
                return "Outside expectation " .
                        $this->_dumper->describeDifference($compare, $this->_upper);
            } else {
                return "Outside expectation " .
                        $this->_dumper->describeDifference($compare, $this->_lower);
            }
        }
    }

    /**
     *    Test for being outside of a range.
     *    @package SimpleTest
     *    @subpackage UnitTester
     */
    class OutsideMarginExpectation extends WithinMarginExpectation {

        /**
         *    Sets the value to compare against and the fuzziness of
         *    the match. Used for comparing floating point values.
         *    @param mixed $value        Test value to not match.
         *    @param mixed $margin       Fuzziness of match.
         *    @param string $message     Customised message on failure.
         *    @access public
         */
        function OutsideMarginExpectation($value, $margin, $message = '%s') {
            $this->WithinMarginExpectation($value, $margin, $message);
        }

        /**
         *    Tests the expectation. True if it matches the
         *    held value.
         *    @param mixed $compare        Comparison value.
         *    @return boolean              True if correct.
         *    @access public
         */
        function test($compare) {
            return ! parent::test($compare);
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            if (! $this->test($compare)) {
                return $this->_withinMessage($compare);
            } else {
                return $this->_outsideMessage($compare);
            }
        }
    }

    /**
     *    Test for reference.
     *    @package SimpleTest
     *    @subpackage UnitTester
     */
    class ReferenceExpectation extends SimpleExpectation {
        var $_value;

        /**
         *    Sets the reference value to compare against.
         *    @param mixed $value       Test reference to match.
         *    @param string $message    Customised message on failure.
         *    @access public
         */
        function ReferenceExpectation(&$value, $message = '%s') {
            $this->SimpleExpectation($message);
            $this->_value =& $value;
        }

        /**
         *    Tests the expectation. True if it exactly
         *    references the held value.
         *    @param mixed $compare        Comparison reference.
         *    @return boolean              True if correct.
         *    @access public
         */
        function test(&$compare) {
            return SimpleTestCompatibility::isReference($this->_value, $compare);
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            if ($this->test($compare)) {
                return "Reference expectation [" . $this->_dumper->describeValue($this->_value) . "]";
            } else {
                return "Reference expectation fails " .
                        $this->_dumper->describeDifference($this->_value, $compare);
            }
        }

        function _getValue() {
            return $this->_value;
        }
    }

    /**
     *    Test for identity.
     *    @package SimpleTest
     *    @subpackage UnitTester
     */
    class IdenticalExpectation extends EqualExpectation {

        /**
         *    Sets the value to compare against.
         *    @param mixed $value       Test value to match.
         *    @param string $message    Customised message on failure.
         *    @access public
         */
        function IdenticalExpectation($value, $message = '%s') {
            $this->EqualExpectation($value, $message);
        }

        /**
         *    Tests the expectation. True if it exactly
         *    matches the held value.
         *    @param mixed $compare        Comparison value.
         *    @return boolean              True if correct.
         *    @access public
         */
        function test($compare) {
            return SimpleTestCompatibility::isIdentical($this->_getValue(), $compare);
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            $dumper = &$this->_getDumper();
            if ($this->test($compare)) {
                return "Identical expectation [" . $dumper->describeValue($this->_getValue()) . "]";
            } else {
                return "Identical expectation [" . $dumper->describeValue($this->_getValue()) .
                        "] fails with [" .
                        $dumper->describeValue($compare) . "] " .
                        $dumper->describeDifference($this->_getValue(), $compare, TYPE_MATTERS);
            }
        }
    }

    /**
     *    Test for non-identity.
     *    @package SimpleTest
     *    @subpackage UnitTester
     */
    class NotIdenticalExpectation extends IdenticalExpectation {

        /**
         *    Sets the value to compare against.
         *    @param mixed $value        Test value to match.
         *    @param string $message     Customised message on failure.
         *    @access public
         */
        function NotIdenticalExpectation($value, $message = '%s') {
            $this->IdenticalExpectation($value, $message);
        }

        /**
         *    Tests the expectation. True if it differs from the
         *    held value.
         *    @param mixed $compare        Comparison value.
         *    @return boolean              True if correct.
         *    @access public
         */
        function test($compare) {
            return ! parent::test($compare);
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            $dumper = &$this->_getDumper();
            if ($this->test($compare)) {
                return "Not identical expectation passes " .
                        $dumper->describeDifference($this->_getValue(), $compare, TYPE_MATTERS);
            } else {
                return "Not identical expectation [" . $dumper->describeValue($this->_getValue()) . "] matches";
            }
        }
    }

    /**
     *    Test for a pattern using Perl regex rules.
     *    @package SimpleTest
     *    @subpackage UnitTester
     */
    class PatternExpectation extends SimpleExpectation {
        var $_pattern;

        /**
         *    Sets the value to compare against.
         *    @param string $pattern    Pattern to search for.
         *    @param string $message    Customised message on failure.
         *    @access public
         */
        function PatternExpectation($pattern, $message = '%s') {
            $this->SimpleExpectation($message);
            $this->_pattern = $pattern;
        }

        /**
         *    Accessor for the pattern.
         *    @return string       Perl regex as string.
         *    @access protected
         */
        function _getPattern() {
            return $this->_pattern;
        }

        /**
         *    Tests the expectation. True if the Perl regex
         *    matches the comparison value.
         *    @param string $compare        Comparison value.
         *    @return boolean               True if correct.
         *    @access public
         */
        function test($compare) {
            return (boolean)preg_match($this->_getPattern(), $compare);
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            if ($this->test($compare)) {
                return $this->_describePatternMatch($this->_getPattern(), $compare);
            } else {
                $dumper = &$this->_getDumper();
                return "Pattern [" . $this->_getPattern() .
                        "] not detected in [" .
                        $dumper->describeValue($compare) . "]";
            }
        }

        /**
         *    Describes a pattern match including the string
         *    found and it's position.
         *    @param string $pattern        Regex to match against.
         *    @param string $subject        Subject to search.
         *    @access protected
         */
        function _describePatternMatch($pattern, $subject) {
            preg_match($pattern, $subject, $matches);
            $position = strpos($subject, $matches[0]);
            $dumper = $this->_getDumper();
            return "Pattern [$pattern] detected at character [$position] in [" .
                    $dumper->describeValue($subject) . "] as [" .
                    $matches[0] . "] in region [" .
                    $dumper->clipString($subject, 100, $position) . "]";
        }
    }

    /**
     *    @package SimpleTest
     *    @subpackage UnitTester
     *    @deprecated
     */
    class WantedPatternExpectation extends PatternExpectation {
    }

    /**
     *    Fail if a pattern is detected within the
     *    comparison.
     *    @package SimpleTest
     *    @subpackage UnitTester
     */
    class NoPatternExpectation extends PatternExpectation {

        /**
         *    Sets the reject pattern
         *    @param string $pattern    Pattern to search for.
         *    @param string $message    Customised message on failure.
         *    @access public
         */
        function NoPatternExpectation($pattern, $message = '%s') {
            $this->PatternExpectation($pattern, $message);
        }

        /**
         *    Tests the expectation. False if the Perl regex
         *    matches the comparison value.
         *    @param string $compare        Comparison value.
         *    @return boolean               True if correct.
         *    @access public
         */
        function test($compare) {
            return ! parent::test($compare);
        }

        /**
         *    Returns a human readable test message.
         *    @param string $compare      Comparison value.
         *    @return string              Description of success
         *                                or failure.
         *    @access public
         */
        function testMessage($compare) {
            if ($this->test($compare)) {
                $dumper = &$this->_getDumper();
                return "Pattern [" . $this->_getPattern() .
                        "] not detected in [" .
                        $dumper->describeValue($compare) . "]";
            } else {
                return $this->_describePatternMatch($this->_getPattern(), $compare);
            }
        }
    }

    /**
     *    @package SimpleTest
     *    @subpackage UnitTester
     *      @deprecated
     */
    class UnwantedPatternExpectation extends NoPatternExpectation {
    }

    /**
     *    Tests either type or class name if it's an object.
     *      @package SimpleTest
     *      @subpackage UnitTester
     */
    class IsAExpectation extends SimpleExpectation {
        var $_type;

        /**
         *    Sets the type to compare with.
         *    @param string $type       Type or class name.
         *    @param string $message    Customised message on failure.
         *    @access public
         */
        function IsAExpectation($type, $message = '%s') {
            $this->SimpleExpectation($message);
            $this->_type = $type;
        }

        /**
         *    Accessor for type to check against.
         *    @return string    Type or class name.
         *    @access protected
         */
        function _getType() {
            return $this->_type;
        }

        /**
         *    Tests the expectation. True if the type or
         *    class matches the string value.
         *    @param string $compare        Comparison value.
         *    @return boolean               True if correct.
         *    @access public
         */
        function test($compare) {
            if (is_object($compare)) {
                return SimpleTestCompatibility::isA($compare, $this->_type);
            } else {
                return (strtolower(gettype($compare)) == $this->_canonicalType($this->_type));
            }
        }

        /**
         *    Coerces type name into a gettype() match.
         *    @param string $type        User type.
         *    @return string             Simpler type.
         *    @access private
         */
        function _canonicalType($type) {
            $type = strtolower($type);
            $map = array(
                    'bool' => 'boolean',
                    'float' => 'double',
                    'real' => 'double',
                    'int' => 'integer');
            if (isset($map[$type])) {
                $type = $map[$type];
            }
            return $type;
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            $dumper = &$this->_getDumper();
            return "Value [" . $dumper->describeValue($compare) .
                    "] should be type [" . $this->_type . "]";
        }
    }

    /**
     *    Tests either type or class name if it's an object.
     *    Will succeed if the type does not match.
     *      @package SimpleTest
     *      @subpackage UnitTester
     */
    class NotAExpectation extends IsAExpectation {
        var $_type;

        /**
         *    Sets the type to compare with.
         *    @param string $type       Type or class name.
         *    @param string $message    Customised message on failure.
         *    @access public
         */
        function NotAExpectation($type, $message = '%s') {
            $this->IsAExpectation($type, $message);
        }

        /**
         *    Tests the expectation. False if the type or
         *    class matches the string value.
         *    @param string $compare        Comparison value.
         *    @return boolean               True if different.
         *    @access public
         */
        function test($compare) {
            return ! parent::test($compare);
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            $dumper = &$this->_getDumper();
            return "Value [" . $dumper->describeValue($compare) .
                    "] should not be type [" . $this->_getType() . "]";
        }
    }

    /**
     *    Tests for existance of a method in an object
     *      @package SimpleTest
     *      @subpackage UnitTester
     */
    class MethodExistsExpectation extends SimpleExpectation {
        var $_method;

        /**
         *    Sets the value to compare against.
         *    @param string $method     Method to check.
         *    @param string $message    Customised message on failure.
         *    @access public
         *    @return void
         */
        function MethodExistsExpectation($method, $message = '%s') {
            $this->SimpleExpectation($message);
            $this->_method = &$method;
        }

        /**
         *    Tests the expectation. True if the method exists in the test object.
         *    @param string $compare        Comparison method name.
         *    @return boolean               True if correct.
         *    @access public
         */
        function test($compare) {
            return (boolean)(is_object($compare) && method_exists($compare, $this->_method));
        }

        /**
         *    Returns a human readable test message.
         *    @param mixed $compare      Comparison value.
         *    @return string             Description of success
         *                               or failure.
         *    @access public
         */
        function testMessage($compare) {
            $dumper = &$this->_getDumper();
            if (! is_object($compare)) {
                return 'No method on non-object [' . $dumper->describeValue($compare) . ']';
            }
            $method = $this->_method;
            return "Object [" . $dumper->describeValue($compare) .
                    "] should contain method [$method]";
        }
    }
?>