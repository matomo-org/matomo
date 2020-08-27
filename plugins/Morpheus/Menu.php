<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
        $menu->registerMenuIcon('CoreAdminHome_Administration', 'icon-settings');
        $menu->registerMenuIcon('UsersManager_MenuPersonal', 'icon-user-personal');
        $menu->registerMenuIcon('CoreAdminHome_MenuSystem', 'icon-server');
        $menu->registerMenuIcon('CorePluginsAdmin_MenuPlatform', 'icon-user-platform');

        $manageMeasurablesIcon = 'icon-open-source';
        $menu->registerMenuIcon('CoreAdminHome_MenuMeasurables', $manageMeasurablesIcon);
        $menu->registerMenuIcon('SitesManager_Sites', $manageMeasurablesIcon);
        $menu->registerMenuIcon('MobileAppMeasurable_MobileApps', $manageMeasurablesIcon);

        if (Development::isEnabled() && Piwik::isUserHasSomeAdminAccess()) {
            $menu->addDevelopmentItem('CoreAdminHome_UiDemo', $this->urlForAction('demo'));
        }
    }
}
