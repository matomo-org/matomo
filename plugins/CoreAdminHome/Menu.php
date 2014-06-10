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

        $menu->add('CoreAdminHome_MenuManage', null, "", $hasAdminAccess, $order = 1);
        $menu->add('CoreAdminHome_MenuDiagnostic', null, "", $hasAdminAccess, $order = 10);
        $menu->add('General_Settings', null, "", $hasAdminAccess, $order = 5);
        $menu->add('General_Settings', 'CoreAdminHome_MenuGeneralSettings',
                   array('module' => 'CoreAdminHome', 'action' => 'generalSettings'),
                   $hasAdminAccess,
                   $order = 6);
        $menu->add('CoreAdminHome_MenuManage', 'CoreAdminHome_TrackingCode',
                   array('module' => 'CoreAdminHome', 'action' => 'trackingCodeGenerator'),
                   $hasAdminAccess,
                   $order = 4);
        $menu->add('General_Settings', 'CoreAdminHome_PluginSettings',
                   array('module' => 'CoreAdminHome', 'action' => 'pluginSettings'),
                   SettingsManager::hasPluginsSettingsForCurrentUser(),
                   $order = 7);
    }

}
