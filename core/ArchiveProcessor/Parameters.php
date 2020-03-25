<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\ArchiveProcessor;

use Piwik\Cache;
use Piwik\DataAccess\RawLogDao;
use Piwik\Date;
use Piwik\Log;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Site;

/**
 * Contains the analytics parameters for the reports that are currently being archived. The analytics
 * parameters include the **website** the reports describe, the **period** of time the reports describe
 * and the **segment** used to limit the visit set.
 */
class Parameters
{
    /**
     * @var Site
     */
    private $site = null;

    /**
     * @var Period
     */
    private $period = null;

    /**
     * @var Segment
     */
    private $segment = null;

    /**
     * @var string Plugin name which triggered this archive processor
     */
    private $requestedPlugin = false;

    private $onlyArchiveRequestedPlugin = false;

    /**
     * @var bool
     */
    private $isRootArchiveRequest = true;

    /**
     * @var RawLogDao
     */
    private $rawLogDao;

    /**
     * Constructor.
     *
     * @ignore
     */
    public function __construct(Site $site, Period $period, Segment $segment)
    {
        $this->site = $site;
        $this->period = $period;
        $this->segment = $segment;
        $this->rawLogDao = new RawLogDao();
    }

    /**
     * @ignore
     */
    public function setRequestedPlugin($plugin)
    {
        $this->requestedPlugin = $plugin;
    }

    /**
     * @ignore
     */
    public function onlyArchiveRequestedPlugin()
    {
        $this->onlyArchiveRequestedPlugin = true;
    }

    /**
     * @ignore
     */
    public function shouldOnlyArchiveRequestedPlugin()
    {
        return $this->onlyArchiveRequestedPlugin;
    }

    /**
     * @ignore
     */
    public function getRequestedPlugin()
    {
        return $this->requestedPlugin;
    }

    /**
     * Returns the period we are computing statistics for.
     *
     * @return Period
     * @api
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Returns the array of Period which make up this archive.
     *
     * @return \Piwik\Period[]
     * @ignore
     */
    public function getSubPeriods()
    {
        if ($this->getPeriod()->getLabel() == 'day') {
            return array( $this->getPeriod() );
        }
        return $this->getPeriod()->getSubperiods();
    }

    /**
     * @return array
     * @ignore
     */
    public function getIdSites()
    {
        $idSite = $this->getSite()->getId();

        $idSites = array($idSite);

        Piwik::postEvent('ArchiveProcessor.Parameters.getIdSites', array(&$idSites, $this->getPeriod()));

        return $idSites;
    }

    /**
     * Returns the site we are computing statistics for.
     *
     * @return Site
     * @api
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * The Segment used to limit the set of visits that are being aggregated.
     *
     * @return Segment
     * @api
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * Returns the end day of the period in the site's timezone.
     *
     * @return Date
     */
    public function getDateEnd()
    {
        return $this->getPeriod()->getDateEnd()->setTimezone($this->getSite()->getTimezone());
    }

    /**
     * Returns the start day of the period in the site's timezone.
     *
     * @return Date
     */
    public function getDateStart()
    {
        return $this->getPeriod()->getDateStart()->setTimezone($this->getSite()->getTimezone());
    }

    /**
     * Returns the start day of the period in the site's timezone (includes the time of day).
     *
     * @return Date
     */
    public function getDateTimeStart()
    {
        return $this->getPeriod()->getDateTimeStart()->setTimezone($this->getSite()->getTimezone());
    }

    /**
     * Returns the end day of the period in the site's timezone (includes the time of day).
     *
     * @return Date
     */
    public function getDateTimeEnd()
    {
        return $this->getPeriod()->getDateTimeEnd()->setTimezone($this->getSite()->getTimezone());
    }

    /**
     * @return bool
     */
    public function isSingleSiteDayArchive()
    {
        return $this->isDayArchive() && $this->isSingleSite();
    }

    /**
     * @return bool
     */
    public function isDayArchive()
    {
        $period = $this->getPeriod();
        $secondsInPeriod = $period->getDateEnd()->getTimestampUTC() - $period->getDateStart()->getTimestampUTC();
        $oneDay = $secondsInPeriod < Date::NUM_SECONDS_IN_DAY;

        return $oneDay;
    }

    public function isSingleSite()
    {
        return count($this->getIdSites()) == 1;
    }

