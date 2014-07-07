<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleUI;

use Piwik\Menu\MenuReporting;
use Piwik\Menu\MenuTop;
use Piwik\Menu\MenuUser;
use Piwik\Plugin\Manager as PluginManager;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->add('UI Framework', '', array('module' => 'ExampleUI', 'action' => 'dataTables'), true, 30);

        $this->addSubMenu($menu, 'Data tables', 'dataTables', 1);
        $this->addSubMenu($menu, 'Bar graph', 'barGraph', 2);
        $this->addSubMenu($menu, 'Pie graph', 'pieGraph', 3);
        $this->addSubMenu($menu, 'Tag clouds', 'tagClouds', 4);
        $this->addSubMenu($menu, 'Sparklines', 'sparklines', 5);
        $this->addSubMenu($menu, 'Evolution Graph', 'evolutionGraph', 6);

        if (PluginManager::getInstance()->isPluginActivated('TreemapVisualization')) {
            $this->addSubMenu($menu, 'Treemap', 'treemap', 7);
        }
    }

    public function configureUserMenu(MenuUser $menu)
    {
        $urlParams = array('module' => 'ExampleUI', 'action' => 'notifications');
        $menu->add('CorePluginsAdmin_MenuPlatform', 'UI Notifications', $urlParams, $displayedForCurrentUser = true, $order = 3);
    }


    private function addSubMenu(MenuReporting $menu, $subMenu, $action, $order)
    {
        $menu->add('UI Framework', $subMenu, array('module' => 'ExampleUI', 'action' => $action), true, $order);
    }
}
