<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Changes\UserChanges;
use Piwik\Changes\Model as ChangesModel;
use Piwik\Plugins\UsersManager\Model as UsersModel;

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

        if (!Piwik::isUserIsAnonymous() && Piwik::isUserHasSomeViewAccess()) {
            $model = new UsersModel();
            $user = $model->getUser(Piwik::getCurrentUserLogin());
            if ($user) {
                $userChanges = new UserChanges($user);

                $newChangesStatus = $userChanges->getNewChangesStatus();
                if ($newChangesStatus !== ChangesModel::NO_CHANGES_EXIST) {

                    $icon = ($newChangesStatus === ChangesModel::NEW_CHANGES_EXIST ? 'icon-notifications_on' : 'icon-reporting-actions');

                    $menu->registerMenuIcon('CoreAdminHome_WhatIsNew', $icon);
                    $menu->addItem('CoreAdminHome_WhatIsNew', null, null, 990,
                        Piwik::translate('CoreAdminHome_WhatIsNewTooltip'),
                        $icon, "Piwik_Popover.createPopupAndLoadUrl('module=CoreAdminHome&action=whatIsNew', '".
                        addslashes(Piwik::translate('CoreAdminHome_WhatIsNewTooltip'))."','what-is-new-popup')");
                }
            }
        }
    }

}