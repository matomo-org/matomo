<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\Plugin\Report;
use Piwik\View;

/**
 * Actions controller
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    //
    // Actions that render whole pages
    //

    public function indexSiteSearch()
    {
        $view = new View('@Actions/indexSiteSearch');

        $keyword  = Report::factory($this->pluginName, 'getSiteSearchKeywords');
        $noResult = Report::factory($this->pluginName, 'getSiteSearchNoResultKeywords');
        $pageUrls = Report::factory($this->pluginName, 'getPageUrlsFollowingSiteSearch');

        $view->keywords = $keyword->render();
        $view->noResultKeywords = $noResult->render();
        $view->pagesUrlsFollowingSiteSearch = $pageUrls->render();

        $categoryTrackingEnabled = Actions::isCustomVariablesPluginsEnabled();
        if ($categoryTrackingEnabled) {
            $categories = Report::factory($this->pluginName, 'getSiteSearchCategories');
            $view->categories = $categories->render();
        }

        return $view->render();
    }

}
