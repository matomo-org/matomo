<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\RecordBuilders;

use Piwik\Archive;
use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Log\LoggerInterface;
use Piwik\Metrics;
use Piwik\Period\Range;
use Piwik\Plugins\Transitions\Model;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;

/**
 * Archives the Transitions report of each page a website pre-processes, see {@link Records} for the records. A day
 * archive looks up the idaction each page URL is tracked under now; a URL that isn't tracked gets zero records.
 */
class PageTransitions extends RecordBuilder
{
    /**
     * MAX_EXECUTION_TIME of the log queries in seconds, none if not positive.
     */
    private static float $timeLimit = -1;

    /**
     * @var \WeakMap<ArchiveProcessor, list<int>>
     */
    private \WeakMap $idPagesByArchive;

    /**
     * Websites whose invalid row limit was logged, so it is logged once per process.
     *
     * @var array<int, true>
     */
    private static array $loggedConfigErrors = [];

    public function __construct()
    {
        parent::__construct();

        $this->idPagesByArchive = new \WeakMap();
    }

    /**
     * Runs a build whose log queries stop after the time limit, as builds within a report request do.
     *
     * @param float $timeLimit seconds, none if not positive
     */
    public static function executeWithTimeLimit(float $timeLimit, callable $build): void
    {
        $previousTimeLimit = self::$timeLimit;
        self::$timeLimit = $timeLimit;

        try {
            $build();
        } finally {
            self::$timeLimit = $previousTimeLimit;
        }
    }

