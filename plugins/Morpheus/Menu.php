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
        $menu->registerMenuIcon('UsersManager_MenuPersonal', 'icon-user-personal');
        $menu->registerMenuIcon('CoreAdminHome_MenuManage', 'icon-user-manage');
        $menu->registerMenuIcon('CorePluginsAdmin_MenuPlatform', 'icon-user-platform');

        if (Development::isEnabled() && Piwik::isUserHasSomeAdminAccess()) {
            $menu->addDevelopmentItem('UI Demo', $this->urlForAction('demo'));
        }

        if (Development::isEnabled() && Piwik::isUserHasSomeAdminAccess()) {
            $menu->addPlatformItem('UI Demo', $this->urlForAction('demo'), $order = 15);
        }
    }
}
