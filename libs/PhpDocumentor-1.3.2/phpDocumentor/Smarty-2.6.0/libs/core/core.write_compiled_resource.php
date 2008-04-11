<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * write the compiled resource
 *
 * @param string $compile_path
 * @param string $compiled_content
 * @param integer $resource_timestamp
 * @return true
 */
function smarty_core_write_compiled_resource($params, &$smarty)
{
    if(!@is_writable($smarty->compile_dir)) {
        // compile_dir not writable, see if it exists
        if(!@is_dir($smarty->compile_dir)) {
            $smarty->trigger_error('the $compile_dir \'' . $smarty->compile_dir . '\' does not exist, or is not a directory.', E_USER_ERROR);
            return false;
        }
        $smarty->trigger_error('unable to write to $compile_dir \'' . realpath($smarty->compile_dir) . '\'. Be sure $compile_dir is writable by the web server user.', E_USER_ERROR);
        return false;
    }

    $_params = array('filename' => $params['compile_path'], 'contents' => $params['compiled_content'], 'create_dirs' => true);
    require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.write_file.php');
    smarty_core_write_file($_params, $smarty);
    touch($params['compile_path'], $params['resource_timestamp']);
    return true;
}

/* vim: set expandtab: */

?>
