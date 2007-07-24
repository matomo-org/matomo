<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty regex_replace modifier plugin
 *
 * Type:     modifier<br>
 * Name:     regex_replace<br>
 * Purpose:  regular epxression search/replace
 * @link http://smarty.php.net/manual/en/language.modifier.regex.replace.php
 *          regex_replace (Smarty online manual)
 * @param string
 * @param string|array
 * @param string|array
 * @return string
 */
function smarty_modifier_regex_replace($string, $search, $replace)
{
    return preg_replace($search, $replace, $string);
}

/* vim: set expandtab: */

?>
