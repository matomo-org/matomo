<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Overlay
 */

class Piwik_Overlay_API
{

    private static $instance = null;

    /**
     * Get Singleton instance
     * @return Piwik_Overlay_API
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

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

        return array_map('Piwik_Translate', $translations);
    }

    /**
     * Get excluded query parameters for a site.
     * This information is used for client side url normalization.
     */
    public function getExcludedQueryParameters($idSite)
    {
        $this->authenticate($idSite);

        $sitesManager = Piwik_SitesManager_API::getInstance();
        $site = $sitesManager->getSiteFromId($idSite);

        try {
            return Piwik_SitesManager::getTrackerExcludedQueryParameters($site);
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

        $url = Piwik_Tracker_Action::excludeQueryParametersFromUrl($url, $idSite);
        // we don't unsanitize $url here. it will be done in the Transitions plugin.

        $resultDataTable = new Piwik_DataTable;

        try {
            $limitBeforeGrouping = Piwik_Config::getInstance()->General['overlay_following_pages_limit'];
            $transitionsReport = Piwik_Transitions_API::getInstance()->getTransitionsForAction(
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
        $notification = null;
        Piwik_PostEvent('FrontController.initAuthenticationObject', $notification, $allowCookieAuthentication = true);

        $auth = Zend_Registry::get('auth');
        $success = Zend_Registry::get('access')->reloadAccess($auth);

        if (!$success) {
            throw new Exception('Authentication failed');
        }

        Piwik::checkUserHasViewAccess($idSite);
    }

}
