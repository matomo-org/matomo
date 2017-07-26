<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Archive;

use Piwik\Archive;
use Piwik\Period;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Period\Factory as PeriodFactory;

class ArchiveQueryFactory
{
    public function build($idSites, $period, $strDate, $segment = false, $_restrictSitesToLogin = false)
    {
        $websiteIds = Site::getIdSitesFromIdSitesString($idSites, $_restrictSitesToLogin);

        $timezone = false;
        if (count($websiteIds) == 1) {
            $timezone = Site::getTimezoneFor($websiteIds[0]);
        }

        if (Period::isMultiplePeriod($strDate, $period)) {
            $oPeriod    = PeriodFactory::build($period, $strDate, $timezone);
            $allPeriods = $oPeriod->getSubperiods();
        } else {
            $oPeriod    = PeriodFactory::makePeriodFromQueryParams($timezone, $period, $strDate);
            $allPeriods = array($oPeriod);
        }

        $segment        = new Segment($segment, $websiteIds);
        $idSiteIsAll    = $idSites == Archive::REQUEST_ALL_WEBSITES_FLAG;
        $isMultipleDate = Period::isMultiplePeriod($strDate, $period);

        return $this->factory($segment, $allPeriods, $websiteIds, $idSiteIsAll, $isMultipleDate);
    }

    public function factory($segment, $periods, $idSites, $idSiteIsAll, $isMultipleDate)
    {
        $forceIndexedBySite = false;
        $forceIndexedByDate = false;

        if ($idSiteIsAll || count($idSites) > 1) {
            $forceIndexedBySite = true;
        }

        if (count($periods) > 1 || $isMultipleDate) {
            $forceIndexedByDate = true;
        }

        $params = new Parameters($idSites, $periods, $segment);

        return $this->newArchiveInstance($params, $forceIndexedBySite, $forceIndexedByDate);
    }

    protected function newArchiveInstance(Parameters $params, $forceIndexedBySite, $forceIndexedByDate)
    {
        return new Archive($params, $forceIndexedBySite, $forceIndexedByDate);
    }
}