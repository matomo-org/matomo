<?php
@include_once 'Text/Diff.php';
@include_once 'Text/Diff/Renderer.php';
@include_once 'Text/Diff/Renderer/unified.php';
require_once 'PEAR/ErrorStack.php';
require_once 'PEAR.php';
class PEAR_PHPTest
{
    var $_diffonly;
    var $_errors;
    function PEAR_PHPTest($diffonly = false, $noStackCatch = false)
    {
        $this->_diffonly = $diffonly;
        $this->_errors = array();
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array(&$this, 'pearerrorCallback'));
        if (!$noStackCatch) {
            PEAR_ErrorStack::setDefaultCallback(array(&$this, 'pearerrorstackCallback'));
        }
    }

    function pearerrorCallback($err)
    {
        PEAR_ErrorStack::staticPush('PEAR_Error', -1, 'error', array('obj' => $err),
            $err->getMessage());
    }

    function pearerrorstackCallback($err)
    {
        $this->_errors[] = $err;
    }

    function assertPEARError($err, $message)
    {
        if (is_a($err, 'PEAR_Error')) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Not a PEAR_Error\n";
        return false;
    }

    function assertNoErrors($message, $trace = null)
    {
        if (count($this->_errors) == 0) {
            return true;
        }
        if ($trace === null) {
            $trace = debug_backtrace();
        }
        $this->_failTest($trace, $message);
        foreach ($this->_errors as $err) {
            if ($err['package'] == 'PEAR_Error') {
                echo "Unexpected PEAR_Error:\n";
                echo 'message "' . $err['message'] . "\"\n";
            } else {
                echo "Unexpected PEAR_ErrorStack error:\n";
                echo 'package "' . $err['package'] . "\"\n";
                echo 'message "' . $err['message'] . "\"\n";
            }
        }
        $this->_errors = array();
        return false;
    }

    function assertErrors($errors, $message, $trace = null)
    {
        if (!count($this->_errors)) {
            if ($trace === null) {
                $trace = debug_backtrace();
            }
            $this->_failTest($trace, $message);
            echo "No errors caught, but errors were expected\n";
            return false;
        }
        if (!isset($errors[0])) {
            $errors = array($errors);
        }
        $failed = false;
        foreach ($errors as $err) {
            $found = false;
            foreach ($this->_errors as $i => $caughterror) {
                if ($caughterror['package'] == $err['package']) {
                    if ($caughterror['message'] == $err['message']) {
                        $found = true;
                        break;
                    }
                }
            }
            if ($found) {
                unset($this->_errors[$i]);
                continue;
            }
            if (!$failed) {
                if ($trace === null) {
                    $trace = debug_backtrace();
                }
                $failed = true;
                $this->_failTest($trace, $message);
            }
            echo "Unthrown error:\n";
            if ($err['package'] == 'PEAR_Error') {
                echo "PEAR_Error:\n";
            } else {
                echo "error package: \"$err[package]\"\n";
            }
            echo "message: \"$err[message]\"\n";
        }
        if (count($this->_errors)) {
            if (!$failed) {
                if ($trace === null) {
                    $trace = debug_backtrace();
                }
                $failed = true;
                $this->_failTest($trace, $message);
            }
            foreach ($this->_errors as $err) {
                echo "Unexpected error:\n";
                if ($err['package'] == 'PEAR_Error') {
                    echo "PEAR_Error:\n";
                } else {
                    echo "error package: \"$err[package]\"\n";
                }
                echo "message: \"$err[message]\"\n";
            }
        }
        $this->_errors = array();
        return !$failed;
    }

    function assertTrue($test, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if ($test === true) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-true value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertIsa($control, $test, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if (is_a($test, $control)) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-$control object: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNull($test, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if ($test === null) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-null value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNotNull($test, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if ($test !== null) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected null: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertSame($test, $test1, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if ($test === $test1) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpectedly two vars are not the same thing: \n";
        echo "\n'$message'\n";
        return false;
    }

    function assertNotSame($test, $test1, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if ($test !== $test1) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpectedly two vars are the same thing: \n";
        echo "\n'$message'\n";
        return false;
    }

    function assertFalse($test, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if ($test === false) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-false value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNotTrue($test, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if (!$test) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected loose true value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNotFalse($test, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if ($test) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected loose false value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertEquals($control, $test, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if (str_replace(array("\r", "\n"), array('', ''),
            var_export($control, true)) != str_replace(array("\r", "\n"), array('', ''),
            var_export($test, true))) {
            $this->_failTest(debug_backtrace(), $message);
            if (class_exists('Text_Diff')) {
                echo "Diff of expecting/received:\n";
                $diff = &new Text_Diff(
                    explode("\n", var_export($control, true)),
                    explode("\n", var_export($test, true)));

                // Output the diff in unified format.
                $renderer = &new Text_Diff_Renderer_unified();
                echo $renderer->render($diff);
                if ($this->_diffonly) {
                    return false;
                }
            }
            echo "Expecting:\n";
            var_export($control);
            echo "\nReceived:\n";
            var_export($test);
            echo "\n";
            return false;
        }
        return true;
    }

    function assertFileExists($fname, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if (!@file_exists($fname)) {
            $this->_failTest(debug_backtrace(), $message);
            echo "File '$fname' does not exist, and should\n";
            return false;
        }
        return true;
    }

    function assertFileNotExists($fname, $message)
    {
        $this->assertNoErrors($message, debug_backtrace());
        if (@file_exists($fname)) {
            $this->_failTest(debug_backtrace(), $message);
            echo "File '$fname' exists, and should not\n";
            return false;
        }
        return true;
    }

    function _failTest($trace, $message)
    {
        echo 'Test Failure: "' . $message  . "\"\n in " . $trace[0]['file'] . ' line ' .
            $trace[0]['line'] . "\n";
    }

    function showAll()
    {
        $this->_diffonly = false;
    }
}
?>