<?php
/**
* @package tests
*/
/**
* Base Class
*
* @package	tests
* @subpackage	test1
*/
class bug_556894_base
{
/**
* I'm a test var
*/
var $test;

/**
* I'm a test method
*/
function test()
{
}
}

/**
* Subclass in same subpackage
*
* @package	tests
* @subpackage	test1
*/
class bug_556894_sub1 extends bug_556894_base
{
}

/**
* Subclass in different subpackage
*
* @package	tests
* @subpackage	test2
*/
class bug_556894_sub2 extends bug_556894_base
{
}
?>
