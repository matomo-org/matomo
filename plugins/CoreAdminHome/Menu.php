<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Db;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\CoreHome;
use Piwik\Plugins\UsersManager\Model AS UsersModel;

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

        if (Piwik::isUserHasSomeAdminAccess()) {
            $menu->addDiagnosticItem('CoreAdminHome_TrackingFailures',
                $this->urlForAction('trackingFailures'),
                $order = 2);
        }
    }

    public function configureTopMenu(MenuTop $menu)
    {
        $url = $this->urlForModuleAction('CoreAdminHome', 'home');
        $menu->registerMenuIcon('CoreAdminHome_Administration', 'icon-settings');
        $menu->addItem('CoreAdminHome_Administration', null, $url, 980, Piwik::translate('CoreAdminHome_Administration'));

        $changes = \Piwik\Plugins\CoreHome\Controller::getChanges();
        if (!Piwik::isUserIsAnonymous() && count($changes['changes']) > 0) {

            $model = new UsersModel();
            $user = $model->getUser(Piwik::getCurrentUserLogin());

            $icon = (isset($user['ts_changes_viewed']) && $user['ts_changes_viewed'] > $changes['latestDate'].' 00:00:00'
                     ? 'icon-reporting-actions' : 'icon-notifications_on');

            $menu->registerMenuIcon('CoreAdminHome_WhatIsNew', $icon);
            $menu->addItem('CoreAdminHome_WhatIsNew', null, '', 990, Piwik::translate('CoreAdminHome_WhatIsNewTooltip'),
                      $icon,false, 'matomo-what-is-new');
        }
    }

}