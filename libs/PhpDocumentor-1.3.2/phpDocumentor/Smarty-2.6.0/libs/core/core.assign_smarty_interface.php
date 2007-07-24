<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty assign_smarty_interface core plugin
 *
 * Type:     core<br>
 * Name:     assign_smarty_interface<br>
 * Purpose:  assign the $smarty interface variable
 * @param array Format: null
 * @param Smarty
 */
function smarty_core_assign_smarty_interface($params, &$smarty)
{
        if (isset($smarty->_smarty_vars) && isset($smarty->_smarty_vars['request'])) {
            return;
        }

        $_globals_map = array('g'  => 'HTTP_GET_VARS',
                             'p'  => 'HTTP_POST_VARS',
                             'c'  => 'HTTP_COOKIE_VARS',
                             's'  => 'HTTP_SERVER_VARS',
                             'e'  => 'HTTP_ENV_VARS');

        $_smarty_vars_request  = array();

        foreach (preg_split('!!', strtolower($smarty->request_vars_order)) as $_c) {
            if (isset($_globals_map[$_c])) {
                $_smarty_vars_request = array_merge($_smarty_vars_request, $GLOBALS[$_globals_map[$_c]]);
            }
        }
        $_smarty_vars_request = @array_merge($_smarty_vars_request, $GLOBALS['HTTP_SESSION_VARS']);

        $smarty->_smarty_vars['request'] = $_smarty_vars_request;
}

/* vim: set expandtab: */

?>
