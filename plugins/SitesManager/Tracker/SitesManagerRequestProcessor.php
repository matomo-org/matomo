<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
        $idSite = $request->getIdSite();

        $createdTimeTimestamp = $this->getSiteCreatedTime($idSite);
        if (empty($createdTimeTimestamp)) {
            return;
        }

        $requestTimestamp = Date::factory((int) $request->getCurrentTimestamp());

        // replicating old Piwik logic, see:
        // https://github.com/piwik/piwik/blob/baa6da86266c7c44bc2d65821c7ffe042c2f4716/core/Archive/ArchiveInvalidator.php#L150
        // before when this was done during archive invalidation, the date would not have an attached time and
        // one extra day was subtracted from the minimum.
        // I am not sure why this is required or if it is still required, but some tests that check the contents
        // of archive tables will fail w/o this.
        $requestTimestamp = $requestTimestamp->subDay(1)->setTime('00:00:00');

        if ($requestTimestamp->isEarlier($createdTimeTimestamp)) {
            $this->updateSiteCreatedTime($idSite, $requestTimestamp);
        }
    }

    private function getSiteCreatedTime($idSite)
    {
        $attributes = Cache::getCacheWebsiteAttributes($idSite);
        if (!isset($attributes['ts_created'])) {
            return null;
        }

        return Date::factory($attributes['ts_created']);
    }

    private function updateSiteCreatedTime($idSite, Date $timestamp)
    {
        $this->sitesManagerModel->updateSiteCreatedTime(array($idSite), $timestamp->getDatetime());
        Cache::deleteCacheWebsiteAttributes($idSite);
    }
}