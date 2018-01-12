<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountryMap\Widgets;

use Piwik\Widget\WidgetConfig;

class GetVisitorMap extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('General_Visitors');
        $config->setSubcategoryId('UserCountry_SubmenuLocations');
        $config->setName('UserCountryMap_VisitorMap');
        $config->setAction('visitorMap');
        $config->setOrder(1);
    }
}
