<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty debug_console function plugin
 *
 * Type:     core<br>
 * Name:     display_debug_console<br>
 * Purpose:  display the javascript debug console window
 * @param array Format: null
 * @param Smarty
 */
function smarty_core_display_debug_console($params, &$smarty)
{
    // we must force compile the debug template in case the environment
    // changed between separate applications.

    if(empty($smarty->debug_tpl)) {
        // set path to debug template from SMARTY_DIR
        $smarty->debug_tpl = SMARTY_DIR . 'debug.tpl';
        if($smarty->security && is_file($smarty->debug_tpl)) {
            $smarty->secure_dir[] = dirname(realpath($smarty->debug_tpl));
        }
    }

    $_ldelim_orig = $smarty->left_delimiter;
    $_rdelim_orig = $smarty->right_delimiter;

    $smarty->left_delimiter = '{';
    $smarty->right_delimiter = '}';

    $_compile_id_orig = $smarty->_compile_id;
    $smarty->_compile_id = null;

    $_compile_path = $smarty->_get_compile_path($smarty->debug_tpl);
    if ($smarty->_compile_resource($smarty->debug_tpl, $_compile_path))
    {
        ob_start();
        $smarty->_include($_compile_path);
        $_results = ob_get_contents();
        ob_end_clean();
    } else {
        $_results = '';
    }

    $smarty->_compile_id = $_compile_id_orig;

    $smarty->left_delimiter = $_ldelim_orig;
    $smarty->right_delimiter = $_rdelim_orig;

    return $_results;
}

/* vim: set expandtab: */

?>
