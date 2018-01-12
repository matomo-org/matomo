<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $menu->addDiagnosticItem('DBStats_DatabaseUsage',
                                     $this->urlForAction('index'),
                                     $order = 6);
        }
    }
}
