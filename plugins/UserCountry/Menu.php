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
        if (UserCountry::isGeoLocationAdminEnabled()) {
            $menu->add('General_Settings', 'UserCountry_Geolocation',
                array('module' => 'UserCountry', 'action' => 'adminIndex'),
                Piwik::hasUserSuperUserAccess(),
                $order = 8);
        }
    }

    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->add('General_Visitors', 'UserCountry_SubmenuLocations', array('module' => 'UserCountry', 'action' => 'index'));
    }
}
