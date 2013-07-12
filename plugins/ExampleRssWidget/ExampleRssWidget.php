<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_ExampleRssWidget
 */

/**
 *
 * @package Piwik_ExampleRssWidget
 */
class Piwik_ExampleRssWidget extends Piwik_Plugin
{
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('ExampleRssWidget_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => '0.1',
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles' => 'getCssFiles',
            'WidgetsList.add'          => 'addWidgets'
        );
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/ExampleRssWidget/stylesheets/rss.css";
    }

    public function addWidgets()
    {
        Piwik_AddWidget('Example Widgets', 'Piwik.org Blog', 'ExampleRssWidget', 'rssPiwik');
        Piwik_AddWidget('Example Widgets', 'Piwik Changelog', 'ExampleRssWidget', 'rssChangelog');
    }
}
