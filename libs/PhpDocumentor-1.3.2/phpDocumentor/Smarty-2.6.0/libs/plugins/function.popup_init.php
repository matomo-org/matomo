<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {popup_init} function plugin
 *
 * Type:     function<br>
 * Name:     popup_init<br>
 * Purpose:  initialize overlib
 * @link http://smarty.php.net/manual/en/language.function.popup.init.php {popup_init}
 *          (Smarty online manual)
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_popup_init($params, &$smarty)
{
	$zindex = 1000;
	
    if (!empty($params['zindex'])) {
		$zindex = $params['zindex'];
	}
	
    if (!empty($params['src'])) {
    	return '<div id="overDiv" style="position:absolute; visibility:hidden; z-index:'.$zindex.';"></div>' . "\n"
         . '<script type="text/javascript" language="JavaScript" src="'.$params['src'].'"></script>' . "\n";
    } else {
        $smarty->trigger_error("popup_init: missing src parameter");
    }
}

/* vim: set expandtab: */

?>
