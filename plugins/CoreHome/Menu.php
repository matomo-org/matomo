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

class Menu extends \Piwik\Plugin\Menu
{
    public function configureUserMenu(MenuUser $menu)
    {
        if (!Piwik::isUserIsAnonymous()) {
            $module = $this->getLoginModule();
            $menu->addItem('General_Logout', null, array('module' => $module, 'action' => 'logout'), 999);
        }
    }

    public function configureTopMenu(MenuTop $menu)
    {
        if (Piwik::isUserIsAnonymous()) {
            $module = $this->getLoginModule();
            $menu->addItem('Login_LogIn', null, array('module' => $module), 999);
        }
    }

    private function getLoginModule()
    {
        return Piwik::getLoginPluginName();
    }

}
