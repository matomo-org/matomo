<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Dashboard;

use Exception;
use Piwik\Common;
use Piwik\Db;
use Piwik\Menu\MenuAbstract;
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
        $menu->add('Dashboard_Dashboard', '', array('module' => 'Dashboard', 'action' => 'embeddedIndex', 'idDashboard' => 1), true, 5);

        if (!Piwik::isUserIsAnonymous()) {
            $login = Piwik::getCurrentUserLogin();

            $dashboard  = new Dashboard();
            $dashboards = $dashboard->getAllDashboards($login);

            $pos = 0;
            foreach ($dashboards as $dashboard) {
                $menu->add('Dashboard_Dashboard', $dashboard['name'], array('module' => 'Dashboard', 'action' => 'embeddedIndex', 'idDashboard' => $dashboard['iddashboard']), true, $pos);
                $pos++;
            }
        }
    }

    public function configureTopMenu(MenuTop $menu)
    {
        $userPreferences = new UserPreferences();
        $idSite = $userPreferences->getDefaultWebsiteId();

        $tooltip = Piwik::translate('Dashboard_TopLinkTooltip', Site::getNameFor($idSite));

        $urlParams = array(
            'module' => 'CoreHome',
            'action' => 'index',
            'idSite' => $idSite,
        );

        $menu->add('Dashboard_Dashboard', null, $urlParams, true, 1, $tooltip);
    }
}

