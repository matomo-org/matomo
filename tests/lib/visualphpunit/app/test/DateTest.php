<?php

class DateTest extends PHPUnit_Framework_TestCase {    
    public function test_this() {
        $key = 'test';
        $value = 'test';
        $this->assertEquals($key, $value, 'test_this() failed!');
    }

    public function test_this_too() {
        $key = 'test';
        $value = 'test';
        print_r('foo { breaks: this } bar');
        $this->assertEquals($key, $value, 'test_this_too() failed!');
    }
}

?>
