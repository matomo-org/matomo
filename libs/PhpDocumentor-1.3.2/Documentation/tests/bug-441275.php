<?php
/** @package tests */
/**
* This makes sure the basic element of bug 44127 is fixed
*/
function test_441275($foo = array())
{
}

/**
* This tests some more advanced cases to make sure to handle them
*/
function test2_441275($foo = array("item1","item2",'item3' => "item4",array("item5","item6"), 'item7' => 
			array('item8','item9',"array('item9','item10')")), $foobar)
{
}

?>
