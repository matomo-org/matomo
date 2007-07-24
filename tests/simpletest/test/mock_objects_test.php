<?php
// $Id: mock_objects_test.php,v 1.36 2007/07/07 00:31:03 lastcraft Exp $
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../expectation.php');
require_once(dirname(__FILE__) . '/../mock_objects.php');

class TestOfAnythingExpectation extends UnitTestCase {

    function testSimpleInteger() {
        $expectation = new AnythingExpectation();
        $this->assertTrue($expectation->test(33));
        $this->assertTrue($expectation->test(false));
        $this->assertTrue($expectation->test(null));
    }
}

class TestOfParametersExpectation extends UnitTestCase {

    function testEmptyMatch() {
        $expectation = new ParametersExpectation(array());
        $this->assertTrue($expectation->test(array()));
        $this->assertFalse($expectation->test(array(33)));
    }

    function testSingleMatch() {
        $expectation = new ParametersExpectation(array(0));
        $this->assertFalse($expectation->test(array(1)));
        $this->assertTrue($expectation->test(array(0)));
    }

    function testAnyMatch() {
        $expectation = new ParametersExpectation(false);
        $this->assertTrue($expectation->test(array()));
        $this->assertTrue($expectation->test(array(1, 2)));
    }

    function testMissingParameter() {
        $expectation = new ParametersExpectation(array(0));
        $this->assertFalse($expectation->test(array()));
    }

    function testNullParameter() {
        $expectation = new ParametersExpectation(array(null));
        $this->assertTrue($expectation->test(array(null)));
        $this->assertFalse($expectation->test(array()));
    }

    function testAnythingExpectations() {
        $expectation = new ParametersExpectation(array(new AnythingExpectation()));
        $this->assertFalse($expectation->test(array()));
        $this->assertIdentical($expectation->test(array(null)), true);
        $this->assertIdentical($expectation->test(array(13)), true);
    }

    function testOtherExpectations() {
        $expectation = new ParametersExpectation(
                array(new PatternExpectation('/hello/i')));
        $this->assertFalse($expectation->test(array('Goodbye')));
        $this->assertTrue($expectation->test(array('hello')));
        $this->assertTrue($expectation->test(array('Hello')));
    }

    function testIdentityOnly() {
        $expectation = new ParametersExpectation(array("0"));
        $this->assertFalse($expectation->test(array(0)));
        $this->assertTrue($expectation->test(array("0")));
    }

    function testLongList() {
        $expectation = new ParametersExpectation(
                array("0", 0, new AnythingExpectation(), false));
        $this->assertTrue($expectation->test(array("0", 0, 37, false)));
        $this->assertFalse($expectation->test(array("0", 0, 37, true)));
        $this->assertFalse($expectation->test(array("0", 0, 37)));
    }
}

class TestOfCallMap extends UnitTestCase {

    function testEmpty() {
        $map = new CallMap();
        $this->assertFalse($map->isMatch("any", array()));
        $this->assertNull($map->findFirstMatch("any", array()));
    }

    function testExactValue() {
        $map = new CallMap();
        $map->addValue(array(0), "Fred");
        $map->addValue(array(1), "Jim");
        $map->addValue(array("1"), "Tom");
        $this->assertTrue($map->isMatch(array(0)));
        $this->assertEqual($map->findFirstMatch(array(0)), "Fred");
        $this->assertTrue($map->isMatch(array(1)));
        $this->assertEqual($map->findFirstMatch(array(1)), "Jim");
        $this->assertEqual($map->findFirstMatch(array("1")), "Tom");
    }

    function testExactReference() {
        $map = new CallMap();
        $ref = "Fred";
        $map->addReference(array(0), $ref);
        $this->assertEqual($map->findFirstMatch(array(0)), "Fred");
        $ref2 = &$map->findFirstMatch(array(0));
        $this->assertReference($ref2, $ref);
    }

