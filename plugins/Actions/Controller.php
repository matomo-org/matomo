<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\Piwik;
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

        $view->keywords = $this->getSiteSearchKeywords(true);
        $view->noResultKeywords = $this->getSiteSearchNoResultKeywords(true);
        $view->pagesUrlsFollowingSiteSearch = $this->getPageUrlsFollowingSiteSearch(true);

        $categoryTrackingEnabled = \Piwik\Plugin\Manager::getInstance()->isPluginActivated('CustomVariables');
        if ($categoryTrackingEnabled) {
            $view->categories = $this->getSiteSearchCategories(true);
        }

        return $view->render();
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
}
