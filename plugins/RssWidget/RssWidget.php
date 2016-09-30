<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\RssWidget;

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
}
