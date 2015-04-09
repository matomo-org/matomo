<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

use Piwik\Db;
use Piwik\Menu\MenuTop;
use Piwik\Menu\MenuUser;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\UsersManager\API as APIUsersManager;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
        $login = Piwik::getCurrentUserLogin();
        $user  = APIUsersManager::getInstance()->getUser($login);

        if (!empty($user['alias'])) {
            $login = $user['alias'];
        }

        if (Plugin\Manager::getInstance()->isPluginActivated('Feedback')) {
            $menu->addItem('General_Help', null, array('module' => 'Feedback', 'action' => 'index'));
        }

        if (Piwik::isUserIsAnonymous()) {
            if (Plugin\Manager::getInstance()->isPluginActivated('Feedback')) {
                $menu->addItem($login, null, array('module' => 'Feedback', 'action' => 'index'), 998);
            } else {
                $menu->addItem($login, null, array('module' => 'API', 'action' => 'listAllAPI'), 998);
            }
        } else {
            $menu->addItem($login, null, array('module' => 'UsersManager', 'action' => 'userSettings'), 998);
        }

        $module = $this->getLoginModule();
        if (Piwik::isUserIsAnonymous()) {
            $menu->addItem('Login_LogIn', null, array('module' => $module, 'action' => false), 999);
        } else {
            $menu->addItem('General_Logout', null, array('module' => $module, 'action' => 'logout', 'idSite' => null), 999);
        }
    }

    public function configureUserMenu(MenuUser $menu)
    {
        $menu->addPersonalItem(null, array(), 1, false);
        $menu->addManageItem(null, array(), 2, false);
        $menu->addPlatformItem(null, array(), 3, false);
    }

    private function getLoginModule()
    {
        return Piwik::getLoginPluginName();
    }

}
