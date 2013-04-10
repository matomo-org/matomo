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
 * Sends AssetManager.getCssFiles or AssetManager.getJsFiles events, gathers assets and include them.
 *
 * Examples:
 * <pre>
 *         {includeAssets type="css"}
 * </pre>
 *
 * @param array $params array([type] => the type of the assets to include)
 * @param $smarty
 * @throws Exception
 * @return
 */
function smarty_function_includeAssets($params, &$smarty)
{
    if (!isset($params['type'])) {
        throw new Exception("The smarty function includeAssets needs a 'type' parameter.");
    }

    $assetType = strtolower($params['type']);
    switch ($assetType) {
        case 'css':

            return Piwik_AssetManager::getCssAssets();

        case 'js':

            return Piwik_AssetManager::getJsAssets();

        default:
            throw new Exception("The smarty function includeAssets 'type' parameter needs to be either 'css' or 'js'.");
    }
}