    function testWildcard() {
        $map = new CallMap();
        $map->addValue(array(new AnythingExpectation(), 1, 3), "Fred");
        $this->assertTrue($map->isMatch(array(2, 1, 3)));
        $this->assertEqual($map->findFirstMatch(array(2, 1, 3)), "Fred");
    }

    function testAllWildcard() {
        $map = new CallMap();
        $this->assertFalse($map->isMatch(array(2, 1, 3)));
        $map->addValue("", "Fred");
        $this->assertTrue($map->isMatch(array(2, 1, 3)));
        $this->assertEqual($map->findFirstMatch(array(2, 1, 3)), "Fred");
    }

    function testOrdering() {
        $map = new CallMap();
        $map->addValue(array(1, 2), "1, 2");
        $map->addValue(array(1, 3), "1, 3");
        $map->addValue(array(1), "1");
        $map->addValue(array(1, 4), "1, 4");
        $map->addValue(array(new AnythingExpectation()), "Any");
        $map->addValue(array(2), "2");
        $map->addValue("", "Default");
        $map->addValue(array(), "None");
        $this->assertEqual($map->findFirstMatch(array(1, 2)), "1, 2");
        $this->assertEqual($map->findFirstMatch(array(1, 3)), "1, 3");
        $this->assertEqual($map->findFirstMatch(array(1, 4)), "1, 4");
        $this->assertEqual($map->findFirstMatch(array(1)), "1");
        $this->assertEqual($map->findFirstMatch(array(2)), "Any");
        $this->assertEqual($map->findFirstMatch(array(3)), "Any");
        $this->assertEqual($map->findFirstMatch(array()), "Default");
    }
}

class Dummy {
    function Dummy() {
    }

    function aMethod() {
        return true;
    }

    function anotherMethod() {
        return true;
    }
}
Mock::generate('Dummy');
Mock::generate('Dummy', 'AnotherMockDummy');
Mock::generate('Dummy', 'MockDummyWithExtraMethods', array('extraMethod'));

class TestOfMockGeneration extends UnitTestCase {

    function testCloning() {
        $mock = &new MockDummy();
        $this->assertTrue(method_exists($mock, "aMethod"));
        $this->assertNull($mock->aMethod());
    }

    function testCloningWithExtraMethod() {
        $mock = &new MockDummyWithExtraMethods();
        $this->assertTrue(method_exists($mock, "extraMethod"));
    }

    function testCloningWithChosenClassName() {
        $mock = &new AnotherMockDummy();
        $this->assertTrue(method_exists($mock, "aMethod"));
    }
}

class TestOfMockReturns extends UnitTestCase {

    function testDefaultReturn() {
        $mock = &new MockDummy();
        $mock->setReturnValue("aMethod", "aaa");
        $this->assertIdentical($mock->aMethod(), "aaa");
        $this->assertIdentical($mock->aMethod(), "aaa");
    }

    function testParameteredReturn() {
        $mock = &new MockDummy();
        $mock->setReturnValue('aMethod', 'aaa', array(1, 2, 3));
        $this->assertNull($mock->aMethod());
        $this->assertIdentical($mock->aMethod(1, 2, 3), 'aaa');
    }

    function testReferenceReturned() {
        $mock = &new MockDummy();
        $object = new Dummy();
        $mock->setReturnReference('aMethod', $object, array(1, 2, 3));
        $this->assertReference($zref = &$mock->aMethod(1, 2, 3), $object);
    }

    function testPatternMatchReturn() {
        $mock = &new MockDummy();
        $mock->setReturnValue(
                "aMethod",
                "aaa",
                array(new PatternExpectation('/hello/i')));
        $this->assertIdentical($mock->aMethod('Hello'), "aaa");
        $this->assertNull($mock->aMethod('Goodbye'));
    }

