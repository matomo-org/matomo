<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\RssWidget;
use Piwik\SettingsPiwik;
use Piwik\Widget\WidgetsList;

/**
 *
 */
class RssWidget extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Request.getRenamedModuleAndAction' => 'renameExampleRssWidgetModule',
            'Widget.filterWidgets' => 'filterWidgets'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/RssWidget/stylesheets/rss.less";
    }

    public function renameExampleRssWidgetModule(&$module, &$action)
    {
        if ($module == 'ExampleRssWidget') {
            $module = 'RssWidget';
        }
    }

    /**
     * @param WidgetsList $list
     */
    public function filterWidgets($list)
    {
        if (!SettingsPiwik::isInternetEnabled()) {
            $list->remove('About Piwik', 'Piwik Changelog');
            $list->remove('About Piwik', 'Piwik.org Blog');
        }
    }
}