    public function logStatusDebug()
    {
        $temporary = 'definitive archive';
        Log::debug(
            "%s archive, idSite = %d (%s), segment '%s', report = '%s', UTC datetime [%s -> %s]",
            $this->getPeriod()->getLabel(),
            $this->getSite()->getId(),
            $temporary,
            $this->getSegment()->getString(),
            $this->getRequestedPlugin(),
            $this->getDateStart()->getDateStartUTC(),
            $this->getDateEnd()->getDateEndUTC()
        );
    }

    /**
     * Returns `true` if these parameters are part of an initial archiving request.
     * Returns `false` if these parameters are for an archiving request that was initiated
     * during archiving.
     *
     * @return bool
     */
    public function isRootArchiveRequest()
    {
        return $this->isRootArchiveRequest;
    }

    /**
     * Sets whether these parameters are part of the initial archiving request or if they are
     * for a request that was initiated during archiving.
     *
     * @param $isRootArchiveRequest
     */
    public function setIsRootArchiveRequest($isRootArchiveRequest)
    {
        $this->isRootArchiveRequest = $isRootArchiveRequest;
    }

    public function __toString()
    {
        return "[idSite = {$this->getSite()->getId()}, period = {$this->getPeriod()->getLabel()} {$this->getPeriod()->getRangeString()}, segment = {$this->getSegment()->getString()}]";
    }

    public function canSkipThisArchive()
    {
        $idSite = $this->getSite()->getId();
        return $this->isWebsiteUsingTheTracker($idSite)
            && !$this->hasSiteVisitsBetweenTimeframe($idSite, $this->getPeriod()->getDateStart()->getDatetime(), $this->getPeriod()->getDateEnd()->getDatetime());
    }

    private function isWebsiteUsingTheTracker($idSite)
    {
        $idSitesNotUsingTracker = self::getSitesNotUsingTracker();

        $isUsingTracker = !in_array($idSite, $idSitesNotUsingTracker);

        return $isUsingTracker;
    }

    public static function getSitesNotUsingTracker()
    {
        $cache = Cache::getTransientCache();

        $cacheKey = 'Archiving.isWebsiteUsingTheTracker';
        $idSitesNotUsingTracker = $cache->fetch($cacheKey);
        if ($idSitesNotUsingTracker === false || !isset($idSitesNotUsingTracker)) {
            // we want to trigger event only once
            $idSitesNotUsingTracker = array();

            /**
             * This event is triggered when detecting whether there are sites that do not use the tracker.
             *
             * By default we only archive a site when there was actually any visit since the last archiving.
             * However, some plugins do import data from another source instead of using the tracker and therefore
             * will never have any visits for this site. To make sure we still archive data for such a site when
             * archiving for this site is requested, you can listen to this event and add the idSite to the list of
             * sites that do not use the tracker.
             *
             * @param bool $idSitesNotUsingTracker The list of idSites that rather import data instead of using the tracker
             */
            Piwik::postEvent('CronArchive.getIdSitesNotUsingTracker', array(&$idSitesNotUsingTracker));

            $cache->save($cacheKey, $idSitesNotUsingTracker);
        }
        return $idSitesNotUsingTracker;
    }

    private function hasSiteVisitsBetweenTimeframe($idSite, $date1, $date2)
    {
        $minVisitTimesPerSite = $this->getMinVisitTimesPerSite($idSite);
        if (empty($minVisitTimesPerSite)) {
            return false;
        }

        $date2 = Date::factory($date2)->addDay(1)->getStartOfDay();
        if ($date2->isEarlier($minVisitTimesPerSite)) {
            return false;
        }

        return $this->rawLogDao->hasSiteVisitsBetweenTimeframe(Date::factory($date1)->getDatetime(), $date2->getDatetime(), $idSite);
    }

    private function getMinVisitTimesPerSite($idSite)
    {
        $cache = Cache::getLazyCache();
        $cacheKey = 'Archiving.minVisitTime.' . $idSite;

        $value = $cache->fetch($cacheKey);
        if ($value === false) {
            $value = $this->rawLogDao->getMinimumVisitTimeForSite($idSite);
            $cache->save($cacheKey, $value, $ttl = 3600); // TODO: constant
        }

        if (!empty($value)) {
            $value = Date::factory($value);
        }

        return $value;
    }
}