    function testMultipleMethods() {
        $mock = &new MockDummy();
        $mock->setReturnValue("aMethod", 100, array(1));
        $mock->setReturnValue("aMethod", 200, array(2));
        $mock->setReturnValue("anotherMethod", 10, array(1));
        $mock->setReturnValue("anotherMethod", 20, array(2));
        $this->assertIdentical($mock->aMethod(1), 100);
        $this->assertIdentical($mock->anotherMethod(1), 10);
        $this->assertIdentical($mock->aMethod(2), 200);
        $this->assertIdentical($mock->anotherMethod(2), 20);
    }

    function testReturnSequence() {
        $mock = &new MockDummy();
        $mock->setReturnValueAt(0, "aMethod", "aaa");
        $mock->setReturnValueAt(1, "aMethod", "bbb");
        $mock->setReturnValueAt(3, "aMethod", "ddd");
        $this->assertIdentical($mock->aMethod(), "aaa");
        $this->assertIdentical($mock->aMethod(), "bbb");
        $this->assertNull($mock->aMethod());
        $this->assertIdentical($mock->aMethod(), "ddd");
    }

    function testReturnReferenceSequence() {
        $mock = &new MockDummy();
        $object = new Dummy();
        $mock->setReturnReferenceAt(1, "aMethod", $object);
        $this->assertNull($mock->aMethod());
        $this->assertReference($zref =& $mock->aMethod(), $object);
        $this->assertNull($mock->aMethod());
    }

    function testComplicatedReturnSequence() {
        $mock = &new MockDummy();
        $object = new Dummy();
        $mock->setReturnValueAt(1, "aMethod", "aaa", array("a"));
        $mock->setReturnValueAt(1, "aMethod", "bbb");
        $mock->setReturnReferenceAt(2, "aMethod", $object, array('*', 2));
        $mock->setReturnValueAt(2, "aMethod", "value", array('*', 3));
        $mock->setReturnValue("aMethod", 3, array(3));
        $this->assertNull($mock->aMethod());
        $this->assertEqual($mock->aMethod("a"), "aaa");
        $this->assertReference($zref =& $mock->aMethod(1, 2), $object);
        $this->assertEqual($mock->aMethod(3), 3);
        $this->assertNull($mock->aMethod());
    }

    function testMultipleMethodSequences() {
        $mock = &new MockDummy();
        $mock->setReturnValueAt(0, "aMethod", "aaa");
        $mock->setReturnValueAt(1, "aMethod", "bbb");
        $mock->setReturnValueAt(0, "anotherMethod", "ccc");
        $mock->setReturnValueAt(1, "anotherMethod", "ddd");
        $this->assertIdentical($mock->aMethod(), "aaa");
        $this->assertIdentical($mock->anotherMethod(), "ccc");
        $this->assertIdentical($mock->aMethod(), "bbb");
        $this->assertIdentical($mock->anotherMethod(), "ddd");
    }

    function testSequenceFallback() {
        $mock = &new MockDummy();
        $mock->setReturnValueAt(0, "aMethod", "aaa", array('a'));
        $mock->setReturnValueAt(1, "aMethod", "bbb", array('a'));
        $mock->setReturnValue("aMethod", "AAA");
        $this->assertIdentical($mock->aMethod('a'), "aaa");
        $this->assertIdentical($mock->aMethod('b'), "AAA");
    }

    function testMethodInterference() {
        $mock = &new MockDummy();
        $mock->setReturnValueAt(0, "anotherMethod", "aaa");
        $mock->setReturnValue("aMethod", "AAA");
        $this->assertIdentical($mock->aMethod(), "AAA");
        $this->assertIdentical($mock->anotherMethod(), "aaa");
    }
}

class TestOfMockExpectationsThatPass extends UnitTestCase {

    function testAnyArgument() {
        $mock = &new MockDummy();
        $mock->expect('aMethod', array('*'));
        $mock->aMethod(1);
        $mock->aMethod('hello');
    }

