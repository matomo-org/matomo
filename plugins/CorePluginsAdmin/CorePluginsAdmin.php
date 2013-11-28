<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CorePluginsAdmin
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Config;
use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\ScheduledTask;
use Piwik\ScheduledTime\Daily;
use Piwik\ScheduledTime;

/**
 *
 * @package CorePluginsAdmin
 */
class CorePluginsAdmin extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Menu.Admin.addItems'                    => 'addMenu',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'TaskScheduler.getScheduledTasks'        => 'getScheduledTasks',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys'
        );
    }

    /**
     * Gets all scheduled tasks executed by this plugin.
     */
    public function getScheduledTasks(&$tasks)
    {
        $tasks[] = new ScheduledTask(
            'Piwik\Plugins\CorePluginsAdmin\MarketplaceApiClient',
            'clearAllCacheEntries',
            null,
            ScheduledTime::factory('daily'),
            ScheduledTask::LOWEST_PRIORITY
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/CorePluginsAdmin/stylesheets/marketplace.less";
    }

    function addMenu()
    {
        $pluginsUpdateMessage = '';
        $themesUpdateMessage = '';

        if (Piwik::isUserIsSuperUser() && static::isMarketplaceEnabled()) {
            $marketplace = new Marketplace();
            $pluginsHavingUpdate = $marketplace->getPluginsHavingUpdate($themesOnly = false);
            $themesHavingUpdate = $marketplace->getPluginsHavingUpdate($themesOnly = true);

            if (!empty($pluginsHavingUpdate)) {
                $pluginsUpdateMessage = sprintf(' (%d)', count($pluginsHavingUpdate));
            }
            if (!empty($themesHavingUpdate)) {
                $themesUpdateMessage = sprintf(' (%d)', count($themesHavingUpdate));
            }
        }

        MenuAdmin::getInstance()->add('CorePluginsAdmin_MenuPlatform', null, "", !Piwik::isUserIsAnonymous(), $order = 15);
        MenuAdmin::getInstance()->add('CorePluginsAdmin_MenuPlatform', Piwik::translate('General_Plugins') . $pluginsUpdateMessage,
            array('module' => 'CorePluginsAdmin', 'action' => 'plugins', 'activated' => ''),
            Piwik::isUserIsSuperUser(),
            $order = 1);
        MenuAdmin::getInstance()->add('CorePluginsAdmin_MenuPlatform', Piwik::translate('CorePluginsAdmin_Themes') . $themesUpdateMessage,
            array('module' => 'CorePluginsAdmin', 'action' => 'themes', 'activated' => ''),
            Piwik::isUserIsSuperUser(),
            $order = 3);

        if (static::isMarketplaceEnabled()) {

            MenuAdmin::getInstance()->add('CorePluginsAdmin_MenuPlatform', 'CorePluginsAdmin_Marketplace',
                array('module' => 'CorePluginsAdmin', 'action' => 'extend', 'activated' => ''),
                !Piwik::isUserIsAnonymous(),
                $order = 5);

        }
    }

    public static function isMarketplaceEnabled()
    {
        return (bool) Config::getInstance()->General['enable_marketplace'];
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/CoreHome/javascripts/popover.js";
        $jsFiles[] = "plugins/CorePluginsAdmin/javascripts/pluginDetail.js";
        $jsFiles[] = "plugins/CorePluginsAdmin/javascripts/pluginOverview.js";
        $jsFiles[] = "plugins/CorePluginsAdmin/javascripts/pluginExtend.js";
        $jsFiles[] = "plugins/CorePluginsAdmin/javascripts/plugins.js";
    }

    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'CorePluginsAdmin_NoZipFileSelected';
    }

}
