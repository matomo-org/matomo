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
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Period\Range;
use Piwik\Plugins\SegmentEditor\Model;
use Psr\Log\LoggerInterface;

/**
 * Provides URLs that initiate archiving during cron archiving for segments.
 *
 * Handles the `[General] process_new_segments_from` INI option.
 */
class SegmentArchivingRequestUrlProvider
{
    const BEGINNING_OF_TIME = 'beginning_of_time';
    const CREATION_TIME = 'segment_creation_time';

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($processNewSegmentsFrom, Model $segmentEditorModel = null, Cache $segmentListCache = null,
                                Date $now = null, LoggerInterface $logger = null)
    {
        $this->processNewSegmentsFrom = $processNewSegmentsFrom;
        $this->segmentEditorModel = $segmentEditorModel ?: new Model();
        $this->segmentListCache = $segmentListCache ?: new Transient();
        $this->now = $now ?: Date::factory('now');
        $this->logger = $logger ?: StaticContainer::get('Psr\Log\LoggerInterface');
    }

    public function getUrlParameterDateString($idSite, $period, $date, $segment)
    {
        $segmentCreatedTime = $this->getCreatedTimeOfSegment($idSite, $segment);

        $oldestDateToProcessForNewSegment = $this->getOldestDateToProcessForNewSegment($segmentCreatedTime);
        if (empty($oldestDateToProcessForNewSegment)) {
            return $date;
        }

        // if the start date for the archiving request is before the minimum date allowed for processing this segment,
        // use the minimum allowed date as the start date
        $periodObj = PeriodFactory::build($period, $date);
        if ($periodObj->getDateStart()->getTimestamp() < $oldestDateToProcessForNewSegment->getTimestamp()) {
            $this->logger->debug("Start date of archiving request period ({start}) is older than configured oldest date to process for the segment.", array(
                'start' => $periodObj->getDateStart()
            ));

            $endDate = $periodObj->getDateEnd();

            // if the creation time of a segment is older than the end date of the archiving request range, we cannot
            // blindly rewrite the date string, since the resulting range would be incorrect. instead we make the
            // start date equal to the end date, so less archiving occurs, and no fatal error occurs.
            if ($oldestDateToProcessForNewSegment->getTimestamp() > $endDate->getTimestamp()) {
                $this->logger->debug("Oldest date to process is greater than end date of archiving request period ({end}), so setting oldest date to end date.", array(
                    'end' => $endDate
                ));

                $oldestDateToProcessForNewSegment = $endDate;
            }

            $date = $oldestDateToProcessForNewSegment->toString().','.$endDate;

            $this->logger->debug("Archiving request date range changed to {date} w/ period {period}.", array('date' => $date, 'period' => $period));
        }

        return $date;
    }

    private function getOldestDateToProcessForNewSegment(Date $segmentCreatedTime)
    {
        if ($this->processNewSegmentsFrom == self::CREATION_TIME) {
            $this->logger->debug("process_new_segments_from set to segment_creation_time, oldest date to process is {time}", array('time' => $segmentCreatedTime));

            return $segmentCreatedTime;
        } elseif (preg_match("/^last([0-9]+)$/", $this->processNewSegmentsFrom, $matches)) {
            $lastN = $matches[1];

            list($lastDate, $lastPeriod) = Range::getDateXPeriodsAgo($lastN, $segmentCreatedTime, 'day');
            $result = Date::factory($lastDate);

            $this->logger->debug("process_new_segments_from set to last{N}, oldest date to process is {time}", array('N' => $lastN, 'time' => $result));

            return $result;
        } else {
            $this->logger->debug("process_new_segments_from set to beginning_of_time or cannot recognize value");

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

        $this->logger->debug("Earliest created time of segment '{segment}' w/ idSite = {idSite} is found to be {time}.", array(
            'segment' => $segmentDefinition,
            'idSite' => $idSite,
            'time' => $earliestCreatedTime
        ));

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
