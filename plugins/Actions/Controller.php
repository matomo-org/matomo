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
use Piwik\Plugins\Actions\Reports\GetPageUrlsFollowingSiteSearch;
use Piwik\Plugins\Actions\Reports\GetSiteSearchCategories;
use Piwik\Plugins\Actions\Reports\GetSiteSearchKeywords;
use Piwik\Plugins\Actions\Reports\GetSiteSearchNoResultKeywords;
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

        $keyword  = new GetSiteSearchKeywords();
        $noResult = new GetSiteSearchNoResultKeywords();
        $pageUrls = new GetPageUrlsFollowingSiteSearch();

        $view->keywords = $keyword->render();
        $view->noResultKeywords = $noResult->render();
        $view->pagesUrlsFollowingSiteSearch = $pageUrls->render();

        $categoryTrackingEnabled = Actions::isCustomVariablesPluginsEnabled();
        if ($categoryTrackingEnabled) {
            $categories = new GetSiteSearchCategories();
            $view->categories = $categories->render();
        }

        return $view->render();
    }

}
