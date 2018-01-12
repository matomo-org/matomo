<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (UserCountry::isGeoLocationAdminEnabled() && Piwik::hasUserSuperUserAccess()) {
            $menu->addSystemItem('UserCountry_Geolocation',
                                 $this->urlForAction('adminIndex'),
                                 $order = 30);
        }
    }
}
