<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive;

use Piwik\Cache\Cache;
use Piwik\Cache\Transient;
use Piwik\Date;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Period\Range;
use Piwik\Plugins\SegmentEditor\Model;

/**
 * Provides URLs that initiate archiving during cron archiving for segments.
 *
 * Handles the `[General] process_new_segments_from` INI option.
 */
class SegmentArchivingRequestUrlProvider
{
    const BEGINNING_OF_TIME = 'beginning_of_time';
    const CREATION_TIME = 'creation_time';

    /**
     * @var Model
     */
    private $segmentEditorModel;

    /**
     * @var Cache
     */
    private $segmentListCache;

    /**
     * @var Date
     */
    private $now;

    private $processNewSegmentsFrom;

    public function __construct($processNewSegmentsFrom, Model $segmentEditorModel = null, Cache $segmentListCache = null, Date $now = null)
    {
        $this->processNewSegmentsFrom = $processNewSegmentsFrom;
        $this->segmentEditorModel = $segmentEditorModel ?: new Model();
        $this->segmentListCache = $segmentListCache ?: new Transient();
        $this->now = $now ?: Date::factory('now');
    }

    public function getUrlParameterDateString($idSite, $period, $date, $segment)
    {
        $segmentCreatedTime = $this->getCreatedTimeOfSegment($idSite, $segment);
        if (empty($segmentCreatedTime)) {
            return $date;
        }

        $oldestDateToProcessForNewSegment = $this->getOldestDateToProcessForNewSegment($segmentCreatedTime);
        if (empty($oldestDateToProcessForNewSegment)) {
            return $date;
        }

        // if the start date for the archiving request is before the minimum date allowed for processing this segment,
        // use the minimum allowed date as the start date
        $periodObj = PeriodFactory::build($period, $date);
        if ($periodObj->getDateStart()->getTimestamp() < $oldestDateToProcessForNewSegment->getTimestamp()) {
            $date = $oldestDateToProcessForNewSegment->toString().','.$periodObj->getDateEnd();
        }

        return $date;
    }

    private function getOldestDateToProcessForNewSegment(Date $segmentCreatedTime)
    {
        if ($this->processNewSegmentsFrom == self::CREATION_TIME) {
            return $segmentCreatedTime;
        } else if (preg_match("/^last([0-9]+)$/", $this->processNewSegmentsFrom, $matches)) {
            $lastN = $matches[1];

            list($lastDate, $lastPeriod) = Range::getDateXPeriodsAgo($lastN, $segmentCreatedTime, 'day');

            return Date::factory($lastDate);
        } else {
            return null;
        }
    }

    private function getCreatedTimeOfSegment($idSite, $segmentDefinition)
    {
        $segments = $this->getAllSegments();

        $earliestCreatedTime = $this->now;
        foreach ($segments as $segment) {
            if (empty($segment['ts_created'])
                || empty($segment['definition'])
                || !isset($segment['enable_only_idsite'])
            ) {
                continue;
            }

            if ($this->isSegmentForSite($segment, $idSite)
                && $segment['definition'] == $segmentDefinition
            ) {
                $createdTime = Date::factory($segment['ts_created']);
                if ($createdTime->getTimestamp() < $earliestCreatedTime->getTimestamp()) {
                    $earliestCreatedTime = $createdTime;
                }
            }
        }
        return $earliestCreatedTime;
    }

    private function getAllSegments()
    {
        if (!$this->segmentListCache->contains('all')) {
            $segments = $this->segmentEditorModel->getAllSegmentsAndIgnoreVisibility();

            $this->segmentListCache->save('all', $segments);
        }

        return $this->segmentListCache->fetch('all');
    }

    private function isSegmentForSite($segment, $idSite)
    {
        return $segment['enable_only_idsite'] == 0
            || $segment['enable_only_idsite'] == $idSite;
    }
}