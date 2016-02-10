<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Morpheus;

use Piwik\Development;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuReporting;
use Piwik\Menu\MenuUser;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $menu->registerMenuIcon('CoreAdminHome_MenuDevelopment', 'icon-admin-development');
        $menu->registerMenuIcon('CoreAdminHome_MenuDiagnostic', 'icon-admin-diagnostic');
        $menu->registerMenuIcon('CorePluginsAdmin_MenuPlatform', 'icon-admin-platform');
        $menu->registerMenuIcon('General_Settings', 'icon-admin-settings');
        $menu->registerMenuIcon('CoreAdminHome_Administration', 'icon-admin-administration');

        if (Development::isEnabled() && Piwik::isUserHasSomeAdminAccess()) {
            $menu->addDevelopmentItem('UI Demo', $this->urlForAction('demo'));
        }
    }

    public function configureUserMenu(MenuUser $menu)
    {
        $menu->registerMenuIcon('UsersManager_MenuPersonal', 'icon-user-personal');
        $menu->registerMenuIcon('CoreAdminHome_MenuManage', 'icon-user-manage');
        $menu->registerMenuIcon('CorePluginsAdmin_MenuPlatform', 'icon-user-platform');

        if (Development::isEnabled() && Piwik::isUserHasSomeAdminAccess()) {
            $menu->addPlatformItem('UI Demo', $this->urlForAction('demo'), $order = 15);
        }
    }

    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->registerMenuIcon('General_Visitors', 'icon-reporting-visitors');
        $menu->registerMenuIcon('General_Actions', 'icon-reporting-actions');
        $menu->registerMenuIcon('Referrers_Referrers', 'icon-reporting-referer');
        $menu->registerMenuIcon('Goals_Goals', 'icon-reporting-goal');
        $menu->registerMenuIcon('Goals_Ecommerce', 'icon-reporting-ecommerce');
        $menu->registerMenuIcon('Dashboard_Dashboard', 'icon-reporting-dashboard');
    }
}
