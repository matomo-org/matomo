<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Db;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugin;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $menu->addDevelopmentItem(null, array(), $order = 40);
        $menu->addManageItem(null, array(), $order = 1);
        $menu->addDiagnosticItem(null, array(), $order = 5);

        if (Piwik::hasUserSuperUserAccess()) {
            $menu->addManageItem('General_GeneralSettings',
                $this->urlForAction('generalSettings'),
                $order = 6);
        }

        if (!Piwik::isUserIsAnonymous()) {
            $menu->addManageItem('CoreAdminHome_TrackingCode',
                $this->urlForAction('trackingCodeGenerator'),
                $order = 25);
        }
    }

    public function configureTopMenu(MenuTop $menu)
    {
        if (Piwik::isUserIsAnonymous()) {
            if (Plugin\Manager::getInstance()->isPluginActivated('ScheduledReports')) {
                $url = $this->urlForModuleAction('ScheduledReports', 'index');
            } else {
                $url = $this->urlForModuleAction('API', 'listAllAPI');
            }
        } else {
            $url = $this->urlForModuleAction('UsersManager', 'userSettings');
        }

        $menu->registerMenuIcon('CoreAdminHome_Administration', 'icon-configure');
        $menu->addItem('CoreAdminHome_Administration', null, $url, 980, Piwik::translate('CoreAdminHome_Administration'));
    }

}