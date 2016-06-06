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
use Piwik\Menu\MenuUser;
use Piwik\Piwik;
use Piwik\Settings\Manager as SettingsManager;

class Menu extends \Piwik\Plugin\Menu
{

    public function configureAdminMenu(MenuAdmin $menu)
    {
        $hasAdminAccess = Piwik::isUserHasSomeAdminAccess();

        if ($hasAdminAccess) {
            $menu->addManageItem(null, array(), $order = 1);
            $menu->addSettingsItem(null, array(), $order = 5);
            $menu->addDiagnosticItem(null, array(), $order = 10);
            $menu->addDevelopmentItem(null, array(), $order = 15);

            if (Piwik::hasUserSuperUserAccess()) {
                $menu->addSettingsItem('General_General',
                    $this->urlForAction('generalSettings'),
                    $order = 6);
            }
        }

        if (Piwik::hasUserSuperUserAccess() && SettingsManager::hasSystemPluginsSettingsForCurrentUser()) {
            $menu->addSettingsItem('CoreAdminHome_PluginSettings',
                                   $this->urlForAction('adminPluginSettings'),
                                   $order = 7);
        }
    }

    public function configureTopMenu(MenuTop $menu)
    {
        if (Piwik::isUserHasSomeAdminAccess()) {
            $url = $this->urlForModuleAction('SitesManager', 'index');

            if (Piwik::hasUserSuperUserAccess()) {
                $url = $this->urlForAction('generalSettings');
            }

            $menu->registerMenuIcon('CoreAdminHome_Administration', 'icon-configure');
            $menu->addItem('CoreAdminHome_Administration', null, $url, 980, Piwik::translate('CoreAdminHome_Administration'));
        }
    }

    public function configureUserMenu(MenuUser $menu)
    {
        if (!Piwik::isUserIsAnonymous()) {
            $menu->addManageItem('CoreAdminHome_TrackingCode',
                $this->urlForAction('trackingCodeGenerator'),
                $order = 20);

            if (SettingsManager::hasUserPluginsSettingsForCurrentUser()) {
                $menu->addPersonalItem('CoreAdminHome_PluginSettings',
                    $this->urlForAction('userPluginSettings'),
                    $order = 15);
            }
        }
    }

}
