<?php
/** @package Smarty
* @subpackage plugins */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     rawurlencode
 * Purpose:  encode string for use in PDFdefaultConverter TOC
 * -------------------------------------------------------------
 */
function smarty_modifier_rawurlencode($string)
{
	return rawurlencode($string);
}

?>
