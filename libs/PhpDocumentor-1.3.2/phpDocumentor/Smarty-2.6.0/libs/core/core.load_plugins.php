<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Load requested plugins
 *
 * @param array $plugins
 */

// $plugins

function smarty_core_load_plugins($params, &$smarty)
{

    foreach ($params['plugins'] as $_plugin_info) {
        list($_type, $_name, $_tpl_file, $_tpl_line, $_delayed_loading) = $_plugin_info;
        $_plugin = &$smarty->_plugins[$_type][$_name];

        /*
         * We do not load plugin more than once for each instance of Smarty.
         * The following code checks for that. The plugin can also be
         * registered dynamically at runtime, in which case template file
         * and line number will be unknown, so we fill them in.
         *
         * The final element of the info array is a flag that indicates
         * whether the dynamically registered plugin function has been
         * checked for existence yet or not.
         */
        if (isset($_plugin)) {
            if (empty($_plugin[3])) {
                if (!is_callable($_plugin[0])) {
                    $smarty->_trigger_fatal_error("[plugin] $_type '$_name' is not implemented", $_tpl_file, $_tpl_line, __FILE__, __LINE__);
                } else {
                    $_plugin[1] = $_tpl_file;
                    $_plugin[2] = $_tpl_line;
                    $_plugin[3] = true;
                    if (!isset($_plugin[4])) $_plugin[4] = true; /* cacheable */
                }
            }
            continue;
        } else if ($_type == 'insert') {
            /*
             * For backwards compatibility, we check for insert functions in
             * the symbol table before trying to load them as a plugin.
             */
            $_plugin_func = 'insert_' . $_name;
            if (function_exists($_plugin_func)) {
                $_plugin = array($_plugin_func, $_tpl_file, $_tpl_line, true, false);
                continue;
            }
        }

        $_plugin_file = $smarty->_get_plugin_filepath($_type, $_name);

        if (! $_found = ($_plugin_file != false)) {
            $_message = "could not load plugin file '$_type.$_name.php'\n";
        }

        /*
         * If plugin file is found, it -must- provide the properly named
         * plugin function. In case it doesn't, simply output the error and
         * do not fall back on any other method.
         */
        if ($_found) {
            include_once $_plugin_file;

            $_plugin_func = 'smarty_' . $_type . '_' . $_name;
            if (!function_exists($_plugin_func)) {
                $smarty->_trigger_fatal_error("[plugin] function $_plugin_func() not found in $_plugin_file", $_tpl_file, $_tpl_line, __FILE__, __LINE__);
                continue;
            }
        }
        /*
         * In case of insert plugins, their code may be loaded later via
         * 'script' attribute.
         */
        else if ($_type == 'insert' && $_delayed_loading) {
            $_plugin_func = 'smarty_' . $_type . '_' . $_name;
            $_found = true;
        }

        /*
         * Plugin specific processing and error checking.
         */
        if (!$_found) {
            if ($_type == 'modifier') {
                /*
                 * In case modifier falls back on using PHP functions
                 * directly, we only allow those specified in the security
                 * context.
                 */
                if ($smarty->security && !in_array($_name, $smarty->security_settings['MODIFIER_FUNCS'])) {
                    $_message = "(secure mode) modifier '$_name' is not allowed";
                } else {
                    if (!function_exists($_name)) {
                        $_message = "modifier '$_name' is not implemented";
                    } else {
                        $_plugin_func = $_name;
                        $_found = true;
                    }
                }
            } else if ($_type == 'function') {
                /*
                 * This is a catch-all situation.
                 */
                $_message = "unknown tag - '$_name'";
            }
        }

        if ($_found) {
            $smarty->_plugins[$_type][$_name] = array($_plugin_func, $_tpl_file, $_tpl_line, true, true);
        } else {
            // output error
            $smarty->_trigger_fatal_error('[plugin] ' . $_message, $_tpl_file, $_tpl_line, __FILE__, __LINE__);
        }
    }
}

/* vim: set expandtab: */

?>
