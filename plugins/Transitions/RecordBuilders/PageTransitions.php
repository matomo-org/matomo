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
use Piwik\Metrics;
use Piwik\Period\Day;
use Piwik\Plugins\Transitions\Model;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;

/**
 * Archives the Transitions report of each page a website pre-processes, see {@link Records} for the records.
 */
class PageTransitions extends RecordBuilder
{
    /**
     * @var \WeakMap<ArchiveProcessor, list<int>>
     */
    private \WeakMap $idActionsByArchive;

    public function __construct()
    {
        parent::__construct();

        $this->idActionsByArchive = new \WeakMap();
    }

    public function isEnabled(ArchiveProcessor $archiveProcessor): bool
    {
        return !empty($this->getIdActionsToBuild($archiveProcessor));
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        $maxRows = Records::getMaxRows($this->getIdSite($archiveProcessor));

        $records = [Record::make(Record::TYPE_NUMERIC, Records::DAYS_WITH_VISITS)];
        foreach ($this->getIdActions($archiveProcessor) as $idAction) {
            foreach ($this->getPageRecords($idAction, $maxRows) as $record) {
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
        foreach ($this->getIdActionsToBuild($archiveProcessor) as $idAction) {
            foreach ($this->getPageRecords($idAction, $maxRows) as $record) {
                if ($record->getType() === Record::TYPE_NUMERIC) {
                    $numericRecordNames[] = $record->getName();
                    continue;
                }

                // Truncate keeps the rows with the highest counts; it decides ties at the row limit
                $columnsAggregationOperation = null;
                $archiveProcessor->aggregateDataTableRecords(
                    $record->getName(),
                    $record->getMaxRowsInTable(),
                    $record->getMaxRowsInSubtable(),
                    (string) $record->getColumnToSortByBeforeTruncation(),
                    $columnsAggregationOperation,
                    null,
                    false
                );
            }
        }

        if (!empty($numericRecordNames)) {
            $archiveProcessor->aggregateNumericMetrics($numericRecordNames);
        }

        $archiveProcessor->insertNumericRecord(Records::DAYS_WITH_VISITS, $this->countDaysWithVisits($archiveProcessor));
    }

    /**
     * Counts the days of the period with visits in their archives, also the days archived before a page was selected.
     * The days are archived before the period, and a day without visits has no archive.
     */
    private function countDaysWithVisits(ArchiveProcessor $archiveProcessor): int
    {
        $params = $archiveProcessor->getParams();
        $period = $params->getPeriod();

        $days = [];
        $end = $period->getDateEnd();
        for ($date = $period->getDateStart(); !$date->isLater($end); $date = $date->addDay(1)) {
            $days[] = new Day($date);
        }

        $archive = Archive::factory($params->getSegment(), $days, [$this->getIdSite($archiveProcessor)], false, true);
        if (!$archive instanceof Archive) {
            return 0;
        }
        $archive->forceFetchingWithoutLaunchingArchiving();

        $visitsByDay = $archive->getNumeric('nb_visits');
        $daysWithVisits = 0;
        foreach (is_array($visitsByDay) ? $visitsByDay : [] as $row) {
            $visits = is_array($row) ? ($row['nb_visits'] ?? 0) : 0;
            if (is_numeric($visits) && $visits > 0) {
                $daysWithVisits++;
            }
        }

        return $daysWithVisits;
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $idActions = $this->getIdActionsToBuild($archiveProcessor);
        $maxRows = Records::getMaxRows($this->getIdSite($archiveProcessor));
        $logAggregator = $archiveProcessor->getLogAggregator();
        $model = StaticContainer::get(Model::class);

        $internalReferrers = $model->queryInternalReferrersOfPages($logAggregator, $idActions, $maxRows, RankingQuery::LABEL_SUMMARY_ROW);
        // reloads are counted in loops, not as following pages
        $followingActions = $model->queryFollowingActionsOfPages($logAggregator, $idActions, $maxRows, RankingQuery::LABEL_SUMMARY_ROW);
        $externalReferrers = $model->queryExternalReferrersOfPages($logAggregator, $idActions, $maxRows, RankingQuery::LABEL_SUMMARY_ROW);

        // a website can be archived without visits
        $hasVisits = $archiveProcessor->getNumberOfVisits() > 0;

        $records = [Records::DAYS_WITH_VISITS => $hasVisits ? 1 : 0];
        foreach ($idActions as $idAction) {
            $records = array_merge($records, $this->makePageRecords(
                $idAction,
                $internalReferrers[$idAction] ?? [],
                $followingActions[$idAction] ?? [],
                $externalReferrers[$idAction] ?? [],
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
    private function makePageRecords(int $idAction, array $internalReferrers, array $followingActions, array $externalReferrers, bool $hasVisits): array
    {
        $records = [];
        $partitions = $this->toArray($internalReferrers['result'] ?? []);

        $loops = 0;
        foreach ($this->toArray($internalReferrers['excludedFromLimit'] ?? []) as $row) {
            $loops += $this->getCount($this->toArray($row), Metrics::INDEX_NB_ACTIONS);
        }

        $pageviews = $loops;
        $records[Records::getRecordName($idAction, Records::PREVIOUS_PAGES)] = $this->makeActionsTable(
            $partitions[Model::PARTITION_PAGE] ?? [],
            Action::TYPE_PAGE_URL,
            $pageviews
        );
        $records[Records::getRecordName($idAction, Records::PREVIOUS_SITE_SEARCHES)] = $this->makeActionsTable(
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
            $records[Records::getRecordName($idAction, $list)] = $this->makeActionsTable($followingActions[$type] ?? [], $type, $total);

            // downloads and outlinks don't end a visit
            if ($type === Action::TYPE_PAGE_URL || $type === Action::TYPE_SITE_SEARCH) {
                $transitionsToFollowingActions += $total;
            }
        }

        $entries = 0;
        $records[Records::getRecordName($idAction, Records::REFERRERS)] = $this->makeReferrersTable($externalReferrers, $entries);

        $records[Records::getRecordName($idAction, Records::PAGEVIEWS)] = $pageviews;
        $records[Records::getRecordName($idAction, Records::LOOPS)] = $loops;
        $records[Records::getRecordName($idAction, Records::ENTRIES)] = $entries;
        $records[Records::getRecordName($idAction, Records::EXITS)] = $pageviews - $transitionsToFollowingActions - $loops;
        if ($hasVisits) {
            $records[Records::getRecordName($idAction, Records::DAYS)] = 1;
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
    private function getPageRecords(int $idAction, int $maxRows): array
    {
        $records = [];
        foreach (Records::ACTION_LISTS as $list) {
            $records[] = Record::make(Record::TYPE_BLOB, Records::getRecordName($idAction, $list))
                // the limit applies to the rows besides the summary row
                ->setMaxRowsInTable($maxRows + 1)
                ->setColumnToSortByBeforeTruncation(Metrics::INDEX_NB_ACTIONS);
        }

        $records[] = Record::make(Record::TYPE_BLOB, Records::getRecordName($idAction, Records::REFERRERS))
            ->setMaxRowsInSubtable($maxRows + 1)
            ->setColumnToSortByBeforeTruncation(Metrics::INDEX_NB_VISITS);

        foreach (Records::NUMERICS as $numeric) {
            $records[] = Record::make(Record::TYPE_NUMERIC, Records::getRecordName($idAction, $numeric));
        }

        return $records;
    }

    /**
     * The pages to build: all pages of the website, or the pages of the requested records.
     *
     * @return list<int>
     */
    private function getIdActionsToBuild(ArchiveProcessor $archiveProcessor): array
    {
        $idActions = $this->getIdActions($archiveProcessor);

        $requestedReports = $archiveProcessor->getParams()->getArchiveOnlyReportAsArray();
        if (empty($requestedReports)) {
            return $idActions;
        }

        $requestedIdActions = [];
        foreach ($requestedReports as $requestedReport) {
            $requestedIdActions[] = Records::getIdActionOfRecord((string) $requestedReport);
        }

        return array_values(array_intersect($idActions, $requestedIdActions));
    }

    /**
     * The selected pages of the website that have been tracked, once per archive. Days look them up in the logs,
     * longer periods, which only sum days, use the idactions the days found.
     *
     * @return list<int>
     */
    private function getIdActions(ArchiveProcessor $archiveProcessor): array
    {
        if (!isset($this->idActionsByArchive[$archiveProcessor])) {
            $service = StaticContainer::get(Service::class);
            $idSite = $this->getIdSite($archiveProcessor);
            $idActions = $archiveProcessor->getParams()->getPeriod()->getLabel() === 'day'
                ? $service->getIdActionsForSite($idSite)
                : $service->getStoredIdActionsForSite($idSite);
            $this->idActionsByArchive[$archiveProcessor] = array_values(array_unique($idActions));
        }

        return $this->idActionsByArchive[$archiveProcessor];
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