    function testAnyTwoArguments() {
        $mock = &new MockDummy();
        $mock->expect('aMethod', array('*', '*'));
        $mock->aMethod(1, 2);
    }

    function testSpecificArgument() {
        $mock = &new MockDummy();
        $mock->expect('aMethod', array(1));
        $mock->aMethod(1);
    }

    function testExpectation() {
        $mock = &new MockDummy();
        $mock->expect('aMethod', array(new IsAExpectation('Dummy')));
        $mock->aMethod(new Dummy());
    }

    function testArgumentsInSequence() {
        $mock = &new MockDummy();
        $mock->expectAt(0, 'aMethod', array(1, 2));
        $mock->expectAt(1, 'aMethod', array(3, 4));
        $mock->aMethod(1, 2);
        $mock->aMethod(3, 4);
    }

    function testAtLeastOnceSatisfiedByOneCall() {
        $mock = &new MockDummy();
        $mock->expectAtLeastOnce('aMethod');
        $mock->aMethod();
    }

    function testAtLeastOnceSatisfiedByTwoCalls() {
        $mock = &new MockDummy();
        $mock->expectAtLeastOnce('aMethod');
        $mock->aMethod();
        $mock->aMethod();
    }

    function testOnceSatisfiedByOneCall() {
        $mock = &new MockDummy();
        $mock->expectOnce('aMethod');
        $mock->aMethod();
    }

    function testMinimumCallsSatisfiedByEnoughCalls() {
        $mock = &new MockDummy();
        $mock->expectMinimumCallCount('aMethod', 1);
        $mock->aMethod();
    }

    function testMinimumCallsSatisfiedByTooManyCalls() {
        $mock = &new MockDummy();
        $mock->expectMinimumCallCount('aMethod', 3);
        $mock->aMethod();
        $mock->aMethod();
        $mock->aMethod();
        $mock->aMethod();
    }

    function testMaximumCallsSatisfiedByEnoughCalls() {
        $mock = &new MockDummy();
        $mock->expectMaximumCallCount('aMethod', 1);
        $mock->aMethod();
    }

    function testMaximumCallsSatisfiedByNoCalls() {
        $mock = &new MockDummy();
        $mock->expectMaximumCallCount('aMethod', 1);
    }
}

class MockWithInjectedTestCase extends SimpleMock {
    function &_getCurrentTestCase() {
        $context = &SimpleTest::getContext();
        $test = &$context->getTest();
        return $test->getMockedTest();
    }
}
SimpleTest::setMockBaseClass('MockWithInjectedTestCase');
Mock::generate('Dummy', 'MockDummyWithInjectedTestCase');
SimpleTest::setMockBaseClass('SimpleMock');
Mock::generate('SimpleTestCase');

class LikeExpectation extends IdenticalExpectation {
    function LikeExpectation($expectation) {
        $expectation->_message = '';
        $this->IdenticalExpectation($expectation);
    }

    function test($compare) {
        $compare->_message = '';
        return parent::test($compare);
    }

    function testMessage($compare) {
        $compare->_message = '';
        return parent::testMessage($compare);
    }
}

class TestOfMockExpectations extends UnitTestCase {
    var $test;

    function setUp() {
        $this->test = &new MockSimpleTestCase();
    }

    function &getMockedTest() {
        return $this->test;
    }

    function testSettingExpectationOnNonMethodThrowsError() {
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectMaximumCallCount('aMissingMethod', 2);
        $this->assertError();
    }

