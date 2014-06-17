<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

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

    public function indexPageUrls()
    {
        return View::singleReport(
            Piwik::translate('General_Pages'),
            $this->getPageUrls(true));
    }

    public function indexEntryPageUrls()
    {
        return View::singleReport(
            Piwik::translate('Actions_SubmenuPagesEntry'),
            $this->getEntryPageUrls(true));
    }

    public function indexExitPageUrls()
    {
        return View::singleReport(
            Piwik::translate('Actions_SubmenuPagesExit'),
            $this->getExitPageUrls(true));
    }

    public function indexPageTitles()
    {
        return View::singleReport(
            Piwik::translate('Actions_SubmenuPageTitles'),
            $this->getPageTitles(true));
    }

    public function indexDownloads()
    {
        return View::singleReport(
            Piwik::translate('General_Downloads'),
            $this->getDownloads(true));
    }

    public function indexOutlinks()
    {
        return View::singleReport(
            Piwik::translate('General_Outlinks'),
            $this->getOutlinks(true));
    }

    //
    // Actions that render individual reports
    //

    public function getPageUrls()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getEntryPageUrls()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getExitPageUrls()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getSiteSearchKeywords()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getSiteSearchNoResultKeywords()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getSiteSearchCategories()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getPageUrlsFollowingSiteSearch()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getPageTitlesFollowingSiteSearch()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getPageTitles()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getEntryPageTitles()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getExitPageTitles()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getDownloads()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getOutlinks()
    {
        return $this->renderReport(__FUNCTION__);
    }

}
