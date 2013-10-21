<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ExamplePluginTemplate
 */
namespace Piwik\Plugins\ExamplePluginTemplate;

use Piwik\Plugin;

/**
 * @package ExamplePluginTemplate
 */
class ExamplePluginTemplate extends Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
        );
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/ExamplePluginTemplate/javascripts/plugin.js';
    }
}
