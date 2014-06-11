<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{

    public function configure(WidgetsList $widgetsList)
    {
        $actions = new Actions();
        if ($actions->isSiteSearchEnabled()) {
            $this->addSearchWidgets($widgetsList);
        }
    }

    private function addSearchWidgets(WidgetsList $widgetsList)
    {
        $controller = 'Actions';
        $category   = 'Actions_SubmenuSitesearch';

        $widgetsList->add($category, 'Actions_WidgetSearchKeywords', $controller, 'getSiteSearchKeywords');

        if (Actions::isCustomVariablesPluginsEnabled()) {
            $widgetsList->add($category, 'Actions_WidgetSearchCategories', $controller, 'getSiteSearchCategories');
        }

        $widgetsList->add($category, 'Actions_WidgetSearchNoResultKeywords', $controller, 'getSiteSearchNoResultKeywords');
        $widgetsList->add($category, 'Actions_WidgetPageUrlsFollowingSearch', $controller, 'getPageUrlsFollowingSiteSearch');
        $widgetsList->add($category, 'Actions_WidgetPageTitlesFollowingSearch', $controller, 'getPageTitlesFollowingSiteSearch');
    }

}