    /**
     * Ranges aren't archived: reports sum them from their subperiods. An invalid row limit only stops the Transitions
     * records from being archived, not the other plugins' reports.
     */
    public function isEnabled(ArchiveProcessor $archiveProcessor): bool
    {
        if ($archiveProcessor->getParams()->getPeriod() instanceof Range || empty($this->getIdPagesToBuild($archiveProcessor))) {
            return false;
        }

        $idSite = $this->getIdSite($archiveProcessor);
        try {
            Records::getMaxRows($idSite);
        } catch (\Exception $e) {
            if (!isset(self::$loggedConfigErrors[$idSite])) {
                self::$loggedConfigErrors[$idSite] = true;
                StaticContainer::get(LoggerInterface::class)->error(
                    'The pre-processed Transitions pages of website {idSite} are not archived: {message}',
                    ['idSite' => $idSite, 'message' => $e->getMessage()]
                );
            }

            return false;
        }

        return true;
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        try {
            $maxRows = Records::getMaxRows($this->getIdSite($archiveProcessor));
        } catch (\Exception $e) {
            // nothing is built then, see isEnabled()
            $maxRows = Records::DEFAULT_MAX_ROWS;
        }

        $records = [Record::make(Record::TYPE_NUMERIC, Records::DAYS_WITH_VISITS)];
        foreach ($this->getIdPages($archiveProcessor) as $idPage) {
            foreach ($this->getPageRecords($idPage, $maxRows) as $record) {
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * Sums the records of the subperiods. A rebuild request names one record of a page, and rebuilds all of them.
     */
    public function buildForNonDayPeriod(ArchiveProcessor $archiveProcessor): void
    {
        $maxRows = Records::getMaxRows($this->getIdSite($archiveProcessor));

        $numericRecordNames = [];
        foreach ($this->getIdPagesToBuild($archiveProcessor) as $idPage) {
            foreach ($this->getPageRecords($idPage, $maxRows) as $record) {
                if ($record->getType() === Record::TYPE_NUMERIC) {
                    $numericRecordNames[] = $record->getName();
                    continue;
                }

                // truncated before core's Truncate, which would decide ties at the row limit by itself
                $column = (int) $record->getColumnToSortByBeforeTruncation();
                $columnsAggregationOperation = null;
                $archiveProcessor->aggregateDataTableRecords(
                    $record->getName(),
                    $record->getMaxRowsInTable(),
                    $record->getMaxRowsInSubtable(),
                    (string) $column,
                    $columnsAggregationOperation,
                    null,
                    false,
                    [],
                    fn(DataTable $table) => Records::truncate($table, $maxRows, $column)
                );
            }
        }

        if (!empty($numericRecordNames)) {
            $archiveProcessor->aggregateNumericMetrics($numericRecordNames);
        }

        $daysWithVisits = $this->sumDaysWithVisits($archiveProcessor);
        if ($daysWithVisits !== null) {
            $archiveProcessor->insertNumericRecord(Records::DAYS_WITH_VISITS, $daysWithVisits);
        }
    }

    /**
     * Sums the days with visits of the subperiods, as their page `days` counters are summed. A subperiod with visits
     * archived before any page was selected has no count, so the period gets none and is never served as covered.
     * A subperiod without an archive, or without visits, counts no days.
     */
    private function sumDaysWithVisits(ArchiveProcessor $archiveProcessor): ?int
    {
        $params = $archiveProcessor->getParams();
        $subperiods = array_values($params->getPeriod()->getSubperiods());

        $archive = Archive::factory($params->getSegment(), $subperiods, [$this->getIdSite($archiveProcessor)], false, true);
        if (!$archive instanceof Archive) {
            return null;
        }
        $archive->forceFetchingWithoutLaunchingArchiving();

        // read apart, as a value missing next to another is returned as 0
        $counts = $archive->getNumeric([Records::DAYS_WITH_VISITS]);
        $visits = $archive->getNumeric(['nb_visits']);

        $daysWithVisits = 0;
        foreach ($subperiods as $subperiod) {
            $rangeString = $subperiod->getRangeString();
            $count = is_array($counts) ? $this->toArray($counts[$rangeString] ?? null)[Records::DAYS_WITH_VISITS] ?? null : null;
            $visitsOfSubperiod = is_array($visits) ? $this->toArray($visits[$rangeString] ?? null)['nb_visits'] ?? 0 : 0;
            if (is_numeric($count)) {
                $daysWithVisits += (int) $count;
            } elseif (is_numeric($visitsOfSubperiod) && $visitsOfSubperiod > 0) {
                return null;
            }
        }

        return $daysWithVisits;
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $idSite = $this->getIdSite($archiveProcessor);
        $idActionsByPage = StaticContainer::get(Service::class)->getIdActionsByPage($idSite, $this->getIdPagesToBuild($archiveProcessor));
        // pages with the same URL action are queried once
        $idActions = array_values(array_unique(array_filter($idActionsByPage, 'is_int')));

        // a website can be archived without visits
        $hasVisits = $archiveProcessor->getNumberOfVisits() > 0;

        $internalReferrers = $followingActions = $externalReferrers = [];
        if (!empty($idActions)) {
            $maxRows = Records::getMaxRows($idSite);
            $logAggregator = $archiveProcessor->getLogAggregator();
            $model = StaticContainer::get(Model::class);

            $internalReferrers = $model->queryInternalReferrersOfPages($logAggregator, $idActions, $maxRows, RankingQuery::LABEL_SUMMARY_ROW, self::$timeLimit);
            // reloads are counted in loops, not as following pages
            $followingActions = $model->queryFollowingActionsOfPages($logAggregator, $idActions, $maxRows, RankingQuery::LABEL_SUMMARY_ROW, self::$timeLimit);
            $externalReferrers = $model->queryExternalReferrersOfPages($logAggregator, $idActions, $maxRows, RankingQuery::LABEL_SUMMARY_ROW, self::$timeLimit);
        }

        $records = [Records::DAYS_WITH_VISITS => $hasVisits ? 1 : 0];
        foreach ($idActionsByPage as $idPage => $idAction) {
            // a URL that isn't tracked, or whose action was deleted as unused, had no pageviews
            $records = array_merge($records, $this->makePageRecords(
                $idPage,
                $idAction === null ? [] : $this->toArray($internalReferrers[$idAction] ?? []),
                $idAction === null ? [] : $this->toArray($followingActions[$idAction] ?? []),
                $idAction === null ? [] : $this->toArray($externalReferrers[$idAction] ?? []),
                $hasVisits
            ));
        }

        return $records;
    }

    /**
     * @param array<mixed> $internalReferrers rows of the page's ranking query: `result` by partition, `excludedFromLimit`
     * @param array<mixed> $followingActions rows of the page's ranking query by action type
     * @param array<mixed> $externalReferrers rows of the page's ranking query by referrer type
     * @return array<string, DataTable|int>
     */
    private function makePageRecords(int $idPage, array $internalReferrers, array $followingActions, array $externalReferrers, bool $hasVisits): array
    {
        $records = [];
        $partitions = $this->toArray($internalReferrers['result'] ?? []);

        $loops = 0;
        foreach ($this->toArray($internalReferrers['excludedFromLimit'] ?? []) as $row) {
            $loops += $this->getCount($this->toArray($row), Metrics::INDEX_NB_ACTIONS);
        }

        $pageviews = $loops;
        $records[Records::getRecordName($idPage, Records::PREVIOUS_PAGES)] = $this->makeActionsTable(
            $partitions[Model::PARTITION_PAGE] ?? [],
            Action::TYPE_PAGE_URL,
            $pageviews
        );
        $records[Records::getRecordName($idPage, Records::PREVIOUS_SITE_SEARCHES)] = $this->makeActionsTable(
            $partitions[Model::PARTITION_SITE_SEARCH] ?? [],
            Action::TYPE_SITE_SEARCH,
            $pageviews
        );
        foreach ($this->toArray($partitions[Model::PARTITION_OTHER] ?? []) as $row) {
            $pageviews += $this->getCount($this->toArray($row), Metrics::INDEX_NB_ACTIONS);
        }

        $transitionsToFollowingActions = 0;
        foreach (StaticContainer::get(Model::class)->getFollowingActionTypes('url') as $type => $list) {
            $total = 0;
            $records[Records::getRecordName($idPage, $list)] = $this->makeActionsTable($followingActions[$type] ?? [], $type, $total);

            // downloads and outlinks don't end a visit
            if ($type === Action::TYPE_PAGE_URL || $type === Action::TYPE_SITE_SEARCH) {
                $transitionsToFollowingActions += $total;
            }
        }

        $entries = 0;
        $records[Records::getRecordName($idPage, Records::REFERRERS)] = $this->makeReferrersTable($externalReferrers, $entries);

        $records[Records::getRecordName($idPage, Records::PAGEVIEWS)] = $pageviews;
        $records[Records::getRecordName($idPage, Records::LOOPS)] = $loops;
        $records[Records::getRecordName($idPage, Records::ENTRIES)] = $entries;
        $records[Records::getRecordName($idPage, Records::EXITS)] = $pageviews - $transitionsToFollowingActions - $loops;
        if ($hasVisits) {
            $records[Records::getRecordName($idPage, Records::DAYS)] = 1;
        }

        return $records;
    }

    /**
     * @param mixed $rows rows of the ranking query
     * @param int $total incremented by the count of all rows
     */
    private function makeActionsTable($rows, int $type, int &$total): DataTable
    {
        $model = StaticContainer::get(Model::class);

        $table = new DataTable();
        foreach ($this->toArray($rows) as $row) {
            $row = $this->toArray($row);
            $count = $this->getCount($row, Metrics::INDEX_NB_ACTIONS);
            $total += $count;

            $label = ($row['name'] ?? null) === RankingQuery::LABEL_SUMMARY_ROW
                ? RankingQuery::LABEL_SUMMARY_ROW
                : $model->getActionLabel($row, $type);
            $table->sumRowWithLabel($label, [Metrics::INDEX_NB_ACTIONS => $count]);
        }

        return $table;
    }

    /**
     * @param array<mixed> $rowsByReferrerType rows of the ranking query
     * @param int $entries incremented by the visits of all rows
     */
    private function makeReferrersTable(array $rowsByReferrerType, int &$entries): DataTable
    {
        $table = new DataTable();
        foreach (Model::REFERRER_TYPES as $referrerType) {
            if (empty($rowsByReferrerType[$referrerType])) {
                continue;
            }

            $visits = 0;
            $referrers = new DataTable();
            foreach ($this->toArray($rowsByReferrerType[$referrerType]) as $row) {
                $row = $this->toArray($row);
                $count = $this->getCount($row, Metrics::INDEX_NB_VISITS);
                $visits += $count;

                $label = $row['referrer_data'] ?? null;
                if ($referrerType === Common::REFERRER_TYPE_SEARCH_ENGINE && empty($label)) {
                    $label = Records::UNKNOWN_SEARCH_ENGINE_LABEL;
                } elseif (empty($label) || !is_scalar($label)) {
                    // counted in the visits of the referrer type only, as in the report
                    continue;
                }

                $referrers->sumRowWithLabel((string) $label, [Metrics::INDEX_NB_VISITS => $count]);
            }

            $referrerTypeRow = $table->addRow(new Row([Row::COLUMNS => [
                'label' => $referrerType,
                Metrics::INDEX_NB_VISITS => $visits,
            ]]));
            if ($referrers->getRowsCount() > 0) {
                $referrerTypeRow->setSubtable($referrers);
            }

            $entries += $visits;
        }

        return $table;
    }

    /**
     * @return list<Record>
     */
    private function getPageRecords(int $idPage, int $maxRows): array
    {
        $records = [];
        foreach (Records::ACTION_LISTS as $list) {
            $records[] = Record::make(Record::TYPE_BLOB, Records::getRecordName($idPage, $list))
                // the limit applies to the rows besides the summary row
                ->setMaxRowsInTable($maxRows + 1)
                ->setColumnToSortByBeforeTruncation(Metrics::INDEX_NB_ACTIONS);
        }

        $records[] = Record::make(Record::TYPE_BLOB, Records::getRecordName($idPage, Records::REFERRERS))
            ->setMaxRowsInSubtable($maxRows + 1)
            ->setColumnToSortByBeforeTruncation(Metrics::INDEX_NB_VISITS);

        foreach (Records::NUMERICS as $numeric) {
            $records[] = Record::make(Record::TYPE_NUMERIC, Records::getRecordName($idPage, $numeric));
        }

        return $records;
    }

    /**
     * The pages to build: all pages of {@link getIdPages()}, or the pages of the requested records.
     *
     * @return list<int>
     */
    private function getIdPagesToBuild(ArchiveProcessor $archiveProcessor): array
    {
        $idPages = $this->getIdPages($archiveProcessor);

        $requestedReports = $archiveProcessor->getParams()->getArchiveOnlyReportAsArray();
        if (empty($requestedReports)) {
            return $idPages;
        }

        $requestedIdPages = [];
        foreach ($requestedReports as $requestedReport) {
            $requestedIdPages[] = Records::getIdPageOfRecord((string) $requestedReport);
        }

        return array_values(array_intersect($idPages, $requestedIdPages));
    }

    /**
     * The selected pages of the website that aren't removed, once per archive. A longer period also sums the records of
     * the pages removed since it started, so archiving it again doesn't drop the records of their days.
     *
     * @return list<int>
     */
    private function getIdPages(ArchiveProcessor $archiveProcessor): array
    {
        if (!isset($this->idPagesByArchive[$archiveProcessor])) {
            $idSite = $this->getIdSite($archiveProcessor);
            $service = StaticContainer::get(Service::class);
            $params = $archiveProcessor->getParams();

            $idPages = $service->getPageIds($idSite);
            if ($params->getPeriod()->getLabel() !== 'day') {
                $idPages = array_values(array_unique(array_merge($idPages, $service->getPageIdsRemovedSince($idSite, $params->getDateTimeStart()))));
            }

            $this->idPagesByArchive[$archiveProcessor] = $idPages;
        }

        return $this->idPagesByArchive[$archiveProcessor];
    }

    private function getIdSite(ArchiveProcessor $archiveProcessor): int
    {
        return (int) $archiveProcessor->getParams()->getSite()->getId();
    }

    /**
     * @param array<mixed> $row
     */
    private function getCount(array $row, int $column): int
    {
        $value = $row[$column] ?? 0;

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @param mixed $value
     * @return array<mixed>
     */
    private function toArray($value): array
    {
        return is_array($value) ? $value : [];
    }
}
