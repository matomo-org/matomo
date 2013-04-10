<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package SmartyPlugins
 */

/**
 *    Load translation strings suffixed with _js for a given list of modules.
 *  This function needs to be called when you want to i18n the user interface.
 *
 *  How to use the function in smarty templates:
 *  {loadJavascriptTranslations plugins='SitesManager CoreHome General'}
 *
 *  This will load the javascript translations array for the modules specified as parameters.
 *  Only translations string with their ids suffixed with '_js' will be loaded
 *  Note: You can specify disableOutputScriptTag=1 and the returned value won't be enclosed in Javascript tags.
 *
 *  You can then translate strings in javascript by calling the javascript function:
 *     _pk_translate('MY_TRANSLATION_STRING_js')
 *
 * _pk_translate does NOT support printf() arguments, but you can call:
 *     sprintf(_pk_translate('MyPlugin_numberOfEggs_js'),'ten')
 * where you would have the following in your translation file plugins/MyPlugin/lang/en.php:
 *     'MyPlugin_numberOfEggs_js' => 'There are %s eggs.'
 *
 * @param array $params
 * @param $smarty
 * @throws Exception
 * @return string
 */
function smarty_function_loadJavascriptTranslations($params, &$smarty)
{
    static $pluginTranslationsAlreadyLoaded = array();
    if (!isset($params['plugins'])) {
        throw new Exception("The smarty function loadJavascriptTranslations needs a 'plugins' parameter.");
    }
    if (in_array($params['plugins'], $pluginTranslationsAlreadyLoaded)) {
        return;
    }
    $pluginTranslationsAlreadyLoaded[] = $params['plugins'];
    $jsTranslations = Piwik_Translate::getInstance()->getJavascriptTranslations(explode(' ', $params['plugins']));
    $jsCode = '';
    if (isset($params['disableOutputScriptTag'])) {
        $jsCode .= $jsTranslations;
    } else {
        $jsCode .= '<script type="text/javascript">';
        $jsCode .= $jsTranslations;
        $jsCode .= '</script>';
    }
    return $jsCode;
}
