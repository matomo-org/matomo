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
            $menu->addSettingsItem('UsersManager_PersonalSettings', $this->urlForAction('userSettings'), $order = 1);
        }

        if (Piwik::hasUserSuperUserAccess() && API::getInstance()->getSitesAccessFromUser('anonymous')) {
            $menu->addSettingsItem('UsersManager_MenuAnonymousUserSettings', $this->urlForAction('anonymousSettings'), $order = 8);
        }
    }

    public function configureUserMenu(MenuUser $menu)
    {
        if (!Piwik::isUserIsAnonymous()) {
            $menu->addItem('', 'General_Settings', $this->urlForAction('userSettings'), 0);
        }
    }
}
