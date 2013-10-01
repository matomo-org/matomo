<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ExampleRssWidget
 */
namespace Piwik\Plugins\ExampleRssWidget;

use Piwik\WidgetsList;

/**
 *
 * @package ExampleRssWidget
 */
class ExampleRssWidget extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'WidgetsList.addWidgets'          => 'addWidgets'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/ExampleRssWidget/stylesheets/rss.less";
    }

    public function addWidgets()
    {
        WidgetsList::add('Example Widgets', 'Piwik.org Blog', 'ExampleRssWidget', 'rssPiwik');
        WidgetsList::add('Example Widgets', 'Piwik Changelog', 'ExampleRssWidget', 'rssChangelog');
    }
}
