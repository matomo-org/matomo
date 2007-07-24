<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Prepend the cache information to the cache file
 * and write it
 *
 * @param string $tpl_file
 * @param string $cache_id
 * @param string $compile_id
 * @param string $results
 * @return true|null
 */

 // $tpl_file, $cache_id, $compile_id, $results

function smarty_core_write_cache_file($params, &$smarty)
{

    // put timestamp in cache header
    $smarty->_cache_info['timestamp'] = time();
    if ($smarty->cache_lifetime > -1){
        // expiration set
        $smarty->_cache_info['expires'] = $smarty->_cache_info['timestamp'] + $smarty->cache_lifetime;
    } else {
        // cache will never expire
        $smarty->_cache_info['expires'] = -1;
    }

    // collapse {nocache...}-tags
    $params['results'] = preg_replace('!((\{nocache\:([0-9a-f]{32})#(\d+)\})'
                                      .'.*'
                                      .'{/nocache\:\\3#\\4\})!Us'
                                      ,'\\2'
                                      ,$params['results']);
    $smarty->_cache_info['cache_serials'] = $smarty->_cache_serials;

    // prepend the cache header info into cache file
    $params['results'] = serialize($smarty->_cache_info)."\n".$params['results'];

    if (!empty($smarty->cache_handler_func)) {
        // use cache_handler function
        call_user_func_array($smarty->cache_handler_func,
                             array('write', &$smarty, &$params['results'], $params['tpl_file'], $params['cache_id'], $params['compile_id'], null));
    } else {
        // use local cache file

        if(!@is_writable($smarty->cache_dir)) {
            // cache_dir not writable, see if it exists
            if(!@is_dir($smarty->cache_dir)) {
                $smarty->trigger_error('the $cache_dir \'' . $smarty->cache_dir . '\' does not exist, or is not a directory.', E_USER_ERROR);
                return false;
            }
            $smarty->trigger_error('unable to write to $cache_dir \'' . realpath($smarty->cache_dir) . '\'. Be sure $cache_dir is writable by the web server user.', E_USER_ERROR);
            return false;
        }

        $_auto_id = $smarty->_get_auto_id($params['cache_id'], $params['compile_id']);
        $_cache_file = $smarty->_get_auto_filename($smarty->cache_dir, $params['tpl_file'], $_auto_id);
        $_params = array('filename' => $_cache_file, 'contents' => $params['results'], 'create_dirs' => true);
        require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.write_file.php');
        smarty_core_write_file($_params, $smarty);
        return true;
    }
}

/* vim: set expandtab: */

?>
