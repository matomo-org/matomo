<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {config_load} function plugin
 *
 * Type:     function<br>
 * Name:     config_load<br>
 * Purpose:  load config file vars
 * @link http://smarty.php.net/manual/en/language.function.config.load.php {config_load}
 *       (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author messju mohr <messju at lammfellpuschen dot de> (added use of resources)
 * @param array Format:
 * <pre>
 * array('file' => required config file name,
 *       'section' => optional config file section to load
 *       'scope' => local/parent/global
 *       'global' => overrides scope, setting to parent if true)
 * </pre>
 * @param Smarty
 */
function smarty_function_config_load($params, &$smarty)
{
        if ($smarty->debugging) {
            $_params = array();
            require_once(SMARTY_CORE_DIR . 'core.get_microtime.php');
            $_debug_start_time = smarty_core_get_microtime($_params, $smarty);
        }

        $_file = isset($params['file']) ? $smarty->_dequote($params['file']) : null;
        $_section = isset($params['section']) ? $smarty->_dequote($params['section']) : null;
        $_scope = isset($params['scope']) ? $smarty->_dequote($params['scope']) : 'global';
        $_global = isset($params['global']) ? $smarty->_dequote($params['global']) : false;

        if (!isset($_file) || strlen($_file) == 0) {
            $smarty->trigger_error("missing 'file' attribute in config_load tag", E_USER_ERROR, __FILE__, __LINE__);
        }

        if (isset($_scope)) {
            if ($_scope != 'local' &&
                $_scope != 'parent' &&
                $_scope != 'global') {
                $smarty->trigger_error("invalid 'scope' attribute value", E_USER_ERROR, __FILE__, __LINE__);
            }
        } else {
            if ($_global) {
                $_scope = 'parent';
            } else {
                $_scope = 'local';
            }
        }

        $_params = array('resource_name' => $_file,
                         'resource_base_path' => $smarty->config_dir,
                         'get_source' => false);
        $smarty->_parse_resource_name($_params);
        $_file_path = $_params['resource_type'] . ':' . $_params['resource_name'];
        if (isset($_section))
            $_compile_file = $smarty->_get_compile_path($_file_path.'|'.$_section);
        else
            $_compile_file = $smarty->_get_compile_path($_file_path);

        if($smarty->force_compile || !file_exists($_compile_file)) {
            $_compile = true;
        } elseif ($smarty->compile_check) {
            $_params = array('resource_name' => $_file,
                             'resource_base_path' => $smarty->config_dir,
                             'get_source' => false);
            $_compile = $smarty->_fetch_resource_info($_params) &&
                $_params['resource_timestamp'] > filemtime($_compile_file);
        } else {
            $_compile = false;
        }

        if($_compile) {
            // compile config file
            if(!is_object($smarty->_conf_obj)) {
                require_once SMARTY_DIR . $smarty->config_class . '.class.php';
                $smarty->_conf_obj = new $smarty->config_class();
                $smarty->_conf_obj->overwrite = $smarty->config_overwrite;
                $smarty->_conf_obj->booleanize = $smarty->config_booleanize;
                $smarty->_conf_obj->read_hidden = $smarty->config_read_hidden;
                $smarty->_conf_obj->fix_newlines = $smarty->config_fix_newlines;
            }

            $_params = array('resource_name' => $_file,
                             'resource_base_path' => $smarty->config_dir,
                             $_params['get_source'] = true);
            if (!$smarty->_fetch_resource_info($_params)) {
                return;
            }
            $smarty->_conf_obj->set_file_contents($_file, $_params['source_content']);
            $_config_vars = array_merge($smarty->_conf_obj->get($_file),
                    $smarty->_conf_obj->get($_file, $_section));
            if(function_exists('var_export')) {
                $_output = '<?php $_config_vars = ' . var_export($_config_vars, true) . '; ?>';
            } else {
                $_output = '<?php $_config_vars = unserialize(\'' . strtr(serialize($_config_vars),array('\''=>'\\\'', '\\'=>'\\\\')) . '\'); ?>';
            }
            $_params = (array('compile_path' => $_compile_file, 'compiled_content' => $_output, 'resource_timestamp' => $_params['resource_timestamp']));
            require_once(SMARTY_CORE_DIR . 'core.write_compiled_resource.php');
            smarty_core_write_compiled_resource($_params, $smarty);
        } else {
            include($_compile_file);
        }

        if ($smarty->caching) {
            $smarty->_cache_info['config'][$_file] = true;
        }

        $smarty->_config[0]['vars'] = @array_merge($smarty->_config[0]['vars'], $_config_vars);
        $smarty->_config[0]['files'][$_file] = true;

        if ($_scope == 'parent') {
                $smarty->_config[1]['vars'] = @array_merge($smarty->_config[1]['vars'], $_config_vars);
                $smarty->_config[1]['files'][$_file] = true;
        } else if ($_scope == 'global') {
            for ($i = 1, $for_max = count($smarty->_config); $i < $for_max; $i++) {
                $smarty->_config[$i]['vars'] = @array_merge($smarty->_config[$i]['vars'], $_config_vars);
                $smarty->_config[$i]['files'][$_file] = true;
            }
        }

        if ($smarty->debugging) {
            $_params = array();
            require_once(SMARTY_CORE_DIR . 'core.get_microtime.php');
            $smarty->_smarty_debug_info[] = array('type'      => 'config',
                                                'filename'  => $_file.' ['.$_section.'] '.$_scope,
                                                'depth'     => $smarty->_inclusion_depth,
                                                'exec_time' => smarty_core_get_microtime($_params, $smarty) - $_debug_start_time);
        }

}

/* vim: set expandtab: */

?>
