<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::isUserHasSomeAdminAccess()) {
            $menu->addDiagnosticItem('DevicesDetection_DeviceDetection',
                                     $this->urlForAction('detection'),
                                     $order = 40);
        }
    }
}
