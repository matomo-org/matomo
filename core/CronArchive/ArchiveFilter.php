<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CronArchive;

use Piwik\Archive;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Period\Factory;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugins\SegmentEditor\Model as SegmentEditorModel;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Log\LoggerInterface;

class ArchiveFilter
{
    private $segmentsToForce = null;

    private $disableSegmentsArchiving = false;

    /**
     * If supplied, archiving will be launched only for periods that fall within this date range. For example,
     * `"2012-01-01,2012-03-15"` would result in January 2012, February 2012 being archived but not April 2012.
     *
     * @var Date[]
     */
    private $restrictToDateRange = false;

    /**
     * A list of periods to launch archiving for. By default, day, week, month and year periods
     * are considered. This variable can limit the periods to, for example, week & month only.
     *
     * @var string[] eg, `array("day","week","month","year")`
     */
    private $restrictToPeriods = array();

    /**
     * @var string[]
     */
    private $periodIdsToLabels;

    /**
     * If enabled, segments will be only archived for yesterday, but not today. If the segment was created recently,
     * then it will still be archived for today and the setting will be ignored for this segment.
     * @var bool
     */
    private $skipSegmentsForToday = false;

    /**
     * If enabled, the only invalidations that will be processed are for the specific plugin and report specified
     * here. Must be in the format "MyPlugin.myReport".
     * @var string|null
     */
    private $forceReport = null;

    public function __construct()
    {
        $this->setRestrictToPeriods('');
        $this->periodIdsToLabels = array_flip(Piwik::$idPeriods);
    }

    /**
     * @param array $archive
     * @return string|false
     */
    public function filterArchive($archive)
    {
        $segment = isset($archive['segment']) ? $archive['segment'] : '';
        if (
            $this->disableSegmentsArchiving
            && !empty($segment)
        ) {
            return 'segment archiving disabled';
        }

        if (!empty($this->segmentsToForce)) {
            if (!empty($this->segmentsToForce) && !in_array($segment, $this->segmentsToForce)) {
                return "segment '$segment' is not in --force-idsegments";
            }
        }

        if (!empty($this->skipSegmentsForToday)) {
            $site = new Site($archive['idsite']);
            if ((int) $archive['period'] === Range::PERIOD_ID) {
                $period = Factory::build($this->periodIdsToLabels[$archive['period']], "{$archive['date1']},{$archive['date2']}");
            } else {
                $period = Factory::build($this->periodIdsToLabels[$archive['period']], $archive['date1']);
            }
            $segment = new Segment($segment, [$archive['idsite']]);
            if (Archive::shouldSkipArchiveIfSkippingSegmentArchiveForToday($site, $period, $segment)) {
                return "skipping segment archives for today";
            }
        }

        if (
            !empty($this->restrictToDateRange)
            && ($this->restrictToDateRange[0]->isLater(Date::factory($archive['date2']))
                || $this->restrictToDateRange[1]->isEarlier(Date::factory($archive['date1']))
            )
        ) {
            return "archive date range ({$archive['date1']},{$archive['date2']}) is not within --force-date-range";
        }

        $periodLabel = $this->periodIdsToLabels[$archive['period']];
        if (
            !empty($this->restrictToPeriods)
            && !in_array($periodLabel, $this->restrictToPeriods)
        ) {
            return "period is not specified in --force-periods";
        }

        if (
            !empty($this->forceReport)
            && (empty($archive['plugin'])
                || empty($archive['report'])
                || $archive['plugin'] . '.' . $archive['report'] != $this->forceReport)
        ) {
            return "report is not the same as value specified in --force-report";
        }

        return false;
    }

    public function logFilterInfo(LoggerInterface $logger)
    {
        $this->logForcedSegmentInfo($logger);
        $this->logForcedPeriodInfo($logger);
        $this->logSkipSegmentInfo($logger);
    }

    private function logForcedSegmentInfo(LoggerInterface $logger)
    {
        if (empty($this->segmentsToForce)) {
            return;
        }

        $logger->info("- Limiting segment archiving to following segments:");
        foreach ($this->segmentsToForce as $segmentDefinition) {
            $logger->info("  * " . $segmentDefinition);
        }
    }

