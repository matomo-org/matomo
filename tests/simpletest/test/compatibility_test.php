<?php
// $Id: compatibility_test.php,v 1.4 2007/04/30 23:39:59 lastcraft Exp $
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../compatibility.php');

class ComparisonClass {
}

class ComparisonSubclass extends ComparisonClass {
}

if (version_compare(phpversion(), '5') >= 0) {
    eval('interface ComparisonInterface { }');
    eval('class ComparisonClassWithInterface implements ComparisonInterface { }');
}

class TestOfCompatibility extends UnitTestCase {
    
    function testIsA() {
        $this->assertTrue(SimpleTestCompatibility::isA(
                new ComparisonClass(),
                'ComparisonClass'));
        $this->assertFalse(SimpleTestCompatibility::isA(
                new ComparisonClass(),
                'ComparisonSubclass'));
        $this->assertTrue(SimpleTestCompatibility::isA(
                new ComparisonSubclass(),
                'ComparisonClass'));
    }
    
    function testIdentityOfNumericStrings() {
        $numericString1 = "123";
        $numericString2 = "00123";
        $this->assertNotIdentical($numericString1, $numericString2);
    }
    
    function testIdentityOfObjects() {
        $object1 = new ComparisonClass();
        $object2 = new ComparisonClass();
        $this->assertIdentical($object1, $object2);
    }
    
    function testReferences () {
        $thing = "Hello";
        $thing_reference = &$thing;
        $thing_copy = $thing;
        $this->assertTrue(SimpleTestCompatibility::isReference(
                $thing,
                $thing));
        $this->assertTrue(SimpleTestCompatibility::isReference(
                $thing,
                $thing_reference));
        $this->assertFalse(SimpleTestCompatibility::isReference(
                $thing,
                $thing_copy));
    }
    
    function testObjectReferences () {
        $object = &new ComparisonClass();
        $object_reference = &$object;
        $object_copy = new ComparisonClass();
        $object_assignment = $object;
        $this->assertTrue(SimpleTestCompatibility::isReference(
                $object,
                $object));
        $this->assertTrue(SimpleTestCompatibility::isReference(
                $object,
                $object_reference));
        $this->assertFalse(SimpleTestCompatibility::isReference(
                $object,
                $object_copy));
        if (version_compare(phpversion(), '5', '>=')) {
            $this->assertTrue(SimpleTestCompatibility::isReference(
                    $object,
                    $object_assignment));
        } else {
            $this->assertFalse(SimpleTestCompatibility::isReference(
                    $object,
                    $object_assignment));
        }
    }
    
    function testInteraceComparison() {
        if (version_compare(phpversion(), '5', '<')) {
            return;
        }
        
        $object = new ComparisonClassWithInterface();
        $this->assertFalse(SimpleTestCompatibility::isA(
                new ComparisonClass(),
                'ComparisonInterface'));
        $this->assertTrue(SimpleTestCompatibility::isA(
                new ComparisonClassWithInterface(),
                'ComparisonInterface'));
    }
}
?>