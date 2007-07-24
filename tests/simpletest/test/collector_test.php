<?php
// $Id: collector_test.php,v 1.13 2007/04/30 23:39:59 lastcraft Exp $
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../collector.php');
SimpleTest::ignore('MockTestSuite');
Mock::generate('TestSuite');

class PathEqualExpectation extends EqualExpectation {
	function PathEqualExpectation($value, $message = '%s') {
    	$this->EqualExpectation(str_replace("\\", '/', $value), $message);
	}

    function test($compare) {
        return parent::test(str_replace("\\", '/', $compare));
    }
}

class TestOfCollector extends UnitTestCase {

    function testCollectionIsAddedToGroup() {
        $suite = &new MockTestSuite();
        $suite->expectMinimumCallCount('addTestFile', 2);
        $suite->expectArguments(
                'addTestFile',
                array(new PatternExpectation('/collectable\\.(1|2)$/')));
        $collector = &new SimpleCollector();
        $collector->collect($suite, dirname(__FILE__) . '/support/collector/');
    }
}

class TestOfPatternCollector extends UnitTestCase {

    function testAddingEverythingToGroup() {
        $suite = &new MockTestSuite();
        $suite->expectCallCount('addTestFile', 2);
        $suite->expectArguments(
                'addTestFile',
                array(new PatternExpectation('/collectable\\.(1|2)$/')));
        $collector = &new SimplePatternCollector('/.*/');
        $collector->collect($suite, dirname(__FILE__) . '/support/collector/');
    }

    function testOnlyMatchedFilesAreAddedToGroup() {
        $suite = &new MockTestSuite();
        $suite->expectOnce('addTestFile', array(new PathEqualExpectation(
        		dirname(__FILE__) . '/support/collector/collectable.1')));
        $collector = &new SimplePatternCollector('/1$/');
        $collector->collect($suite, dirname(__FILE__) . '/support/collector/');
    }
}
?>