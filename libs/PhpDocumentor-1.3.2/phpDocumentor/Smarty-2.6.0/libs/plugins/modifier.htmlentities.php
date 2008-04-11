<?php
/** @package Smarty
* @subpackage plugins */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     upper
 * Purpose:  convert string to uppercase
 * -------------------------------------------------------------
 */
function smarty_modifier_htmlentities($string)
{
	return htmlentities($string);
}

?>
