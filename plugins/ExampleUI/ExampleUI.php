<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ExampleUI
 */

namespace Piwik\Plugins\ExampleUI;

/**
 * @package ExampleUI
 */
class ExampleUI extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Menu.Reporting.addItems' => 'addMenus',
        );
    }

    function addMenus()
    {
        Piwik_AddMenu('UI Framework', '', array('module' => 'ExampleUI', 'action' => 'dataTables'), true, 30);

        $this->addSubMenu('Data tables', 'dataTables', 1);
        $this->addSubMenu('Bar graph', 'barGraph', 2);
        $this->addSubMenu('Pie graph', 'pieGraph', 3);
        $this->addSubMenu('Tag clouds', 'tagClouds', 4);
        $this->addSubMenu('Sparklines', 'sparklines', 5);
    }

    private function addSubMenu($subMenu, $action, $order)
    {
        Piwik_AddMenu('UI Framework', $subMenu, array('module' => 'ExampleUI', 'action' => $action), true, $order);
    }
}