    function testMaxCallsDetectsOverrun() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new MaximumCallCountExpectation('aMethod', 2)),
                3));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectMaximumCallCount('aMethod', 2);
        $mock->aMethod();
        $mock->aMethod();
        $mock->aMethod();
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testTallyOnMaxCallsSendsPassOnUnderrun() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new MaximumCallCountExpectation('aMethod', 2)),
                2));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectMaximumCallCount("aMethod", 2);
        $mock->aMethod();
        $mock->aMethod();
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testExpectNeverDetectsOverrun() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new MaximumCallCountExpectation('aMethod', 0)),
                1));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectNever('aMethod');
        $mock->aMethod();
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testTallyOnExpectNeverStillSendsPassOnUnderrun() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new MaximumCallCountExpectation('aMethod', 0)),
                0));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectNever('aMethod');
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testMinCalls() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new MinimumCallCountExpectation('aMethod', 2)),
                2));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectMinimumCallCount('aMethod', 2);
        $mock->aMethod();
        $mock->aMethod();
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testFailedNever() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new MaximumCallCountExpectation('aMethod', 0)),
                1));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectNever('aMethod');
        $mock->aMethod();
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testUnderOnce() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new CallCountExpectation('aMethod', 1)),
                0));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectOnce('aMethod');
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testOverOnce() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new CallCountExpectation('aMethod', 1)),
                2));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectOnce('aMethod');
        $mock->aMethod();
        $mock->aMethod();
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testUnderAtLeastOnce() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new MinimumCallCountExpectation('aMethod', 1)),
                0));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectAtLeastOnce("aMethod");
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testZeroArguments() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new ParametersExpectation(array())),
                array(),
                '*'));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expect("aMethod", array());
        $mock->aMethod();
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testExpectedArguments() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new ParametersExpectation(array(1, 2, 3))),
                array(1, 2, 3),
                '*'));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expect('aMethod', array(1, 2, 3));
        $mock->aMethod(1, 2, 3);
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testFailedArguments() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new ParametersExpectation(array('this'))),
                array('that'),
                '*'));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expect('aMethod', array('this'));
        $mock->aMethod('that');
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testWildcardsAreTranslatedToAnythingExpectations() {
        $this->test->expectOnce('assert', array(
                new LikeExpectation(new ParametersExpectation(array(
                            new AnythingExpectation(), 123, new AnythingExpectation()))),
                array(100, 123, 101),
                '*'));
        $mock = &new MockDummyWithInjectedTestCase($this);
        $mock->expect("aMethod", array('*', 123, '*'));
        $mock->aMethod(100, 123, 101);
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testSpecificPassingSequence() {
        $this->test->expectAt(0, 'assert', array(
                new LikeExpectation(new ParametersExpectation(array(1, 2, 3))),
                array(1, 2, 3),
                '*'));
        $this->test->expectAt(1, 'assert', array(
                new LikeExpectation(new ParametersExpectation(array('Hello'))),
                array('Hello'),
                '*'));
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expectAt(1, 'aMethod', array(1, 2, 3));
        $mock->expectAt(2, 'aMethod', array('Hello'));
        $mock->aMethod();
        $mock->aMethod(1, 2, 3);
        $mock->aMethod('Hello');
        $mock->aMethod();
        $mock->_mock->atTestEnd('testSomething', $this->test);
    }

    function testNonArrayForExpectedParametersGivesError() {
        $mock = &new MockDummyWithInjectedTestCase();
        $mock->expect("aMethod", "foo");
        $this->assertErrorPattern('/\$args.*not an array/i');
        $mock->aMethod();
        $mock->tally();
        $mock->_mock->atTestEnd('testSomething', $this->test);
   }
}

class TestOfMockComparisons extends UnitTestCase {

    function testEqualComparisonOfMocksDoesNotCrash() {
        $expectation = &new EqualExpectation(new MockDummy());
        $this->assertTrue($expectation->test(new MockDummy(), true));
    }

    function testIdenticalComparisonOfMocksDoesNotCrash() {
        $expectation = &new IdenticalExpectation(new MockDummy());
        $this->assertTrue($expectation->test(new MockDummy()));
    }
}

class ClassWithSpecialMethods {
    function __get($name) { }
    function __set($name, $value) { }
    function __isset($name) { }
    function __unset($name) { }
    function __call($method, $arguments) { }
    function __toString() { }
}
Mock::generate('ClassWithSpecialMethods');

