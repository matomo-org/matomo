<?php
 
class SkippedTest extends PHPUnit_Framework_TestCase {
    protected function setUp() {
        if (!extension_loaded('something_bogus')) {
            $this->markTestSkipped('The something_bogus extension is not available.');
        }
    }

    public function test_something_else() {
        $key = 'test';
        $value = 'value';
        print_r('some stuff');
        $this->assertEquals($key, $value, 'test_something_else() failed!');
    }
}

?>
