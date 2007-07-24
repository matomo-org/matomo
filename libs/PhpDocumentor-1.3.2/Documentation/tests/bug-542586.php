<?php
/** @package tests */
/**  testie */
define("testie",1);

/**
* @access private
*/
define('testoe2',2);

function func()
{
}

/**
* @access private
*/
function funie()
{
}

/** @package tests */
class ctest
{
	var $t1;
	/** @access private */
	var $t2;
	var $t3;
	function ctest()
	{
	}
	
	/** @access private */
	function atest()
	{
	}
	
	function btest()
	{
	}
}

/** @package tests */
class priv1{}

/** @access private */
class priv extends priv1
{
	var $privvar;
	
	function privfunction()
	{
	}
}

/** what happens here?  testing bug 564135
 * @package tests */
class childofpriv extends priv
{
}
?>
