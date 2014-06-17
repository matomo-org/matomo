<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\WidgetsList;
use Piwik\Piwik;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $widgetContinentLabel = Piwik::translate('UserCountry_WidgetLocation')
            . ' (' . Piwik::translate('UserCountry_Continent') . ')';
        $widgetCountryLabel = Piwik::translate('UserCountry_WidgetLocation')
            . ' (' . Piwik::translate('UserCountry_Country') . ')';
        $widgetRegionLabel = Piwik::translate('UserCountry_WidgetLocation')
            . ' (' . Piwik::translate('UserCountry_Region') . ')';
        $widgetCityLabel = Piwik::translate('UserCountry_WidgetLocation')
            . ' (' . Piwik::translate('UserCountry_City') . ')';

        $category   = 'General_Visitors';
        $controller = 'UserCountry';

        WidgetsList::add($category, $widgetContinentLabel, $controller, 'getContinent');
        WidgetsList::add($category, $widgetCountryLabel, $controller, 'getCountry');
        WidgetsList::add($category, $widgetRegionLabel, $controller, 'getRegion');
        WidgetsList::add($category, $widgetCityLabel, $controller, 'getCity');
    }

}
