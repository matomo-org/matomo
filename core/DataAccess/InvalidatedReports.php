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
use Piwik\Piwik;

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

    /**
     * Record those website IDs as having been invalidated
     *
     * @param $idSites
     */
    public function storeInvalidatedSitesAndDates($idSites)
    {
        $invalidatedIdSites = $this->getSitesToReprocess();
        $invalidatedIdSites = array_merge($invalidatedIdSites, $idSites);
        $invalidatedIdSites = array_unique($invalidatedIdSites);
        $invalidatedIdSites = array_values($invalidatedIdSites);
        $this->setSitesToReprocess($invalidatedIdSites);
    }


    /**
     * @param $idSite
     */
    public function storeSiteIsReprocessed($idSite)
    {
        $websiteIdsInvalidated = $this->getSitesToReprocess();

        if (count($websiteIdsInvalidated)) {
            $found = array_search($idSite, $websiteIdsInvalidated);
            if ($found !== false) {
                unset($websiteIdsInvalidated[$found]);
                $this->setSitesToReprocess($websiteIdsInvalidated);
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
        Option::clearCachedOption(self::OPTION_INVALIDATED_IDSITES_TO_REPROCESS);
        $invalidatedIdSites = Option::get(self::OPTION_INVALIDATED_IDSITES_TO_REPROCESS);

        if ($invalidatedIdSites
            && ($invalidatedIdSites = unserialize($invalidatedIdSites))
            && count($invalidatedIdSites)
        ) {
            return $invalidatedIdSites;
        }
        return array();
    }

    /**
     * @param $websiteIdsInvalidated
     */
    private function setSitesToReprocess($websiteIdsInvalidated)
    {
        Option::set(self::OPTION_INVALIDATED_IDSITES_TO_REPROCESS, serialize($websiteIdsInvalidated));
    }


}