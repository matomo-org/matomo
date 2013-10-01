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

/*
- prepare a page with all use cases
- test the actions datatable in this page?
- test datatable with search disabled
- test datatable with low population disabled
- without footer
- without all columns icon
+ update http://piwik.org/participate/user-interface
*/
namespace Piwik\Plugins\ExampleUI;


/**
 *
 * @package ExampleUI
 */
class ExampleUI extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'Menu.addMenuEntry' => 'addMenus',
        );
        return $hooks;
    }

    function addMenus()
    {
        $menus = array(
            'Data tables'     => 'dataTables',
            'Evolution graph' => 'evolutionGraph',
            'Bar graph'       => 'barGraph',
            'Pie graph'       => 'pieGraph',
            'Tag clouds'      => 'tagClouds',
            'Sparklines'      => 'sparklines',
        );

        Piwik_AddMenu('UI Framework', '', array('module' => 'ExampleUI', 'action' => 'dataTables'), true, 30);
        $order = 1;
        foreach ($menus as $subMenu => $action) {
            Piwik_AddMenu('UI Framework', $subMenu, array('module' => 'ExampleUI', 'action' => $action), true, $order++);
        }
    }
}
