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
        if (Piwik::isUserHasSomeAdminAccess()) {
            $menu->addDiagnosticItem('DevicesDetection_DeviceDetection',
                                     $this->urlForAction('deviceDetection'),
                                     $order = 40);
        }
    }

    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->addVisitorsItem('DevicesDetection_submenu', $this->urlForAction('index'));
    }
}
