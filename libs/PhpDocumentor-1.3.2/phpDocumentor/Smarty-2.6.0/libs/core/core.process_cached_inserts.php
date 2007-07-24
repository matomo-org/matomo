<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Replace cached inserts with the actual results
 *
 * @param string $results
 * @return string
 */
function smarty_core_process_cached_inserts($params, &$smarty)
{
    preg_match_all('!'.$smarty->_smarty_md5.'{insert_cache (.*)}'.$smarty->_smarty_md5.'!Uis',
                   $params['results'], $match);
    list($cached_inserts, $insert_args) = $match;

    for ($i = 0, $for_max = count($cached_inserts); $i < $for_max; $i++) {
        if ($smarty->debugging) {
            $_params = array();
            require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.get_microtime.php');
            $debug_start_time = smarty_core_get_microtime($_params, $smarty);
        }

        $args = unserialize($insert_args[$i]);
        $name = $args['name'];

        if (isset($args['script'])) {
            $_params = array('resource_name' => $smarty->_dequote($args['script']));
            require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.get_php_resource.php');
            if(!smarty_core_get_php_resource($_params, $smarty)) {
                return false;
            }
            $resource_type = $_params['resource_type'];
            $php_resource = $_params['php_resource'];


            if ($resource_type == 'file') {
                $smarty->_include($php_resource, true);
            } else {
                $smarty->_eval($php_resource);
            }
        }

        $function_name = $smarty->_plugins['insert'][$name][0];
        if (empty($args['assign'])) {
            $replace = $function_name($args, $smarty);
        } else {
            $smarty->assign($args['assign'], $function_name($args, $smarty));
            $replace = '';
        }

        $params['results'] = str_replace($cached_inserts[$i], $replace, $params['results']);
        if ($smarty->debugging) {
            $_params = array();
            require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.get_microtime.php');
            $smarty->_smarty_debug_info[] = array('type'      => 'insert',
                                                'filename'  => 'insert_'.$name,
                                                'depth'     => $smarty->_inclusion_depth,
                                                'exec_time' => smarty_core_get_microtime($_params, $smarty) - $debug_start_time);
        }
    }

    return $params['results'];
}

/* vim: set expandtab: */

?>
