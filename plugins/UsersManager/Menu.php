<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
        if (Piwik::isUserHasSomeAdminAccess() && UsersManager::isUsersAdminEnabled()) {
            $menu->addSystemItem('UsersManager_MenuUsers', $this->urlForAction('index'), $order = 15);
        }

        if (Piwik::hasUserSuperUserAccess() && API::getInstance()->getSitesAccessFromUser('anonymous')) {
            $menu->addSystemItem('UsersManager_AnonymousUser', $this->urlForAction('anonymousSettings'), $order = 16);
        }

        if (!Piwik::isUserIsAnonymous()) {
            $menu->addItem('UsersManager_MenuPersonal', 'General_Settings', $this->urlForAction('userSettings'), 0);
            $menu->addItem('UsersManager_MenuPersonal', 'General_Security', $this->urlForAction('userSecurity'), 1);
        }
    }
}
