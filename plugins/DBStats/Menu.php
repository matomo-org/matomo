<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats;

use Exception;
use Piwik\Common;
use Piwik\Db;
use Piwik\Menu\MenuAbstract;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuReporting;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Site;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $menu->add('CoreAdminHome_MenuDiagnostic', 'DBStats_DatabaseUsage',
                   array('module' => 'DBStats', 'action' => 'index'),
                   Piwik::hasUserSuperUserAccess(),
                   $order = 6);
    }
}
