<?php
 
class IncompleteTest extends PHPUnit_Framework_TestCase {
    public function test_something() {
        // Optional: Test anything here, if you want.
        $this->assertTrue(TRUE, 'This should already work.');
 
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function test_something_else() {
        $key = 'test';
        $value = 'test';
        print_r('some stuff');
        $this->assertEquals($key, $value, 'test_something_else() failed!');
    }
}

?>
