<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\plugins\CoreAdminHome;

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
     * Returns array of idSites to force re-process next time core:archive command runs
     *
     * @ignore
     * @return mixed
     */
    public function getWebsiteIdsToInvalidate()
    {
        Piwik::checkUserHasSomeAdminAccess();

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
     * Force to re-process data for these websites in the next cron core:archive command run
     *
     * @param $idSites
     */
    public function setSiteIdsToBeInvalidated($idSites)
    {
        $store = new InvalidatedReports();
        $invalidatedIdSites = $store->getWebsiteIdsToInvalidate();
        $invalidatedIdSites = array_merge($invalidatedIdSites, $idSites);
        $invalidatedIdSites = array_unique($invalidatedIdSites);
        $invalidatedIdSites = array_values($invalidatedIdSites);
        Option::set(self::OPTION_INVALIDATED_IDSITES_TO_REPROCESS, serialize($invalidatedIdSites));
    }


    /**
     * @param $idSite
     */
    public function removeWebsiteFromInvalidatedWebsites($idSite)
    {
        $websiteIdsInvalidated = $this->getWebsiteIdsToInvalidate();

        if (count($websiteIdsInvalidated)) {
            $found = array_search($idSite, $websiteIdsInvalidated);
            if ($found !== false) {
                unset($websiteIdsInvalidated[$found]);
                Option::set(self::OPTION_INVALIDATED_IDSITES_TO_REPROCESS, serialize($websiteIdsInvalidated));
            }
        }
    }

}