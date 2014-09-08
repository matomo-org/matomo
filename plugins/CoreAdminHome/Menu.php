<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Db;
use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Settings\Manager as SettingsManager;

class Menu extends \Piwik\Plugin\Menu
{

    public function configureAdminMenu(MenuAdmin $menu)
    {
        $hasAdminAccess = Piwik::isUserHasSomeAdminAccess();

        if ($hasAdminAccess) {
            $menu->addManageItem(null, "", $order = 1);
            $menu->addSettingsItem(null, "", $order = 5);
            $menu->addDiagnosticItem(null, "", $order = 10);
            $menu->addDevelopmentItem(null, "", $order = 15);

            $menu->addSettingsItem('CoreAdminHome_MenuGeneralSettings',
                                   $this->urlForAction('generalSettings'),
                                   $order = 6);
            $menu->addManageItem('CoreAdminHome_TrackingCode',
                                 $this->urlForAction('trackingCodeGenerator'),
                                 $order = 4);
        }

        if (SettingsManager::hasPluginsSettingsForCurrentUser()) {
            $menu->addSettingsItem('CoreAdminHome_PluginSettings',
                                   $this->urlForAction('pluginSettings'),
                                   $order = 7);
        }
    }

}