class TestOfSpecialMethods extends UnitTestCase {
    function skip() {
        $this->skipIf(version_compare(phpversion(), '5', '<='), 'Overloading not tested for PHP 4');
    }

    function testCanMockTheThingAtAll() {
        $mock = new MockClassWithSpecialMethods();
    }

    function testReturnFromSpecialAccessor() {
        $mock = &new MockClassWithSpecialMethods();
        $mock->setReturnValue('__get', '1st Return', array('first'));
        $mock->setReturnValue('__get', '2nd Return', array('second'));
        $this->assertEqual($mock->first, '1st Return');
        $this->assertEqual($mock->second, '2nd Return');
    }

    function testcanExpectTheSettingOfValue() {
        $mock = &new MockClassWithSpecialMethods();
        $mock->expectOnce('__set', array('a', 'A'));
        $mock->a = 'A';
    }

    function testCanSimulateAnOverloadmethod() {
        $mock = &new MockClassWithSpecialMethods();
        $mock->expectOnce('__call', array('amOverloaded', array('A')));
        $mock->setReturnValue('__call', 'aaa');
        $this->assertIdentical($mock->amOverloaded('A'), 'aaa');
    }

    function testCanEmulateIsset() {
        $mock = &new MockClassWithSpecialMethods();
        $mock->setReturnValue('__isset', true);
        $this->assertIdentical(isset($mock->a), true);
    }

    function testCanExpectUnset() {
        $mock = &new MockClassWithSpecialMethods();
        $mock->expectOnce('__unset', array('a'));
        unset($mock->a);
    }

    function testToStringMagic() {
        $mock = &new MockClassWithSpecialMethods();
        $mock->expectOnce('__toString');
        $mock->setReturnValue('__toString', 'AAA');
        ob_start();
        print $mock;
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEqual($output, 'AAA');
    }
}

if (version_compare(phpversion(), '5', '>=')) {
    $class  = 'class WithStaticMethod { ';
    $class .= '    static function aStaticMethod() { } ';
    $class .= '}';
    eval($class);
}
Mock::generate('WithStaticMethod');

class TestOfMockingClassesWithStaticMethods extends UnitTestCase {
    function skip() {
        $this->skipUnless(version_compare(phpversion(), '5', '>='));
    }
    
    function testStaticMethodIsMockedAsStatic() {
        $mock = new WithStaticMethod();
        $reflection = new ReflectionClass($mock);
        $method = $reflection->getMethod('aStaticMethod');
        $this->assertTrue($method->isStatic());
    }
}

Mock::generatePartial('Dummy', 'TestDummy', array('anotherMethod'));

class TestOfPartialMocks extends UnitTestCase {

    function testMethodReplacementWithNoBehaviourReturnsNull() {
        $mock = &new TestDummy();
        $this->assertEqual($mock->aMethod(99), 99);
        $this->assertNull($mock->anotherMethod());
    }

    function testSettingReturns() {
        $mock = &new TestDummy();
        $mock->setReturnValue('anotherMethod', 33, array(3));
        $mock->setReturnValue('anotherMethod', 22);
        $mock->setReturnValueAt(2, 'anotherMethod', 44, array(3));
        $this->assertEqual($mock->anotherMethod(), 22);
        $this->assertEqual($mock->anotherMethod(3), 33);
        $this->assertEqual($mock->anotherMethod(3), 44);
    }

    function testReferences() {
        $mock = &new TestDummy();
        $object = new Dummy();
        $mock->setReturnReferenceAt(0, 'anotherMethod', $object, array(3));
        $this->assertReference($zref =& $mock->anotherMethod(3), $object);
    }

    function testExpectations() {
        $mock = &new TestDummy();
        $mock->expectCallCount('anotherMethod', 2);
        $mock->expect('anotherMethod', array(77));
        $mock->expectAt(1, 'anotherMethod', array(66));
        $mock->anotherMethod(77);
        $mock->anotherMethod(66);
    }

