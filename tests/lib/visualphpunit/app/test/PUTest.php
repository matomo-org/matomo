<?php

class PUTest extends PHPUnit_Framework_TestCase {
    public function test_this() {
        $key = 'test';
        $value = 'value';
        print_r('some stuff');
        $this->assertEquals($key, $value, 'test_this() failed!');
    }

    public function test_this_too() {
        somestr;
        $key = 'test';
        $value = 'value';
        $this->assertEquals($key, $value, 'test_this_too() failed!');
    }
}

?>
