<?php
/**
* @package tests
*/
/**
* !
*/
define('thisisdumbbutworks', 'I' . /* "shouldn't " */ 'parse this');

/**
* @package tests
*/
class testme
{
	// var $dontshow;
	# var $oldfashioneddontshow;
	var /* $notme */ $me = array('item1' => 2,
	#						'NOTME' => hahaha,
	//						'MENEITHER' => oops,
							'item2' => 3);
}

/**
* @package tests
*/
class metoo
{
	var $mine = // not this
	'but this';
}
?>