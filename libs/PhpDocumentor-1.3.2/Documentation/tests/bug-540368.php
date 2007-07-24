<?php
/** @package tests */
/** @package tests */
class summary_form
{
	var $dp;
	/**
	*
	* @see function blah
	*/
	function get_header2($atate)
	{
		$header2 .= '\'>First Page</a></td>';
	}
	// confuses parser into thinking that the 2nd function blah() is a member of class summary_form
	function blah()
	{
	}
}
function blah()
{
}
?>
