<?php
/**
* inherited functions with @access private should not be shown in inherited list of child
* @package tests
*/
class parent_b587733
{
/**
* displayed in inherited list?
* @access private
*/
var $v1;
/**
* displayed in inherited list?
* @access private
*/
function b1()
{
	global $doitwork;
}
}
/**
* @package tests
*/
class kiddie_b587733 extends parent_b587733
{
	function kiddie_b587733() {}
}
?>