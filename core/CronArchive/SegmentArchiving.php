<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CronArchive;

use Doctrine\Common\Cache\Cache;
use Matomo\Cache\Transient;
use Piwik\Access;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache as PiwikCache;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Period\Range;
use Piwik\Plugins\SegmentEditor\Model;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Log\LoggerInterface;

/**
 * Provides URLs that initiate archiving during cron archiving for segments.
 *
 * Handles the `[General] process_new_segments_from` INI option.
 */
class SegmentArchiving
{
    public const BEGINNING_OF_TIME = 'beginning_of_time';
    public const CREATION_TIME = 'segment_creation_time';
    public const LAST_EDIT_TIME = 'segment_last_edit_time';
    public const DEFAULT_BEGINNING_OF_TIME_LAST_N_YEARS = 7;

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

    public function __construct(
        $beginningOfTimeLastNInYears = self::DEFAULT_BEGINNING_OF_TIME_LAST_N_YEARS,
        ?Model $segmentEditorModel = null,
        ?Cache $segmentListCache = null,
        ?Date $now = null,
        ?LoggerInterface $logger = null
    ) {
        $this->processNewSegmentsFrom = StaticContainer::get('ini.General.process_new_segments_from');
        $this->beginningOfTimeLastNInYears = $beginningOfTimeLastNInYears;
        $this->segmentEditorModel = $segmentEditorModel ?: new Model();
        $this->segmentListCache = $segmentListCache ?: PiwikCache::getTransientCache();
        $this->now = $now ?: Date::factory('now');
        $this->logger = $logger ?: StaticContainer::get(LoggerInterface::class);
        $this->forceArchiveAllSegments = self::getShouldForceArchiveAllSegments();
    }

    public function findSegmentForHash($hash, $idSite)
    {
        foreach ($this->getAllSegments() as $segment) {
            if (
                !$this->isAutoArchivingEnabledFor($segment)
                || !self::isSegmentForSite($segment, $idSite)
            ) {
                continue;
            }

            if (!Segment::isAvailable($segment['definition'], [$idSite])) {
                $this->logger->debug("Could not process segment {$segment['definition']} for site {$idSite}. Segment should not exist for the site, but does.");
                continue;
            }

            $segmentObj = new Segment($segment['definition'], [$idSite]);

            if ($segmentObj->getHash() == $hash) {
                return $segment;
            }
        }
        return null;
    }

    public function getReArchiveSegmentStartDate($segmentInfo)
    {
        [$segmentCreatedTime, $segmentLastEditedTime] = $this->getCreatedTimeOfSegment($segmentInfo);

        if ($this->processNewSegmentsFrom == SegmentArchiving::CREATION_TIME) {
            if (empty($segmentCreatedTime)) {
                return null;
            }
            $this->logger->debug("process_new_segments_from set to segment_creation_time, oldest date to process is {time}", array('time' => $segmentCreatedTime));

            return $segmentCreatedTime;
        } elseif ($this->processNewSegmentsFrom == SegmentArchiving::LAST_EDIT_TIME) {
            if (empty($segmentLastEditedTime)) {
                return null;
            }
            $this->logger->debug(
                "process_new_segments_from set to segment_last_edit_time, segment last edit time is {time}",
                array('time' => $segmentLastEditedTime)
            );

            return $segmentLastEditedTime;
        } elseif (preg_match("/^editLast([0-9]+)$/", $this->processNewSegmentsFrom, $matches)) {
            if (empty($segmentLastEditedTime)) {
                return null;
            }
            $lastN = $matches[1];

            [$lastDate, $lastPeriod] = Range::getDateXPeriodsAgo($lastN, $segmentLastEditedTime, 'day');
            $result = Date::factory($lastDate);

            $this->logger->debug("process_new_segments_from set to editLast{N}, oldest date to process is {time}", array('N' => $lastN, 'time' => $result));

            return $result;
        } elseif (preg_match("/^last([0-9]+)$/", $this->processNewSegmentsFrom, $matches)) {
            if (empty($segmentCreatedTime)) {
                return null;
            }
            $lastN = $matches[1];

            [$lastDate, $lastPeriod] = Range::getDateXPeriodsAgo($lastN, $segmentCreatedTime, 'day');
            $result = Date::factory($lastDate);

            $this->logger->debug("process_new_segments_from set to last{N}, oldest date to process is {time}", array('N' => $lastN, 'time' => $result));

            return $result;
        } else {
            $this->logger->debug("process_new_segments_from set to beginning_of_time or cannot recognize value");

            $result = Date::factory('today')->subYear($this->beginningOfTimeLastNInYears);

            $idSite = $segmentInfo['enable_only_idsite'] ?? null;
            if (!empty($idSite)) {
                $siteCreationDate = Date::factory(Site::getCreationDateFor($idSite));

                if ($result->isEarlier($siteCreationDate)) {
                    $result = $siteCreationDate;
                }
            }

            return $result;
        }
    }

