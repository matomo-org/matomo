<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ExampleUI;
use Piwik\Menu\MenuAbstract;
use Piwik\Menu\MenuMain;
use Piwik\Menu\MenuTop;

/**
 */
class ExampleUI extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Menu.Reporting.addItems' => 'addReportingMenuItems',
            'Menu.Top.addItems'       => 'addTopMenuItems',
        );
    }

    function addReportingMenuItems(MenuAbstract $menu)
    {
        $menu->add('UI Framework', '', array('module' => 'ExampleUI', 'action' => 'dataTables'), true, 30);

        $this->addSubMenu($menu, 'Data tables', 'dataTables', 1);
        $this->addSubMenu($menu, 'Bar graph', 'barGraph', 2);
        $this->addSubMenu($menu, 'Pie graph', 'pieGraph', 3);
        $this->addSubMenu($menu, 'Tag clouds', 'tagClouds', 4);
        $this->addSubMenu($menu, 'Sparklines', 'sparklines', 5);
        $this->addSubMenu($menu, 'Evolution Graph', 'evolutionGraph', 6);

        if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated('TreemapVisualization')) {
            $this->addSubMenu($menu, 'Treemap', 'treemap', 7);
        }
    }

    function addTopMenuItems(MenuTop $menu)
    {
        $urlParams = array('module' => 'ExampleUI', 'action' => 'notifications');
        $menu->addEntry('UI Notifications', null, $urlParams, $displayedForCurrentUser = true, $order = 3);
    }

    private function addSubMenu(MenuAbstract $menu, $subMenu, $action, $order)
    {
        $menu->add('UI Framework', $subMenu, array('module' => 'ExampleUI', 'action' => $action), true, $order);
    }
}
