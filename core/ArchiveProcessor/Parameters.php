<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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

    /**
     * Constructor.
     *
     * @ignore
     */
    public function __construct(Site $site, Period $period, Segment $segment, $skipAggregationOfSubTables = false)
    {
        $this->site = $site;
        $this->period = $period;
        $this->segment = $segment;
        $this->skipAggregationOfSubTables = $skipAggregationOfSubTables;
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
        if($this->getPeriod()->getLabel() == 'day') {
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
     * @return bool
     */
    public function isSingleSiteDayArchive()
    {
        $oneSite = $this->isSingleSite();
        $oneDay = $this->getPeriod()->getLabel() == 'day';
        return $oneDay && $oneSite;
    }

    public function isSingleSite()
    {
        return count($this->getIdSites()) == 1;
    }

    public function isSkipAggregationOfSubTables()
    {
        return $this->skipAggregationOfSubTables;
    }

    public function logStatusDebug($isTemporary)
    {
        $temporary = 'definitive archive';
        if ($isTemporary) {
            $temporary = 'temporary archive';
        }
        Log::verbose(
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
}
