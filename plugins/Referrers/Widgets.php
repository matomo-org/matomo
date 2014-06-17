<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\SettingsPiwik;
use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $category   = 'Referrers_Referrers';
        $controller = 'Referrers';

        $widgetsList->add($category, 'Referrers_WidgetKeywords', $controller, 'getKeywords');
        $widgetsList->add($category, 'Referrers_WidgetExternalWebsites', $controller, 'getWebsites');
        $widgetsList->add($category, 'Referrers_WidgetSocials', $controller, 'getSocials');
        $widgetsList->add($category, 'Referrers_SearchEngines', $controller, 'getSearchEngines');
        $widgetsList->add($category, 'Referrers_Campaigns', $controller, 'getCampaigns');
        $widgetsList->add($category, 'General_Overview', $controller, 'getReferrerType');
        $widgetsList->add($category, 'Referrers_WidgetGetAll', $controller, 'getAll');

        if (SettingsPiwik::isSegmentationEnabled()) {
            $widgetsList->add('SEO', 'Referrers_WidgetTopKeywordsForPages', $controller, 'getKeywordsForPage');
        }
    }

}
