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
            $menu->registerMenuIcon('General_Help', 'icon-help');
            $menu->addItem('General_Help', null, array('module' => 'Feedback', 'action' => 'index'), $order = 990, Piwik::translate('General_Help'));
        }

        $menu->registerMenuIcon($login, 'icon-user');

        if (Piwik::isUserIsAnonymous()) {
            if (Plugin\Manager::getInstance()->isPluginActivated('ScheduledReports')) {
                $menu->addItem($login, null, array('module' => 'ScheduledReports', 'action' => 'index'), 970, Piwik::translate('UsersManager_PersonalSettings'));
            } else {
                $menu->addItem($login, null, array('module' => 'API', 'action' => 'listAllAPI'), 970, Piwik::translate('API_ReportingApiReference'));
            }
        } else {
            $tooltip = sprintf('%s: %s', Piwik::translate('UsersManager_PersonalSettings'), $login);
            $menu->addItem($login, null, array('module' => 'UsersManager', 'action' => 'userSettings'), 970, $tooltip);
        }

        $module = $this->getLoginModule();
        if (Piwik::isUserIsAnonymous()) {
            $menu->registerMenuIcon('Login_LogIn', 'icon-sign-in');
            $menu->addItem('Login_LogIn', null, array('module' => $module, 'action' => false), 1000, Piwik::translate('Login_LogIn'));
        } else {
            $menu->registerMenuIcon('General_Logout', 'icon-sign-out');
            $menu->addItem('General_Logout', null, array('module' => $module, 'action' => 'logout', 'idSite' => null), 1000, Piwik::translate('General_Logout'));
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
