<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Actions
 */
use Piwik\Controller;
use Piwik\ViewDataTable;
use Piwik\View;

/**
 * Actions controller
 *
 * @package Piwik_Actions
 */
class Piwik_Actions_Controller extends Controller
{
    // 
    // Actions that render whole pages
    // 
    
    public function indexPageUrls($fetch = false)
    {
        return View::singleReport(
            Piwik_Translate('Actions_SubmenuPages'),
            $this->getPageUrls(true), $fetch);
    }

    public function indexEntryPageUrls($fetch = false)
    {
        return View::singleReport(
            Piwik_Translate('Actions_SubmenuPagesEntry'),
            $this->getEntryPageUrls(true), $fetch);
    }

    public function indexExitPageUrls($fetch = false)
    {
        return View::singleReport(
            Piwik_Translate('Actions_SubmenuPagesExit'),
            $this->getExitPageUrls(true), $fetch);
    }

    public function indexSiteSearch()
    {
        $view = new View('@Actions/indexSiteSearch');

        $view->keywords = $this->getSiteSearchKeywords(true);
        $view->noResultKeywords = $this->getSiteSearchNoResultKeywords(true);
        $view->pagesUrlsFollowingSiteSearch = $this->getPageUrlsFollowingSiteSearch(true);

        $categoryTrackingEnabled = \Piwik\PluginsManager::getInstance()->isPluginActivated('CustomVariables');
        if ($categoryTrackingEnabled) {
            $view->categories = $this->getSiteSearchCategories(true);
        }

        echo $view->render();
    }

    public function indexPageTitles($fetch = false)
    {
        return View::singleReport(
            Piwik_Translate('Actions_SubmenuPageTitles'),
            $this->getPageTitles(true), $fetch);
    }

    public function indexDownloads($fetch = false)
    {
        return View::singleReport(
            Piwik_Translate('Actions_SubmenuDownloads'),
            $this->getDownloads(true), $fetch);
    }

    public function indexOutlinks($fetch = false)
    {
        return View::singleReport(
            Piwik_Translate('Actions_SubmenuOutlinks'),
            $this->getOutlinks(true), $fetch);
    }
    
    // 
    // Actions that render individual reports
    // 
    
    public function getPageUrls($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }
    
    public function getEntryPageUrls($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getExitPageUrls($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getSiteSearchKeywords($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getSiteSearchNoResultKeywords($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getSiteSearchCategories($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getPageUrlsFollowingSiteSearch($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getPageTitlesFollowingSiteSearch($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getPageTitles($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getEntryPageTitles($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getExitPageTitles($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getDownloads($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getOutlinks($fetch = false)
    {
        return ViewDataTable::render($this->pluginName, __FUNCTION__, $fetch);
    }
}
