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
use Piwik\Piwik;
use Piwik\Plugin;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
        $module = $this->getLoginModule();
        if (Piwik::isUserIsAnonymous()) {
            $menu->registerMenuIcon('Login_LogIn', 'icon-sign-in');
            $menu->addItem('Login_LogIn', null, array('module' => $module, 'action' => false), 1000, Piwik::translate('Login_LogIn'));
        } else {
            $menu->registerMenuIcon('General_Logout', 'icon-sign-out');
            $menu->addItem('General_Logout', null, array('module' => $module, 'action' => 'logout', 'idSite' => null), 1000, Piwik::translate('General_Logout'));
        }
    }

    private function getLoginModule()
    {
        return Piwik::getLoginPluginName();
    }

}
