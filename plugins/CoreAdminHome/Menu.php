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
        $menu->addPersonalItem(null, array(), 1, false);
        $menu->addSystemItem(null, array(), 2, false);
        $menu->addMeasurableItem(null, array(), $order = 3);
        $menu->addPlatformItem(null, array(), 4, false);
        $menu->addDiagnosticItem(null, array(), $order = 5);
        $menu->addDevelopmentItem(null, array(), $order = 40);

        if (Piwik::hasUserSuperUserAccess()) {
            $menu->addSystemItem('General_GeneralSettings',
                $this->urlForAction('generalSettings'),
                $order = 5);
        }

        if (!Piwik::isUserIsAnonymous()) {
            $menu->addMeasurableItem('CoreAdminHome_TrackingCode',
                $this->urlForAction('trackingCodeGenerator'),
                $order = 12);
        }
    }

    public function configureTopMenu(MenuTop $menu)
    {
        $url = $this->urlForModuleAction('CoreAdminHome', 'home');

        $menu->registerMenuIcon('CoreAdminHome_Administration', 'icon-settings');
        $menu->addItem('CoreAdminHome_Administration', null, $url, 980, Piwik::translate('CoreAdminHome_Administration'));
    }

}