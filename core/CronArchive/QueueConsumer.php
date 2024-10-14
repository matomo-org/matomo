<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CronArchive;

use Piwik\ArchiveProcessor\Loader;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Rules;
use Piwik\CliMulti\RequestParser;
use Piwik\CronArchive;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Period;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Timer;
use Piwik\Log\LoggerInterface;

class QueueConsumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FixedSiteIds|SharedSiteIds
     */
    private $websiteIdArchiveList;

    /**
     * @var int
     */
    private $countOfProcesses;

    /**
     * @var int
     */
    private $pid;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var ArchiveFilter
     */
    private $archiveFilter;

    /**
     * @var SegmentArchiving
     */
    private $segmentArchiving;

    /**
     * @var CronArchive
     */
    private $cronArchive;

    /**
     * @var array
     */
    private $invalidationsToExclude;

    /**
     * @var string[]
     */
    private $periodIdsToLabels;

    /**
     * @var RequestParser
     */
    private $cliMultiRequestParser;

    /**
     * @var int
     */
    private $idSite;

    /**
     * @var int
     */
    private $siteRequests;

    /**
     * @var Timer
     */
    private $siteTimer;

    /**
     * @var string
     */
    private $currentSiteArchivingStartTime;

    /**
     * @var int|null
     */
    private $maxSitesToProcess = null;

    private $processedSiteCount = 0;

    public function __construct(
        LoggerInterface $logger,
        $websiteIdArchiveList,
        $countOfProcesses,
        $pid,
        Model $model,
        SegmentArchiving $segmentArchiving,
        CronArchive $cronArchive,
        RequestParser $cliMultiRequestParser,
        ?ArchiveFilter $archiveFilter = null
    ) {
        $this->logger = $logger;
        $this->websiteIdArchiveList = $websiteIdArchiveList;
        $this->countOfProcesses = $countOfProcesses;
        $this->pid = $pid;
        $this->model = $model;
        $this->segmentArchiving = $segmentArchiving;
        $this->cronArchive = $cronArchive;
        $this->cliMultiRequestParser = $cliMultiRequestParser;
        $this->archiveFilter = $archiveFilter;

        // if we skip or can't process an idarchive, we want to ignore it the next time we look for an invalidated
        // archive. these IDs are stored here (using a list like this serves to keep our SQL simple).
        $this->invalidationsToExclude = [];

        $this->periodIdsToLabels = array_flip(Piwik::$idPeriods);
    }

    /**
     * Get next archives to process.
     *
     * Returns either an array of archives to process for the current site (may be
     * empty if there are no more archives to process for it) or null when there are
     * no more sites to process.
     *
     * @return null|array
     */
    public function getNextArchivesToProcess()
    {
        if (empty($this->idSite)) {
            if ($this->maxSitesToProcess && $this->processedSiteCount >= $this->maxSitesToProcess) {
                $this->logger->info("Maximum number of sites to process per execution has been reached.");
                return null;
            }
            $this->idSite = $this->getNextIdSiteToArchive();
            if (empty($this->idSite)) { // no sites left to archive, stop
                $this->logger->debug("No more sites left to archive, stopping.");
                return null;
            }

            ++$this->processedSiteCount;

            /**
             * This event is triggered before the cron archiving process starts archiving data for a single
             * site.
             *
             * Note: multiple archiving processes can post this event.
             *
             * @param int $idSite The ID of the site we're archiving data for.
             * @param string $pid The PID of the process processing archives for this site.
             */
            Piwik::postEvent('CronArchive.archiveSingleSite.start', array($this->idSite, $this->pid));

            $this->logger->info("Start processing archives for site {idSite}.", ['idSite' => $this->idSite]);

            $this->siteTimer = new Timer();
            $this->siteRequests = 0;

            // check if we need to process invalidations
            // NOTE: we do this on every site iteration so we don't end up processing say a single user entered invalidation,
            // and then stop until the next hour.
            $this->cronArchive->invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain($this->idSite);

            $this->currentSiteArchivingStartTime = Date::now()->getDatetime();
        }

        // we don't want to invalidate different periods together or segment archives w/ no-segment archives
        // together, but it's possible to end up querying these archives. if we find one, we keep track of it
        // in this array to exclude, but after we run the current batch, we reset the array so we'll still
        // process them eventually.
        $invalidationsToExcludeInBatch = [];

        $siteCreationTime = Date::factory(Site::getCreationDateFor($this->idSite));

        // get archives to process simultaneously
        $archivesToProcess = [];
        while (count($archivesToProcess) < $this->countOfProcesses) {
            $invalidatedArchive = $this->getNextInvalidatedArchive($this->idSite, array_keys($invalidationsToExcludeInBatch));
            if (empty($invalidatedArchive)) {
                $this->logger->debug("No next invalidated archive.");
                break;
            }

            $invalidationDesc = $this->getInvalidationDescription($invalidatedArchive);

            if ($invalidatedArchive['periodObj']->getDateEnd()->isEarlier($siteCreationTime)) {
                $this->logger->debug("Invalidation is for period that is older than the site's creation time, ignoring: $invalidationDesc");
                $this->model->deleteInvalidations([$invalidatedArchive]);
                continue;
            }

            if (
                !empty($invalidatedArchive['plugin'])
                && !Manager::getInstance()->isPluginActivated($invalidatedArchive['plugin'])
            ) {
                $this->logger->debug("Plugin specific archive {$invalidatedArchive['idarchive']}'s plugin is deactivated, ignoring $invalidationDesc.");
                $this->model->deleteInvalidations([$invalidatedArchive]);
                continue;
            }

            if ($invalidatedArchive['segment'] === null) {
                $this->logger->debug("Found archive for segment that is not auto archived, ignoring: $invalidationDesc");
                $this->addInvalidationToExclude($invalidatedArchive);
                continue;
            }

            if ($this->archiveArrayContainsArchive($archivesToProcess, $invalidatedArchive)) {
                $this->logger->debug("Found duplicate invalidated archive {$invalidatedArchive['idarchive']}, ignoring: $invalidationDesc");
                $this->addInvalidationToExclude($invalidatedArchive);
                $this->model->deleteInvalidations([$invalidatedArchive]);
                continue;
            }

            if ($this->model->isSimilarArchiveInProgress($invalidatedArchive)) {
                $this->logger->debug("Found duplicate invalidated archive (same archive currently in progress), ignoring: $invalidationDesc");
                $this->addInvalidationToExclude($invalidatedArchive);
                $this->model->deleteInvalidations([$invalidatedArchive]);
                continue;
            }

            if (self::hasIntersectingPeriod($archivesToProcess, $invalidatedArchive)) {
                $this->logger->debug("Found archive with intersecting period with others in concurrent batch, skipping until next batch: $invalidationDesc");

                $idinvalidation = $invalidatedArchive['idinvalidation'];
                $invalidationsToExcludeInBatch[$idinvalidation] = true;
                continue;
            }

            $reason = $this->shouldSkipArchive($invalidatedArchive);
            if ($reason) {
                $this->logger->debug("Skipping invalidated archive {$invalidatedArchive['idinvalidation']}, $reason: $invalidationDesc");
                $this->addInvalidationToExclude($invalidatedArchive);
                continue;
            }

            list($isUsableExists, $archivedTime) = $this->usableArchiveExists($invalidatedArchive);
            if ($isUsableExists) {
                $now = Date::now()->getDatetime();
                $this->addInvalidationToExclude($invalidatedArchive);
                if (empty($invalidatedArchive['plugin'])) {
                    $this->logger->debug("Found invalidation with usable archive (not yet outdated, ts_archived of existing = $archivedTime, now = $now) skipping until archive is out of date: $invalidationDesc");
                } else {
                    $this->logger->debug("Found invalidation with usable archive (not yet outdated, ts_archived of existing = $archivedTime, now = $now) ignoring and deleting: $invalidationDesc");
                    $this->model->deleteInvalidations([$invalidatedArchive]);
                }
                continue;
            } else {
                $now = Date::now()->getDatetime();
                $this->logger->debug("No usable archive exists (ts_archived of existing = $archivedTime, now = $now).");
            }

            $alreadyInProgressId = $this->model->isArchiveAlreadyInProgress($invalidatedArchive);
            if ($alreadyInProgressId) {
                $this->addInvalidationToExclude($invalidatedArchive);
                if ($alreadyInProgressId < $invalidatedArchive['idinvalidation']) {
                    $this->logger->debug("Skipping invalidated archive {$invalidatedArchive['idinvalidation']}, invalidation already in progress. Since in progress is older, not removing invalidation.");
                } elseif ($alreadyInProgressId > $invalidatedArchive['idinvalidation']) {
                    $this->logger->debug("Skipping invalidated archive {$invalidatedArchive['idinvalidation']}, invalidation already in progress. Since in progress is newer, will remove invalidation.");
                    $this->model->deleteInvalidations([$invalidatedArchive]);
                }
                continue;
            }

            if ($this->canSkipArchiveBecauseNoPoint($invalidatedArchive)) {
                $this->logger->debug("Found invalidated archive we can skip (no visits): $invalidationDesc");
                $this->addInvalidationToExclude($invalidatedArchive);
                $this->model->deleteInvalidations([$invalidatedArchive]);
                continue;
            }

            $reason = $this->shouldSkipArchiveBecauseLowerPeriodOrSegmentIsInProgress($invalidatedArchive);
            if ($reason) {
                $this->logger->debug("Skipping invalidated archive, $reason: $invalidationDesc");
                $invalidationsToExcludeInBatch[$invalidatedArchive['idinvalidation']] = true;
                $this->addInvalidationToExclude($invalidatedArchive);
                continue;
            }

            $started = $this->model->startArchive($invalidatedArchive);
            if (!$started) { // another process started on this archive, pull another one
                $this->logger->debug("Archive invalidation is being handled by another process: $invalidationDesc");
                $this->addInvalidationToExclude($invalidatedArchive);
                continue;
            }

            $this->addInvalidationToExclude($invalidatedArchive);

            $this->logger->debug("Processing invalidation: $invalidationDesc.");

            $archivesToProcess[] = $invalidatedArchive;
        }

        if (
            empty($archivesToProcess)
            && empty($invalidationsToExcludeInBatch)
        ) { // no invalidated archive left
            /**
             * This event is triggered immediately after the cron archiving process starts archiving data for a single
             * site.
             *
             * Note: multiple archiving processes can post this event.
             *
             * @param int $idSite The ID of the site we're archiving data for.
             * @param string $pid The PID of the process processing archives for this site.
             */
            Piwik::postEvent('CronArchive.archiveSingleSite.finish', array($this->idSite, $this->pid));

            $this->logger->info("Finished archiving for site {idSite}, {requests} API requests, {timer} [{processed} / {totalNum} done]", [
                'idSite' => $this->idSite,
                'processed' => $this->processedSiteCount,
                'totalNum' => $this->websiteIdArchiveList->getNumSites(),
                'timer' => $this->siteTimer,
                'requests' => $this->siteRequests,
            ]);

            $this->idSite = null;
        }

        $this->siteRequests += count($archivesToProcess);

        return $archivesToProcess;
    }

    private function archiveArrayContainsArchive($archiveArray, $archive)
    {
        foreach ($archiveArray as $entry) {
            if (
                $entry['idsite'] == $archive['idsite']
                && $entry['period'] == $archive['period']
                && $entry['date1'] == $archive['date1']
                && $entry['date2'] == $archive['date2']
                && $entry['name'] == $archive['name']
                && $entry['plugin'] == $archive['plugin']
                && $entry['report'] == $archive['report']
            ) {
                return true;
            }
        }
        return false;
    }

    private function getNextInvalidatedArchive($idSite, $extraInvalidationsToIgnore)
    {
        $iterations = 0;
        while ($iterations < 100) {
            $invalidationsToExclude = array_merge($this->invalidationsToExclude, $extraInvalidationsToIgnore);

            $nextArchive = $this->model->getNextInvalidatedArchive($idSite, $this->currentSiteArchivingStartTime, $invalidationsToExclude);
            if (empty($nextArchive)) {
                break;
            }

            $this->detectPluginForArchive($nextArchive);

            $periodLabel = $this->periodIdsToLabels[$nextArchive['period']];
            if (
                !PeriodFactory::isPeriodEnabledForAPI($periodLabel)
                || PeriodFactory::isAnyLowerPeriodDisabledForAPI($periodLabel)
            ) {
                $this->logger->info("Found invalidation for period that is disabled in the API, skipping and removing: {$nextArchive['idinvalidation']}");
                $this->model->deleteInvalidations([$nextArchive]);
                continue;
            }

            $periodDate = $periodLabel == 'range' ? $nextArchive['date1'] . ',' . $nextArchive['date2'] : $nextArchive['date1'];
            $nextArchive['periodObj'] = PeriodFactory::build($periodLabel, $periodDate);

            $isCronArchivingEnabled = $this->findSegmentForArchive($nextArchive);
            if ($isCronArchivingEnabled) {
                return $nextArchive;
            }

            $this->logger->debug("Found invalidation for segment that does not have auto archiving enabled, skipping: {$nextArchive['idinvalidation']}");
            $this->model->deleteInvalidations([$nextArchive]);

            ++$iterations;
        }

        return null;
    }

    private function shouldSkipArchive($archive)
    {
        if ($this->archiveFilter) {
            return $this->archiveFilter->filterArchive($archive);
        }

        return false;
    }

    // public for tests
    public function canSkipArchiveBecauseNoPoint(array $invalidatedArchive)
    {
        $site = new Site($invalidatedArchive['idsite']);

        $periodLabel = $this->periodIdsToLabels[$invalidatedArchive['period']];
        $dateStr = $periodLabel == 'range' ? ($invalidatedArchive['date1'] . ',' . $invalidatedArchive['date2']) : $invalidatedArchive['date1'];
        $period = PeriodFactory::build($periodLabel, $dateStr);

        $segment = new Segment($invalidatedArchive['segment'], [$invalidatedArchive['idsite']]);

        $params = new Parameters($site, $period, $segment);
        if (!empty($invalidatedArchive['plugin'])) {
            $params->setRequestedPlugin($invalidatedArchive['plugin']);
        }

        $loader = new Loader($params);
        return $loader->canSkipThisArchive(); // if no point in archiving, skip
    }

    public function shouldSkipArchiveBecauseLowerPeriodOrSegmentIsInProgress(array $archiveToProcess)
    {
        $inProgressArchives = $this->cliMultiRequestParser->getInProgressArchivingCommands();

        foreach ($inProgressArchives as $archiveBeingProcessed) {
            if (
                empty($archiveBeingProcessed['period'])
                || empty($archiveBeingProcessed['date'])
            ) {
                continue;
            }

            if (
                empty($archiveBeingProcessed['idSite'])
                || $archiveBeingProcessed['idSite'] != $archiveToProcess['idsite']
            ) {
                continue; // different site
            }

            // we don't care about lower periods being concurrent if they are for different segments (that are not "all visits")
            if (
                !empty($archiveBeingProcessed['segment'])
                && !empty($archiveToProcess['segment'])
                && $archiveBeingProcessed['segment'] != $archiveToProcess['segment']
                && urldecode($archiveBeingProcessed['segment']) != $archiveToProcess['segment']
            ) {
                continue;
            }

            $archiveBeingProcessed['periodObj'] = PeriodFactory::build($archiveBeingProcessed['period'], $archiveBeingProcessed['date']);

            if ($this->isArchiveOfLowerPeriod($archiveToProcess, $archiveBeingProcessed)) {
                return "lower or same period in progress in another local climulti process (period = {$archiveBeingProcessed['period']}, date = {$archiveBeingProcessed['date']})";
            }

            if ($this->isArchiveNonSegmentAndInProgressArchiveSegment($archiveToProcess, $archiveBeingProcessed)) {
                return "segment archive in progress for same site/period ({$archiveBeingProcessed['segment']})";
            }
        }

        return false;
    }

    private function isArchiveOfLowerPeriod(array $archiveToProcess, $archiveBeingProcessed)
    {
        /** @var Period $archiveToProcessPeriodObj */
        $archiveToProcessPeriodObj = $archiveToProcess['periodObj'];
        /** @var Period $archivePeriodObj */
        $archivePeriodObj = $archiveBeingProcessed['periodObj'];

        if (
            $archiveToProcessPeriodObj->getId() >= $archivePeriodObj->getId()
            && $archiveToProcessPeriodObj->isPeriodIntersectingWith($archivePeriodObj)
        ) {
            return true;
        }

        return false;
    }

    private function isArchiveNonSegmentAndInProgressArchiveSegment(array $archiveToProcess, array $archiveBeingProcessed)
    {
        // archive is for different site/period
        if (
            empty($archiveBeingProcessed['idSite'])
            || $archiveToProcess['idsite'] != $archiveBeingProcessed['idSite']
            || $archiveToProcess['periodObj']->getId() != $archiveBeingProcessed['periodObj']->getId()
            || $archiveToProcess['periodObj']->getDateStart()->toString() != $archiveBeingProcessed['periodObj']->getDateStart()->toString()
        ) {
            return false;
        }

        return empty($archiveToProcess['segment']) && !empty($archiveBeingProcessed['segment']);
    }

    private function detectPluginForArchive(&$archive)
    {
        $archive['plugin'] = $this->getPluginNameForArchiveIfAny($archive);
    }

    // static so it can be unit tested
    public static function hasIntersectingPeriod(array $archivesToProcess, $invalidatedArchive)
    {
        if (empty($archivesToProcess)) {
            return false;
        }

        foreach ($archivesToProcess as $archive) {
            $isSamePeriod = $archive['period'] == $invalidatedArchive['period']
                && $archive['date1'] == $invalidatedArchive['date1']
                && $archive['date2'] == $invalidatedArchive['date2'];

            // don't do the check for $archvie, if we have the same period and segment as $invalidatedArchive
            // we only want to to do the intersecting periods check if there are different periods or one of the
            // invalidations is for an "all visits" archive.
            //
            // it's allowed to archive the same period concurrently for different segments, where neither is
            // "All Visits"
            if (
                !empty($archive['segment'])
                && !empty($invalidatedArchive['segment'])
                && $archive['segment'] != $invalidatedArchive['segment']
                && $isSamePeriod
            ) {
                continue;
            }

            if ($archive['periodObj']->isPeriodIntersectingWith($invalidatedArchive['periodObj'])) {
                return true;
            }
        }

        return false;
    }

    private function findSegmentForArchive(&$archive)
    {
        $flag = explode('.', $archive['name'])[0];
        if ($flag == 'done') {
            $archive['segment'] = '';
            return true;
        }

        $hash = substr($flag, 4);
        $storedSegment = $this->segmentArchiving->findSegmentForHash($hash, $archive['idsite']);
        if (!isset($storedSegment['definition'])) {
            $this->logger->debug("Could not find stored segment for done flag hash: $flag");
            $archive['segment'] = null;
            return false;
        }

        $archive['segment'] = $storedSegment['definition'];
        return $this->segmentArchiving->isAutoArchivingEnabledFor($storedSegment);
    }

    private function getPluginNameForArchiveIfAny($archive)
    {
        $name = $archive['name'];
        if (strpos($name, '.') === false) {
            return null;
        }

        $parts = explode('.', $name);
        return $parts[1];
    }

    public function ignoreIdInvalidation($idinvalidation)
    {
        $this->invalidationsToExclude[$idinvalidation] = $idinvalidation;
    }

    public function skipToNextSite()
    {
        $this->idSite = null;
    }

    private function addInvalidationToExclude(array $invalidatedArchive)
    {
        $id = $invalidatedArchive['idinvalidation'];
        if (empty($this->invalidationsToExclude[$id])) {
            $this->invalidationsToExclude[$id] = $id;
        }
    }

    private function getNextIdSiteToArchive()
    {
        return $this->websiteIdArchiveList->getNextSiteId();
    }

    private function getInvalidationDescription(array $invalidatedArchive)
    {
        return sprintf(
            "[idinvalidation = %s, idsite = %s, period = %s(%s - %s), name = %s, segment = %s]",
            $invalidatedArchive['idinvalidation'],
            $invalidatedArchive['idsite'],
            $this->periodIdsToLabels[$invalidatedArchive['period']],
            $invalidatedArchive['date1'],
            $invalidatedArchive['date2'],
            $invalidatedArchive['name'],
            $invalidatedArchive['segment'] ?? ''
        );
    }

    // public for test
    public function usableArchiveExists(array $invalidatedArchive)
    {
        $site = new Site($invalidatedArchive['idsite']);

        $periodLabel = $this->periodIdsToLabels[$invalidatedArchive['period']];
        $dateStr = $periodLabel == 'range' ? ($invalidatedArchive['date1'] . ',' . $invalidatedArchive['date2']) : $invalidatedArchive['date1'];
        $period = PeriodFactory::build($periodLabel, $dateStr);

        $segment = new Segment($invalidatedArchive['segment'], [$invalidatedArchive['idsite']]);

        $params = new Parameters($site, $period, $segment);
        if (!empty($invalidatedArchive['plugin'])) {
            $params->setRequestedPlugin($invalidatedArchive['plugin']);
        }

        // if latest archive includes today and is usable (DONE_OK or DONE_INVALIDATED and recent enough), skip
        $today = Date::factoryInTimezone('today', Site::getTimezoneFor($site->getId()));
        $isArchiveIncludesToday = $period->isDateInPeriod($today);
        if (!$isArchiveIncludesToday) {
            return [false, null];
        }

        // if valid archive already exists, do not re-archive
        $minDateTimeProcessedUTC = Date::now()->subSeconds(Rules::getPeriodArchiveTimeToLiveDefault($periodLabel));
        $archiveIdAndVisits = ArchiveSelector::getArchiveIdAndVisits($params, $minDateTimeProcessedUTC, $includeInvalidated = false);
        $idArchives = $archiveIdAndVisits['idArchives'];
        $tsArchived = $archiveIdAndVisits['tsArchived'];

        $tsArchived = !empty($tsArchived) ? Date::factory($tsArchived)->getDatetime() : null;

        if (empty($idArchives)) {
            return [false, $tsArchived];
        }

        return [true, $tsArchived];
    }

    public function getIdSite()
    {
        return $this->idSite;
    }

    /**
     * Set or get the maximum number of sites to process
     *
     * @param int|null $newValue New value or null to just return current value
     *
     * @return int|null New or existing value
     */
    public function setMaxSitesToProcess($newValue = null)
    {
        if (null !== $newValue) {
            $this->maxSitesToProcess = $newValue;
        }
        return $this->maxSitesToProcess;
    }
}
