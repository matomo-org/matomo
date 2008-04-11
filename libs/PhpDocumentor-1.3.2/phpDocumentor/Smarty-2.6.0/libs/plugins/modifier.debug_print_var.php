<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty debug_print_var modifier plugin
 *
 * Type:     modifier<br>
 * Name:     debug_print_var<br>
 * Purpose:  formats variable contents for display in the console
 * @link http://smarty.php.net/manual/en/language.modifier.debug.print.var.php
 *          debug_print_var (Smarty online manual)
 * @param array|object
 * @param integer
 * @param integer
 * @return string
 */
function smarty_modifier_debug_print_var($var, $depth = 0, $length = 40)
{
	$_replace = array("\n"=>'<i>&#92;n</i>', "\r"=>'<i>&#92;r</i>', "\t"=>'<i>&#92;t</i>');
    if (is_array($var)) {
        $results = "<b>Array (".count($var).")</b>";
        foreach ($var as $curr_key => $curr_val) {
            $return = smarty_modifier_debug_print_var($curr_val, $depth+1, $length);
            $results .= "<br>".str_repeat('&nbsp;', $depth*2)."<b>".strtr($curr_key, $_replace)."</b> =&gt; $return";
        }
        return $results;
    } else if (is_object($var)) {
        $object_vars = get_object_vars($var);
        $results = "<b>".get_class($var)." Object (".count($object_vars).")</b>";
        foreach ($object_vars as $curr_key => $curr_val) {
            $return = smarty_modifier_debug_print_var($curr_val, $depth+1, $length);
            $results .= "<br>".str_repeat('&nbsp;', $depth*2)."<b>$curr_key</b> =&gt; $return";
        }
        return $results;
    } else {
        if (empty($var) && $var != "0") {
            return '<i>empty</i>';
        }
        if (strlen($var) > $length ) {
            $results = substr($var, 0, $length-3).'...';
        } else {
            $results = $var;
        }
        $results = htmlspecialchars($results);
        $results = strtr($results, $_replace);
        return $results;
    }
}

/* vim: set expandtab: */

?>
