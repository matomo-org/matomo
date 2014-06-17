<?php
/**
 * Piwik - free/libre analytics platform
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
        $category   = 'General_Actions';
        $controller = 'Actions';

        $widgetsList->add($category, 'General_Pages', $controller, 'getPageUrls');
        $widgetsList->add($category, 'Actions_WidgetPageTitles', $controller, 'getPageTitles');
        $widgetsList->add($category, 'General_Outlinks', $controller, 'getOutlinks');
        $widgetsList->add($category, 'General_Downloads', $controller, 'getDownloads');
        $widgetsList->add($category, 'Actions_WidgetPagesEntry', $controller, 'getEntryPageUrls');
        $widgetsList->add($category, 'Actions_WidgetPagesExit', $controller, 'getExitPageUrls');
        $widgetsList->add($category, 'Actions_WidgetEntryPageTitles', $controller, 'getEntryPageTitles');
        $widgetsList->add($category, 'Actions_WidgetExitPageTitles', $controller, 'getExitPageTitles');

        $actions = new Actions();
        if ($actions->isSiteSearchEnabled()) {
            $this->addSearchWidgets($widgetsList, $controller);
        }
    }

    private function addSearchWidgets(WidgetsList $widgetsList, $controller)
    {
        $category = 'Actions_SubmenuSitesearch';

        $widgetsList->add($category, 'Actions_WidgetSearchKeywords', $controller, 'getSiteSearchKeywords');

        if (Actions::isCustomVariablesPluginsEnabled()) {
            $widgetsList->add($category, 'Actions_WidgetSearchCategories', $controller, 'getSiteSearchCategories');
        }

        $widgetsList->add($category, 'Actions_WidgetSearchNoResultKeywords', $controller, 'getSiteSearchNoResultKeywords');
        $widgetsList->add($category, 'Actions_WidgetPageUrlsFollowingSearch', $controller, 'getPageUrlsFollowingSiteSearch');
        $widgetsList->add($category, 'Actions_WidgetPageTitlesFollowingSearch', $controller, 'getPageTitlesFollowingSiteSearch');
    }

}
