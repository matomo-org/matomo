<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuReporting;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (UserCountry::isGeoLocationAdminEnabled() && Piwik::hasUserSuperUserAccess()) {
            $menu->addSettingsItem('UserCountry_Geolocation',
                                   $this->urlForAction('adminIndex'),
                                   $order = 8);
        }
    }

    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->addVisitorsItem('UserCountry_SubmenuLocations', $this->urlForAction('index'));
    }
}
