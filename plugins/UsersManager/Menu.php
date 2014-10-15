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
use Piwik\Menu\MenuUser;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::isUserHasSomeAdminAccess()) {
            $menu->addManageItem('UsersManager_MenuUsers', $this->urlForAction('index'), $order = 2);
            $menu->addManageItem('UsersManager_MenuUserSettings', $this->urlForAction('userSettings'), $order = 3);
        }
    }

    public function configureUserMenu(MenuUser $menu)
    {
        if (!Piwik::isUserIsAnonymous()) {
            $menu->addItem('', 'General_Settings', $this->urlForAction('userSettings'), 0);
        }
    }
}
