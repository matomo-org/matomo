<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * read a cache file, determine if it needs to be
 * regenerated or not
 *
 * @param string $tpl_file
 * @param string $cache_id
 * @param string $compile_id
 * @param string $results
 * @return boolean
 */

//  $tpl_file, $cache_id, $compile_id, &$results

function smarty_core_read_cache_file(&$params, &$smarty)
{
    static  $content_cache = array();

    if ($smarty->force_compile) {
        // force compile enabled, always regenerate
        return false;
    }

    if (isset($content_cache[$params['tpl_file'].','.$params['cache_id'].','.$params['compile_id']])) {
        list($params['results'], $smarty->_cache_info) = $content_cache[$params['tpl_file'].','.$params['cache_id'].','.$params['compile_id']];
        return true;
    }

    if (!empty($smarty->cache_handler_func)) {
        // use cache_handler function
        call_user_func_array($smarty->cache_handler_func,
                             array('read', &$smarty, &$params['results'], $params['tpl_file'], $params['cache_id'], $params['compile_id'], null));
    } else {
        // use local cache file
        $_auto_id = $smarty->_get_auto_id($params['cache_id'], $params['compile_id']);
        $_cache_file = $smarty->_get_auto_filename($smarty->cache_dir, $params['tpl_file'], $_auto_id);
        $params['results'] = $smarty->_read_file($_cache_file);
    }

    if (empty($params['results'])) {
        // nothing to parse (error?), regenerate cache
        return false;
    }

    $_contents = $params['results'];
    $_info_start = strpos($_contents, "\n") + 1;
    $_info_len = (int)substr($_contents, 0, $_info_start - 1);
    $_cache_info = unserialize(substr($_contents, $_info_start, $_info_len));
    $params['results'] = substr($_contents, $_info_start + $_info_len);

    if ($smarty->caching == 2 && isset ($_cache_info['expires'])){
        // caching by expiration time
        if ($_cache_info['expires'] > -1 && (time() > $_cache_info['expires'])) {
            // cache expired, regenerate
            return false;
        }
    } else {
        // caching by lifetime
        if ($smarty->cache_lifetime > -1 && (time() - $_cache_info['timestamp'] > $smarty->cache_lifetime)) {
            // cache expired, regenerate
            return false;
        }
    }

    if ($smarty->compile_check) {
        $_params = array('get_source' => false, 'quiet'=>true);
        foreach (array_keys($_cache_info['template']) as $_template_dep) {
            $_params['resource_name'] = $_template_dep;
            if (!$smarty->_fetch_resource_info($_params) || $_cache_info['timestamp'] < $_params['resource_timestamp']) {
                // template file has changed, regenerate cache
                return false;
            }
        }

        if (isset($_cache_info['config'])) {
            $_params = array('resource_base_path' => $smarty->config_dir, 'get_source' => false, 'quiet'=>true);
            foreach (array_keys($_cache_info['config']) as $_config_dep) {
                $_params['resource_name'] = $_config_dep;
                if (!$smarty->_fetch_resource_info($_params) || $_cache_info['timestamp'] < $_params['resource_timestamp']) {
                    // config file has changed, regenerate cache
                    return false;
                }
            }
        }
    }

    $content_cache[$params['tpl_file'].','.$params['cache_id'].','.$params['compile_id']] = array($params['results'], $_cache_info);

    $smarty->_cache_info = $_cache_info;
    return true;
}

/* vim: set expandtab: */

?>
