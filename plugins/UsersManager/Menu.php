<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::isUserHasSomeAdminAccess()) {
            $menu->addManageItem('UsersManager_MenuUsers',
                                 array('module' => 'UsersManager', 'action' => 'index'),
                                 $order = 2);
            $menu->addManageItem('UsersManager_MenuUserSettings',
                                 array('module' => 'UsersManager', 'action' => 'userSettings'),
                                 $order = 3);
        }
    }
}