    /**
     * Retrieve the created and last edited time as date objects from the supplied segment array
     *
     * @param array $storedSegment
     *
     * @return array<Date|null>
     */
    private function getCreatedTimeOfSegment(array $storedSegment): array
    {
        // check for an earlier ts_created timestamp
        $createdTime = empty($storedSegment['ts_created']) ? null : Date::factory($storedSegment['ts_created']);

        // if there is no ts_last_edit timestamp, initialize it to ts_created
        if (empty($storedSegment['ts_last_edit'])) {
            $storedSegment['ts_last_edit'] = empty($storedSegment['ts_created']) ? null : $storedSegment['ts_created'];
        }

        // check for a later ts_last_edit timestamp
        $lastEditTime = empty($storedSegment['ts_last_edit']) || $storedSegment['ts_last_edit'] === '0000-00-00 00:00:00'
            ? null : Date::factory($storedSegment['ts_last_edit']);

        return [$createdTime, $lastEditTime];
    }

    public function getAllSegments()
    {
        $cacheKey = 'SegmentArchiving_AllSegments';

        if (!$this->segmentListCache->contains($cacheKey)) {
            $segments = $this->segmentEditorModel->getAllSegmentsAndIgnoreVisibility();

            $this->segmentListCache->save($cacheKey, $segments);
        }

        return $this->segmentListCache->fetch($cacheKey);
    }

    public function getAllSegmentsToArchive($idSite)
    {
        return Rules::getSegmentsToProcess([$idSite]);
    }

    public static function isSegmentForSite($segment, $idSite)
    {
        return $segment['enable_only_idsite'] == 0
            || $segment['enable_only_idsite'] == $idSite;
    }

    public function isAutoArchivingEnabledFor($storedSegment)
    {
        return $this->forceArchiveAllSegments || !empty($storedSegment['auto_archive']);
    }

    public static function getShouldForceArchiveAllSegments()
    {
        return !Rules::isBrowserTriggerEnabled() && !Rules::isBrowserArchivingAvailableForSegments();
    }

    public function reArchiveSegment($segmentInfo)
    {
        if (empty($segmentInfo['definition'])) { // sanity check
            return;
        }

        $definition = $segmentInfo['definition'];
        $idSite = !empty($segmentInfo['enable_only_idsite']) ? $segmentInfo['enable_only_idsite'] : 'all';

        /*
         * Done as super user, to ensure we are able to receive all site ids, even if the current user
         * does not have access to all of them.
         */
        $idSites = Access::doAsSuperUser(function () use ($idSite) {
            return Site::getIdSitesFromIdSitesString($idSite);
        });
        $startDate = $this->getReArchiveSegmentStartDate($segmentInfo);

        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $invalidator->scheduleReArchiving($idSites, null, null, $startDate, new Segment($definition, $idSites));
    }
}
