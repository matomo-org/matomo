<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\ArchiveProcessor;

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
     * @var string
     */
    private $archiveOnlyReport = null;

    /**
     * @var bool
     */
    private $isArchiveOnlyReportHandled;

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
    }

    /**
     * If we want to archive only a single report, we can request that via this method.
     * It is up to each plugin's archiver to respect the setting.
     *
     * @param string $archiveOnlyReport
     * @api
     */
    public function setArchiveOnlyReport($archiveOnlyReport)
    {
        $this->archiveOnlyReport = $archiveOnlyReport;
    }

    /**
     * Gets the report we want to archive specifically, or null if none was specified.
     *
     * @return string|null
     * @api
     */
    public function getArchiveOnlyReport()
    {
        return $this->archiveOnlyReport;
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
            "%s archive, idSite = %d (%s), segment '%s', plugin = '%s', report = '%s', UTC datetime [%s -> %s]",
            $this->getPeriod()->getLabel(),
            $this->getSite()->getId(),
            $temporary,
            $this->getSegment()->getString(),
            $this->getRequestedPlugin(),
            $this->getArchiveOnlyReport(),
            $this->getDateStart()->getDateStartUTC(),
            $this->getDateEnd()->getDateEndUTC()
        );
    }

    public function __toString()
    {
        return "[idSite = {$this->getSite()->getId()}, period = {$this->getPeriod()->getLabel()} {$this->getPeriod()->getRangeString()}, segment = {$this->getSegment()->getString()}, plugin = {$this->getRequestedPlugin()}, report = {$this->getArchiveOnlyReport()}]";
    }

    /**
     * Returns whether the setArchiveOnlyReport() was handled by an Archiver.
     *
     * @return bool
     */
    public function isPartialArchive()
    {
        if (!$this->getRequestedPlugin()) { // sanity check, partial archives are only for single reports
            return false;
        }

        return $this->isArchiveOnlyReportHandled;
    }

    /**
     * If a plugin's archiver handles the setArchiveOnlyReport() setting, it should call this method
     * so it is known that the archive only contains the requested report. This should be called
     * in an Archiver's __construct method.
     *
     * @param bool $isArchiveOnlyReportHandled
     */
    public function setIsPartialArchive($isArchiveOnlyReportHandled)
    {
        $this->isArchiveOnlyReportHandled = $isArchiveOnlyReportHandled;
    }
}
