<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\Tracker;

use Piwik\Date;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Plugins\SitesManager\Model as SitesManagerModel;

/**
 * Handles site specific logic during tracking.
 */
class SitesManagerRequestProcessor extends RequestProcessor
{
    /**
     * @var SitesManagerModel
     */
    private $sitesManagerModel;

    public function __construct(SitesManagerModel $sitesManagerModel)
    {
        $this->sitesManagerModel = $sitesManagerModel;
    }

    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        // if we successfully record some data and the date is older than the site's created time,
        // update the created time so the data will be viewable in the UI
        $visitorNotFoundInDb = $request->getMetadata('CoreHome', 'visitorNotFoundInDb');
        if ($visitorNotFoundInDb) {
            return;
        }

        $idSite = $request->getIdSite();

        $createdTimeTimestamp = $this->getSiteCreatedTime($idSite);
        if ($request->getCurrentTimestamp() < $createdTimeTimestamp) {
            $this->updateSiteCreatedTime($idSite, $request->getCurrentTimestamp());
        }
    }

    private function getSiteCreatedTime($idSite)
    {
        $attributes = Cache::getCacheWebsiteAttributes($idSite);
        $tsCreated = isset($attributes['ts_created']) ? $attributes['ts_created'] : 0;
        return Date::factory($tsCreated)->getTimestamp();
    }

    private function updateSiteCreatedTime($idSite, $timestamp)
    {
        $this->sitesManagerModel->updateSiteCreatedTime(array($idSite), Date::factory($timestamp)->getDatetime());
        Cache::deleteCacheWebsiteAttributes($idSite);
    }
}