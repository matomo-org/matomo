<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {debug} function plugin
 *
 * Type:     function<br>
 * Name:     debug<br>
 * Date:     July 1, 2002<br>
 * Purpose:  popup debug window
 * @link http://smarty.php.net/manual/en/language.function.debug.php {debug}
 *       (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string output from {@link Smarty::_generate_debug_output()}
 */
function smarty_function_debug($params, &$smarty)
{
    if (isset($params['output'])) {
        $smarty->assign('_smarty_debug_output', $params['output']);
    }
    require_once(SMARTY_CORE_DIR . 'core.display_debug_console.php');
    return smarty_core_display_debug_console(null, $smarty);
}

/* vim: set expandtab: */

?>