    private function logForcedPeriodInfo(LoggerInterface $logger)
    {
        if (!empty($this->restrictToPeriods)) {
            $logger->info("- Will only process the following periods: " . implode(", ", $this->restrictToPeriods) . " (--force-periods)");
        }
    }

    /**
     * @return null
     */
    public function getSegmentsToForce()
    {
        return $this->segmentsToForce;
    }

    /**
     * @param int[] $idSegments
     */
    public function setSegmentsToForceFromSegmentIds($idSegments)
    {
        /** @var SegmentEditorModel $segmentEditorModel */
        $segmentEditorModel = StaticContainer::get('Piwik\Plugins\SegmentEditor\Model');
        $segments = $segmentEditorModel->getAllSegmentsAndIgnoreVisibility();

        $segments = array_filter($segments, function ($segment) use ($idSegments) {
            return in_array($segment['idsegment'], $idSegments);
        });

        $segments = array_map(function ($segment) {
            return $segment['definition'];
        }, $segments);

        $this->segmentsToForce = $segments;
    }

    /**
     * @return bool
     */
    public function isDisableSegmentsArchiving()
    {
        return $this->disableSegmentsArchiving;
    }

    /**
     * @param bool $disableSegmentsArchiving
     */
    public function setDisableSegmentsArchiving(bool $disableSegmentsArchiving)
    {
        $this->disableSegmentsArchiving = $disableSegmentsArchiving;
    }

    /**
     * @return false|string
     */
    public function getRestrictToDateRange()
    {
        return $this->restrictToDateRange;
    }

    /**
     * @param false|string $restrictToDateRange
     */
    public function setRestrictToDateRange($restrictToDateRange)
    {
        if (empty($restrictToDateRange)) {
            $this->restrictToDateRange = $restrictToDateRange;
            return;
        }

        try {
            $parts = explode(',', $restrictToDateRange);
            $parts = [
                Date::factory($parts[0]),
                Date::factory($parts[1]),
            ];
        } catch (\Exception $ex) {
            throw new \Exception('Invalid restrict to date range argument: ' . $restrictToDateRange);
        }

        $this->restrictToDateRange = $parts;
    }

    public function setSegmentsToForce(array $segments)
    {
        $this->segmentsToForce = $segments;
    }

    public function setSkipSegmentsForToday($skipSegmentsForToday)
    {
        $this->skipSegmentsForToday = $skipSegmentsForToday;
    }

    /**
     * @return bool
     */
    public function isSkipSegmentsForToday(): bool
    {
        return $this->skipSegmentsForToday;
    }

    public function setForceReport($forceReport)
    {
        $this->forceReport = $forceReport;
    }

    /**
     * @return array
     */
    private function getPeriodsToProcess()
    {
        return $this->restrictToPeriods;
    }

    /**
     * @return array
     */
    private function getDefaultPeriodsToProcess()
    {
        return array('day', 'week', 'month', 'year', 'range');
    }

    /**
     * @return string[]
     */
    public function getRestrictToPeriods()
    {
        return $this->restrictToPeriods;
    }

    /**
     * @param string|string[] $restrictToPeriods
     */
    public function setRestrictToPeriods($restrictToPeriods)
    {
        if (is_string($restrictToPeriods)) {
            $restrictToPeriods = explode(',', $restrictToPeriods);
            $restrictToPeriods = array_map('trim', $restrictToPeriods);
        }

        $this->restrictToPeriods = $restrictToPeriods ?: [];
        $this->restrictToPeriods = array_intersect($this->restrictToPeriods, $this->getDefaultPeriodsToProcess());
        $this->restrictToPeriods = array_intersect($this->restrictToPeriods, PeriodFactory::getPeriodsEnabledForAPI());
    }

    private function logSkipSegmentInfo(LoggerInterface $logger)
    {
        if ($this->skipSegmentsForToday) {
            $logger->info('Will skip segments archiving for today unless they were created recently');
        }
    }
}
