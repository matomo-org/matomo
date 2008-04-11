<?php
/** @package Smarty
* @subpackage plugins */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     assign
 * Purpose:  assign a value to a template variable
 * -------------------------------------------------------------
 */
function smarty_function_var_dump($params, &$smarty)
{
    var_dump('<pre>',$params,'</pre>');
}

/* vim: set expandtab: */

?>
