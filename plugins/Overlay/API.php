<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Overlay;

use Exception;
use Piwik\Access;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\SitesManager\SitesManager;
use Piwik\Plugins\Transitions\API as APITransitions;
use Piwik\Tracker\PageUrl;

/**
 * Class API
 * @method static \Piwik\Plugins\Overlay\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Get translation strings
     */
    public function getTranslations($idSite)
    {
        $this->authenticate($idSite);

        $translations = array(
            'oneClick'         => 'Overlay_OneClick',
            'clicks'           => 'Overlay_Clicks',
            'clicksFromXLinks' => 'Overlay_ClicksFromXLinks',
            'link'             => 'Overlay_Link'
        );

        return array_map(array('\\Piwik\\Piwik','translate'), $translations);
    }

    /**
     * Get excluded query parameters for a site.
     * This information is used for client side url normalization.
     */
    public function getExcludedQueryParameters($idSite)
    {
        $this->authenticate($idSite);

        $sitesManager = APISitesManager::getInstance();
        $site = $sitesManager->getSiteFromId($idSite);

        try {
            return SitesManager::getTrackerExcludedQueryParameters($site);
        } catch (Exception $e) {
            // an exception is thrown when the user has no view access.
            // do not throw the exception to the outside.
            return array();
        }
    }

    /**
     * Get following pages of a url.
     * This is done on the logs - not the archives!
     *
     * Note: if you use this method via the regular API, the number of results will be limited.
     * Make sure, you set filter_limit=-1 in the request.
     */
    public function getFollowingPages($url, $idSite, $period, $date, $segment = false)
    {
        $this->authenticate($idSite);

        $url = PageUrl::excludeQueryParametersFromUrl($url, $idSite);
        // we don't unsanitize $url here. it will be done in the Transitions plugin.

        $resultDataTable = new DataTable;

        try {
            $limitBeforeGrouping = Config::getInstance()->General['overlay_following_pages_limit'];
            $transitionsReport = APITransitions::getInstance()->getTransitionsForAction(
                $url, $type = 'url', $idSite, $period, $date, $segment, $limitBeforeGrouping,
                $part = 'followingActions', $returnNormalizedUrls = true);
        } catch (Exception $e) {
            return $resultDataTable;
        }

        $reports = array('followingPages', 'outlinks', 'downloads');
        foreach ($reports as $reportName) {
            if (!isset($transitionsReport[$reportName])) {
                continue;
            }
            foreach ($transitionsReport[$reportName]->getRows() as $row) {
                // don't touch the row at all for performance reasons
                $resultDataTable->addRow($row);
            }
        }

        return $resultDataTable;
    }

    /** Do cookie authentication. This way, the token can remain secret. */
    private function authenticate($idSite)
    {
        /**
         * Triggered immediately before the user is authenticated.
         *
         * This event can be used by plugins that provide their own authentication mechanism
         * to make that mechanism available. Subscribers should set the `'Piwik\Auth'` object in
         * the container to an object that implements the {@link Piwik\Auth} interface.
         *
         * **Example**
         *
         *     use Piwik\Container\StaticContainer;
         *
         *     public function initAuthenticationObject($activateCookieAuth)
         *     {
         *         StaticContainer::getContainer()->set('Piwik\Auth', new LDAPAuth($activateCookieAuth));
         *     }
         *
         * @param bool $activateCookieAuth Whether authentication based on `$_COOKIE` values should
         *                                        be allowed.
         */
        Piwik::postEvent('Request.initAuthenticationObject', array($activateCookieAuth = true));

        $auth = StaticContainer::get('Piwik\Auth');
        $success = Access::getInstance()->reloadAccess($auth);

        if (!$success) {
            throw new Exception('Authentication failed');
        }

        Piwik::checkUserHasViewAccess($idSite);
    }
}