    function testSettingExpectationOnMissingMethodThrowsError() {
        $mock = &new TestDummy();
        $mock->expectCallCount('aMissingMethod', 2);
        $this->assertError();
    }
}

class ConstructorSuperClass {
    function ConstructorSuperClass() { }
}

class ConstructorSubClass extends ConstructorSuperClass {
}

class TestOfPHP4StyleSuperClassConstruct extends UnitTestCase {
    /*
     * This addresses issue #1231401.  Without the fix in place, this will
     * generate a fatal PHP error.
     */
    function testBasicConstruct() {
        Mock::generate('ConstructorSubClass');
        $mock = &new MockConstructorSubClass();
        $this->assertIsA($mock, 'ConstructorSubClass');
        $this->assertTrue(method_exists($mock, 'ConstructorSuperClass'));
    }
}

class TestOfPHP5StaticMethodMocking extends UnitTestCase {
    function skip() {
        $this->skipIf(version_compare(phpversion(), '5', '<='), 'Static methods not tested in PHP 4');
    }

    function testCanCreateAMockObjectWithStaticMethodsWithoutError() {
        eval('
            class SimpleObjectContainingStaticMethod {
                static function someStatic() { }
            }
        ');

        Mock::generate('SimpleObjectContainingStaticMethod');
        $this->assertNoErrors();
    }
}

class TestOfPHP5AbstractMethodMocking extends UnitTestCase {
    function skip() {
        $this->skipIf(version_compare(phpversion(), '5', '<='), 'Abstract class/methods not tested in PHP 4');
    }

    function testCanCreateAMockObjectFromAnAbstractWithProperFunctionDeclarations() {
        eval('
             abstract class SimpleAbstractClassContainingAbstractMethods {
                abstract function anAbstract();
                abstract function anAbstractWithParameter($foo);
                abstract function anAbstractWithMultipleParameters($foo, $bar);
            }
        ');

        Mock::generate('SimpleAbstractClassContainingAbstractMethods');
        $this->assertNoErrors();

        $this->assertTrue(
            method_exists(
                'MockSimpleAbstractClassContainingAbstractMethods',
                'anAbstract'
            )
        );
        $this->assertTrue(
            method_exists(
                'MockSimpleAbstractClassContainingAbstractMethods',
                'anAbstractWithParameter'
            )
        );
        $this->assertTrue(
            method_exists(
                'MockSimpleAbstractClassContainingAbstractMethods',
                'anAbstractWithMultipleParameters'
            )
        );
    }

    function testMethodsDefinedAsAbstractInParentShouldHaveFullSignature() {
        eval('
             abstract class SimpleParentAbstractClassContainingAbstractMethods {
                abstract function anAbstract();
                abstract function anAbstractWithParameter($foo);
                abstract function anAbstractWithMultipleParameters($foo, $bar);
            }

             class SimpleChildAbstractClassContainingAbstractMethods extends SimpleParentAbstractClassContainingAbstractMethods {
                function anAbstract(){}
                function anAbstractWithParameter($foo){}
                function anAbstractWithMultipleParameters($foo, $bar){}
            }
        ');

        Mock::generate('SimpleChildAbstractClassContainingAbstractMethods');
        $this->assertNoErrors();

        $this->assertTrue(
            method_exists(
                'MockSimpleChildAbstractClassContainingAbstractMethods',
                'anAbstract'
            )
        );
        $this->assertTrue(
            method_exists(
                'MockSimpleChildAbstractClassContainingAbstractMethods',
                'anAbstractWithParameter'
            )
        );
        $this->assertTrue(
            method_exists(
                'MockSimpleChildAbstractClassContainingAbstractMethods',
                'anAbstractWithMultipleParameters'
            )
        );
    }
}

?>