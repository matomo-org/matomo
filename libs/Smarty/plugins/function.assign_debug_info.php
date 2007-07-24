<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {assign_debug_info} function plugin
 *
 * Type:     function<br>
 * Name:     assign_debug_info<br>
 * Purpose:  assign debug info to the template<br>
 * @author Monte Ohrt <monte at ohrt dot com>
 * @param array unused in this plugin, this plugin uses {@link Smarty::$_config},
 *              {@link Smarty::$_tpl_vars} and {@link Smarty::$_smarty_debug_info}
 * @param Smarty
 */
function smarty_function_assign_debug_info($params, &$smarty)
{
    $assigned_vars = $smarty->_tpl_vars;
    ksort($assigned_vars);
    if (@is_array($smarty->_config[0])) {
        $config_vars = $smarty->_config[0];
        ksort($config_vars);
        $smarty->assign("_debug_config_keys", array_keys($config_vars));
        $smarty->assign("_debug_config_vals", array_values($config_vars));
    }
    
    $included_templates = $smarty->_smarty_debug_info;
    
    $smarty->assign("_debug_keys", array_keys($assigned_vars));
    $smarty->assign("_debug_vals", array_values($assigned_vars));
    
    $smarty->assign("_debug_tpls", $included_templates);
}

/* vim: set expandtab: */

?>
