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
}