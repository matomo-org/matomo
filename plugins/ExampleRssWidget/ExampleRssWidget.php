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
use Piwik\Plugin;

/**
 *
 * @package Piwik_ExampleRssWidget
 */
class Piwik_ExampleRssWidget extends Plugin
{
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
        $cssFiles[] = "plugins/ExampleRssWidget/stylesheets/rss.less";
    }

    public function addWidgets()
    {
        Piwik_AddWidget('Example Widgets', 'Piwik.org Blog', 'ExampleRssWidget', 'rssPiwik');
        Piwik_AddWidget('Example Widgets', 'Piwik Changelog', 'ExampleRssWidget', 'rssChangelog');
    }
}
