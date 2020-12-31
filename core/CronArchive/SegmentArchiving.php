<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive;

use Doctrine\Common\Cache\Cache;
use Matomo\Cache\Transient;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period\Range;
use Piwik\Plugins\SegmentEditor\Model;
use Piwik\Segment;
use Piwik\Site;
use Psr\Log\LoggerInterface;

/**
 * Provides URLs that initiate archiving during cron archiving for segments.
 *
 * Handles the `[General] process_new_segments_from` INI option.
 */
class SegmentArchiving
{
    const BEGINNING_OF_TIME = 'beginning_of_time';
    const CREATION_TIME = 'segment_creation_time';
    const LAST_EDIT_TIME = 'segment_last_edit_time';
    const DEFAULT_BEGINNING_OF_TIME_LAST_N_YEARS = 7;

    /**
     * @var Model
     */
    private $segmentEditorModel;

    /**
     * @var Transient
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

    /**
     * @var int
     */
    private $beginningOfTimeLastNInYears;

    /**
     * @var bool
     */
    private $forceArchiveAllSegments;

    public function __construct($processNewSegmentsFrom, $beginningOfTimeLastNInYears = self::DEFAULT_BEGINNING_OF_TIME_LAST_N_YEARS,
                                Model $segmentEditorModel = null, Cache $segmentListCache = null, Date $now = null,
                                LoggerInterface $logger = null)
    {
        $this->processNewSegmentsFrom = $processNewSegmentsFrom;
        $this->beginningOfTimeLastNInYears = $beginningOfTimeLastNInYears;
        $this->segmentEditorModel = $segmentEditorModel ?: new Model();
        $this->segmentListCache = $segmentListCache ?: new Transient();
        $this->now = $now ?: Date::factory('now');
        $this->logger = $logger ?: StaticContainer::get('Psr\Log\LoggerInterface');
        $this->forceArchiveAllSegments = $this->getShouldForceArchiveAllSegments();
    }

    public function getSegmentArchivesToInvalidateForNewSegments($idSite)
    {
        return $this->getSegmentArchivesToInvalidate($idSite, true);
    }

