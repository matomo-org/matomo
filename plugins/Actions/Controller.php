<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\Common;
use Piwik\Piwik;
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

    /**
     * @deprecated since 2.5.0
     */
    public function indexPageUrls()
    {
        $this->redirectForBackwardsCompatibility('getPageUrls');
    }

    public function indexEntryPageUrls()
    {
        $this->redirectForBackwardsCompatibility('getEntryPageUrls');
    }

    public function indexExitPageUrls()
    {
        $this->redirectForBackwardsCompatibility('getExitPageUrls');
    }

    public function indexPageTitles()
    {
        $this->redirectForBackwardsCompatibility('getPageTitles');
    }

    public function indexDownloads()
    {
        $this->redirectForBackwardsCompatibility('getDownloads');
    }

    public function indexOutlinks()
    {
        $this->redirectForBackwardsCompatibility('getOutlinks');
    }

    private function redirectForBackwardsCompatibility($reportAction)
    {
        $idSite = Common::getRequestVar('idSite', false, 'int');
        $date   = Common::getRequestVar('date', false, 'string');
        $period = Common::getRequestVar('period', false, 'string');
        $this->redirectToIndex('CoreHome', 'renderMenuReport', $idSite, $period, $date, array('reportModule' => 'Actions', 'reportAction' => $reportAction));
    }

    //
    // Actions that render individual reports
    //

}
