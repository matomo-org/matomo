<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
    public function __construct()
    {
        // empty
    }

    /**
     * @see \Piwik\Archive::build()
     */
    public function build($idSites, $strPeriod, $strDate, $strSegment = false, $_restrictSitesToLogin = false)
    {
        list($websiteIds, $timezone, $idSiteIsAll) = $this->getSiteInfoFromQueryParam($idSites, $_restrictSitesToLogin);
        list($allPeriods, $isMultipleDate) = $this->getPeriodInfoFromQueryParam($strDate, $strPeriod, $timezone);
        $segment = $this->getSegmentFromQueryParam($strSegment, $websiteIds, $allPeriods);

        return $this->factory($segment, $allPeriods, $websiteIds, $idSiteIsAll, $isMultipleDate);
    }

    /**
     * @see \Piwik\Archive::factory()
     */
    public function factory(Segment $segment, array $periods, array $idSites, $idSiteIsAll = false, $isMultipleDate = false)
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

        return $this->newInstance($params, $forceIndexedBySite, $forceIndexedByDate);
    }

    public function newInstance(Parameters $params, $forceIndexedBySite, $forceIndexedByDate)
    {
        return new Archive($params, $forceIndexedBySite, $forceIndexedByDate);
    }

    /**
     * Parses the site ID string provided in the 'idSite' query parameter to a list of
     * website IDs.
     *
     * @param string $idSites the value of the 'idSite' query parameter
     * @param bool $_restrictSitesToLogin
     * @return array an array containing three elements:
     *     - an array of website IDs
     *     - string timezone to use (or false to use no timezone) when creating periods.
     *     - true if the request was for all websites (this forces the archive result to
     *       be indexed by site, even if there is only one site in Piwik)
     */
    protected function getSiteInfoFromQueryParam($idSites, $_restrictSitesToLogin)
    {
        $websiteIds = Site::getIdSitesFromIdSitesString($idSites, $_restrictSitesToLogin);

        $timezone = false;
        if (count($websiteIds) === 1) {
            $timezone = Site::getTimezoneFor($websiteIds[0]);
        }

        $idSiteIsAll = $idSites === Archive::REQUEST_ALL_WEBSITES_FLAG;

        return [$websiteIds, $timezone, $idSiteIsAll];
    }

    /**
     * Parses the date & period query parameters into a list of periods.
     *
     * @param string $strDate the value of the 'date' query parameter
     * @param string $strPeriod the value of the 'period' query parameter
     * @param string $timezone the timezone to use when constructing periods.
     * @return array an array containing two elements:
     *     - the list of period objects to query archive data for
     *     - true if the request was for multiple periods (ie, two months, two weeks, etc.), false if otherwise.
     *       (this forces the archive result to be indexed by period, even if the list of periods
     *       has only one period).
     */
    protected function getPeriodInfoFromQueryParam($strDate, $strPeriod, $timezone)
    {
        if (Period::isMultiplePeriod($strDate, $strPeriod)) {
            $oPeriod    = PeriodFactory::build($strPeriod, $strDate, $timezone);
            $allPeriods = $oPeriod->getSubperiods();
        } else {
            $oPeriod    = PeriodFactory::makePeriodFromQueryParams($timezone, $strPeriod, $strDate);
            $allPeriods = array($oPeriod);
        }

        $isMultipleDate = Period::isMultiplePeriod($strDate, $strPeriod);

        return [$allPeriods, $isMultipleDate];
    }

    /**
     * Parses the segment query parameter into a Segment object.
     *
     * @param string $strSegment the value of the 'segment' query parameter.
     * @param int[] $websiteIds the list of sites being queried.
     * @param Period[] $allPeriods list of all periods
     * @return Segment
     */
    protected function getSegmentFromQueryParam($strSegment, $websiteIds, $allPeriods)
    {
        // we might have multiple periods, so use the start date of the first one and
        // the end date of the last one to limit the possible segment subquery
        return new Segment($strSegment, $websiteIds, reset($allPeriods)->getDateTimeStart(), end($allPeriods)->getDateTimeEnd());
    }
}