    public function getSegmentArchivesToInvalidate($idSite, $checkOnlyForNewSegments = false)
    {
        $result = [];

        $segmentsForSite = $this->getAllSegments();
        foreach ($segmentsForSite as $storedSegment) {
            if (!$this->isAutoArchivingEnabledFor($storedSegment)
                || !$this->isSegmentForSite($storedSegment, $idSite)
            ) {
                continue;
            }

            $oldestDateToProcessForNewSegment = $this->getOldestDateToProcessForNewSegment($idSite, $storedSegment, $checkOnlyForNewSegments);
            if (empty($oldestDateToProcessForNewSegment)) {
                continue;
            }

            $found = false;
            foreach ($result as $segment) {
                if ($segment['segment'] == $storedSegment['definition']) {
                    $segment['date'] = $segment['date']->isEarlier($oldestDateToProcessForNewSegment) ? $segment['date'] : $oldestDateToProcessForNewSegment;

                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $result[] = [
                    'date' => $oldestDateToProcessForNewSegment,
                    'segment' => $storedSegment['definition'],
                ];
            }
        }
        return $result;
    }

    public function findSegmentForHash($hash, $idSite)
    {
        foreach ($this->getAllSegments() as $segment) {
            if (!$this->isAutoArchivingEnabledFor($segment)
                || !$this->isSegmentForSite($segment, $idSite)
            ) {
                continue;
            }

            try {
                $segmentObj = new Segment($segment['definition'], [$idSite]);
            } catch (\Exception $ex) {
                $this->logger->debug("Could not process segment {$segment['definition']} for site {$idSite}. Segment should not exist for the site, but does.");
                continue;
            }

            if ($segmentObj->getHash() == $hash) {
                return $segment;
            }
        }
        return null;
    }

    private function getOldestDateToProcessForNewSegment($idSite, $storedSegment, $checkOnlyForNewSegments)
    {
        /**
         * @var Date $segmentCreatedTime
         * @var Date $segmentLastEditedTime
         */
        list($segmentCreatedTime, $segmentLastEditedTime) = $this->getCreatedTimeOfSegment($idSite, $storedSegment);
        if (empty($segmentCreatedTime)) {
            return null;
        }

        $lastInvalidationTime = CronArchive::getLastInvalidationTime();
        if (!empty($lastInvalidationTime)) {
            $lastInvalidationTime = Date::factory((int) $lastInvalidationTime);
        }

        $segmentTimeToUse = $segmentLastEditedTime ?: $segmentCreatedTime;
        if ($checkOnlyForNewSegments) {
            if (!empty($lastInvalidationTime)
                && !empty($segmentTimeToUse)
                && $segmentTimeToUse->isEarlier($lastInvalidationTime)
            ) {
                return null; // has already have been invalidated, ignore
            }
        }

        if ($this->processNewSegmentsFrom == self::CREATION_TIME) {
            $this->logger->debug("process_new_segments_from set to segment_creation_time, oldest date to process is {time}", array('time' => $segmentCreatedTime));

            return $segmentCreatedTime;
        } elseif ($this->processNewSegmentsFrom == self::LAST_EDIT_TIME) {
            $this->logger->debug("process_new_segments_from set to segment_last_edit_time, segment last edit time is {time}",
                array('time' => $segmentLastEditedTime));

            if ($segmentLastEditedTime === null
                || $segmentLastEditedTime->getTimestamp() < $segmentCreatedTime->getTimestamp()
            ) {
                $this->logger->debug("segment last edit time is older than created time, using created time instead");

                $segmentLastEditedTime = $segmentCreatedTime;
            }

            return $segmentLastEditedTime;
        } elseif (preg_match("/^last([0-9]+)$/", $this->processNewSegmentsFrom, $matches)) {
            $lastN = $matches[1];

            list($lastDate, $lastPeriod) = Range::getDateXPeriodsAgo($lastN, $segmentCreatedTime, 'day');
            $result = Date::factory($lastDate);

            $this->logger->debug("process_new_segments_from set to last{N}, oldest date to process is {time}", array('N' => $lastN, 'time' => $result));

            return $result;
        } else {
            $this->logger->debug("process_new_segments_from set to beginning_of_time or cannot recognize value");

            $siteCreationDate = Date::factory(Site::getCreationDateFor($idSite));

            $result = Date::factory('today')->subYear($this->beginningOfTimeLastNInYears);
            if ($result->isEarlier($siteCreationDate)) {
                $result = $siteCreationDate;
            }

            $earliestVisitTime = $this->getEarliestVisitTimeFor($idSite);
            if (!empty($earliestVisitTime)
                && $result->isEarlier($earliestVisitTime)
            ) {
                $result = $earliestVisitTime;
            }

            return $result;
        }
    }

    private function getEarliestVisitTimeFor($idSite)
    {
        $earliestIdVisit = Db::fetchOne('SELECT idvisit FROM ' . Common::prefixTable('log_visit')
            . ' WHERE idsite = ? ORDER BY visit_last_action_time ASC LIMIT 1', [$idSite]);

        $earliestStartTime = Db::fetchOne('SELECT visit_first_action_time FROM ' . Common::prefixTable('log_visit') . ' WHERE idvisit = ?', [
            $earliestIdVisit,
        ]);

        if (empty($earliestStartTime)) {
            return null;
        }

        return Date::factory($earliestStartTime);
    }

    private function getCreatedTimeOfSegment($idSite, $storedSegment)
    {
        /** @var Date $latestEditTime */
        $latestEditTime = null;
        $earliestCreatedTime = $this->now;
        if (empty($storedSegment['ts_created'])
            || empty($storedSegment['definition'])
            || !isset($storedSegment['enable_only_idsite'])
            || !$this->isSegmentForSite($storedSegment, $idSite)
        ) {
            return [null, null];
        }

        // check for an earlier ts_created timestamp
        $createdTime = Date::factory($storedSegment['ts_created']);
        if ($createdTime->getTimestamp() < $earliestCreatedTime->getTimestamp()) {
            $earliestCreatedTime = $createdTime;
        }

        // if there is no ts_last_edit timestamp, initialize it to ts_created
        if (empty($storedSegment['ts_last_edit'])) {
            $storedSegment['ts_last_edit'] = $storedSegment['ts_created'];
        }

        // check for a later ts_last_edit timestamp
        $lastEditTime = Date::factory($storedSegment['ts_last_edit']);
        if ($latestEditTime === null
            || $latestEditTime->getTimestamp() < $lastEditTime->getTimestamp()
        ) {
            $latestEditTime = $lastEditTime;
        }

        $this->logger->debug(
            "Earliest created time of segment '{segment}' w/ idSite = {idSite} is found to be {createdTime}. Latest " .
            "edit time is found to be {latestEditTime}.",
            array(
                'segment' => $storedSegment['definition'],
                'idSite' => $idSite,
                'createdTime' => $earliestCreatedTime,
                'latestEditTime' => $latestEditTime,
            )
        );

        return array($earliestCreatedTime, $latestEditTime);
    }

    public function getAllSegments()
    {
        if (!$this->segmentListCache->contains('all')) {
            $segments = $this->segmentEditorModel->getAllSegmentsAndIgnoreVisibility();

            $this->segmentListCache->save('all', $segments);
        }

        return $this->segmentListCache->fetch('all');
    }

    public function getAllSegmentsToArchive($idSite)
    {
        $segments = [];
        foreach ($this->getAllSegments() as $segment) {
            if (!$this->isAutoArchivingEnabledFor($segment)
                || !$this->isSegmentForSite($segment, $idSite)
            ) {
                continue;
            }

            $segments[] = $segment;
        }
        return $segments;
    }

    private function isSegmentForSite($segment, $idSite)
    {
        return $segment['enable_only_idsite'] == 0
            || $segment['enable_only_idsite'] == $idSite;
    }

    public function isAutoArchivingEnabledFor($storedSegment)
    {
        return $this->forceArchiveAllSegments || !empty($storedSegment['auto_archive']);
    }

    private function getShouldForceArchiveAllSegments()
    {
        return !Rules::isBrowserTriggerEnabled() && !Rules::isBrowserArchivingAvailableForSegments();
    }
}
