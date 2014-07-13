<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection;

use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuReporting;
use Piwik\Piwik;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $menu->add(
            'CoreAdminHome_MenuDiagnostic', 'DevicesDetection_DeviceDetection',
            array('module' => 'DevicesDetection', 'action' => 'deviceDetection'),
            Piwik::isUserHasSomeAdminAccess(),
            $order = 40
        );
    }

    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->add('General_Visitors', 'DevicesDetection_submenu', array('module' => 'DevicesDetection', 'action' => 'index'));
    }
}
