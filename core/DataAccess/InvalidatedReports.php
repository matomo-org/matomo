<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\DataAccess;

use Piwik\Option;

/**
 * Keeps track of which reports were invalidated via CoreAdminHome.invalidateArchivedReports API.
 *
 * This is used by:
 *
 * 1. core:archive command to know which websites should be reprocessed
 *
 * 2. scheduled task purgeInvalidatedArchives to know which websites/months should be purged
 *
 */
class InvalidatedReports
{
    const OPTION_INVALIDATED_IDSITES_TO_REPROCESS = 'InvalidatedOldReports_WebsiteIds';
    const OPTION_INVALIDATED_DATES_SITES_TO_PURGE = 'InvalidatedOldReports_DatesWebsiteIds';

    /**
     * Mark the sites IDs and Dates as being invalidated, so we can purge them later on.
     *
     * @param array $idSites
     * @param array $yearMonths
     */
    public function addSitesToPurgeForYearMonths(array $idSites, $yearMonths)
    {
        $idSitesByYearMonth = $this->getSitesByYearMonthToPurge();

        foreach($yearMonths as $yearMonthToPurge) {

            if(isset($idSitesByYearMonth[$yearMonthToPurge])) {
                $existingIdSitesToPurge = $idSitesByYearMonth[$yearMonthToPurge];
                $idSites = array_merge($existingIdSitesToPurge, $idSites);
                $idSites = array_unique($idSites);
            }
            $idSitesByYearMonth[$yearMonthToPurge] = $idSites;
        }
        $this->persistSitesByYearMonthToPurge($idSitesByYearMonth);
    }

    /**
     * Returns the list of websites IDs for which invalidated archives can be purged.
     */
    public function getSitesByYearMonthArchiveToPurge()
    {
        $idSitesByYearMonth = $this->getSitesByYearMonthToPurge();

        // From this list we remove the websites that are not yet re-processed
        // so we don't purge them before they were re-processed
        $idSitesNotYetReprocessed = $this->getSitesToReprocess();

        foreach($idSitesByYearMonth as $yearMonth => &$idSites) {
            $idSites = array_diff($idSites, $idSitesNotYetReprocessed);
        }
        return $idSitesByYearMonth;
    }

    public function markSiteIdsHaveBeenPurged(array $idSites, $yearMonth)
    {
        $idSitesByYearMonth = $this->getSitesByYearMonthToPurge();

        if(!isset($idSitesByYearMonth[$yearMonth])) {
            return;
        }

        $idSitesByYearMonth[$yearMonth] = array_diff($idSitesByYearMonth[$yearMonth], $idSites);
        $this->persistSitesByYearMonthToPurge($idSitesByYearMonth);
    }

    /**
     * Record those website IDs as having been invalidated
     *
     * @param $idSites
     */
    public function addInvalidatedSitesToReprocess(array $idSites)
    {
        $siteIdsToReprocess = $this->getSitesToReprocess();
        $siteIdsToReprocess = array_merge($siteIdsToReprocess, $idSites);
        $this->setSitesToReprocess($siteIdsToReprocess);
    }


    /**
     * @param $idSite
     */
    public function storeSiteIsReprocessed($idSite)
    {
        $siteIdsToReprocess = $this->getSitesToReprocess();

        if (count($siteIdsToReprocess)) {
            $found = array_search($idSite, $siteIdsToReprocess);
            if ($found !== false) {
                unset($siteIdsToReprocess[$found]);
                $this->setSitesToReprocess($siteIdsToReprocess);
            }
        }
    }

    /**
     * Returns array of idSites to force re-process next time core:archive command runs
     *
     * @return array of id sites
     */
    public function getSitesToReprocess()
    {
        return $this->getArrayValueFromOptionName(self::OPTION_INVALIDATED_IDSITES_TO_REPROCESS);
    }

    /**
     * @return array|false|mixed|string
     */
    private function getSitesByYearMonthToPurge()
    {
        return $this->getArrayValueFromOptionName(self::OPTION_INVALIDATED_DATES_SITES_TO_PURGE);
    }

    /**
     * @param $websiteIdsInvalidated
     */
    private function setSitesToReprocess($websiteIdsInvalidated)
    {
        $websiteIdsInvalidated = array_unique($websiteIdsInvalidated);
        $websiteIdsInvalidated = array_values($websiteIdsInvalidated);
        Option::set(self::OPTION_INVALIDATED_IDSITES_TO_REPROCESS, serialize($websiteIdsInvalidated));
    }

    /**
     * @param $optionName
     * @return array|false|mixed|string
     */
    private function getArrayValueFromOptionName($optionName)
    {
        Option::clearCachedOption($optionName);
        $array = Option::get($optionName);

        if ($array
            && ($array = unserialize($array))
            && count($array)
        ) {
            return $array;
        }
        return array();
    }

    /**
     * @param $idSitesByYearMonth
     */
    private function persistSitesByYearMonthToPurge($idSitesByYearMonth)
    {
        // remove dates for which there are no sites to purge
        $idSitesByYearMonth = array_filter($idSitesByYearMonth);

        Option::set(self::OPTION_INVALIDATED_DATES_SITES_TO_PURGE, serialize($idSitesByYearMonth));
    }



}