<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\Db;
use Piwik\Menu\MenuReporting;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UserPreferences;
use Piwik\Site;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->addItem('Dashboard_Dashboard', '', $this->urlForAction('embeddedIndex', array('idDashboard' => 1)), 5);

        if (!Piwik::isUserIsAnonymous()) {
            $login = Piwik::getCurrentUserLogin();

            $dashboard  = new Dashboard();
            $dashboards = $dashboard->getAllDashboards($login);

            $pos = 0;
            foreach ($dashboards as $dashboard) {
                $menu->addItem('Dashboard_Dashboard', $dashboard['name'], $this->urlForAction('embeddedIndex', array('idDashboard' => $dashboard['iddashboard'])), $pos);
                $pos++;
            }
        }
    }

    public function configureTopMenu(MenuTop $menu)
    {
        $userPreferences = new UserPreferences();
        $idSite = $userPreferences->getDefaultWebsiteId();
        $tooltip = Piwik::translate('Dashboard_TopLinkTooltip', Site::getNameFor($idSite));

        $urlParams = $this->urlForModuleActionWithDefaultUserParams('CoreHome', 'index') ;
        $menu->addItem('Dashboard_Dashboard', null, $urlParams, 1, $tooltip);
    }
}

