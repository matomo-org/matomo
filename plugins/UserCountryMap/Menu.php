<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountryMap;

use Piwik\Menu\MenuReporting;
use Piwik\Plugin\Manager as PluginManager;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        if (PluginManager::getInstance()->isPluginActivated('UserCountry')) {
            $menu->addVisitorsItem('UserCountryMap_RealTimeMap',
                                   $this->urlForAction('realtimeWorldMap'),
                                   $order = 70);
        }
    }
}
