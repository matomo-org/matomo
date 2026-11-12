<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
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

        $records = [];
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

                $columnToSortBy = (string) $record->getColumnToSortByBeforeTruncation();
                $columnsAggregationOperation = null;
                $archiveProcessor->aggregateDataTableRecords(
                    $record->getName(),
                    $record->getMaxRowsInTable(),
                    $record->getMaxRowsInSubtable(),
                    $columnToSortBy,
                    $columnsAggregationOperation,
                    null,
                    false,
                    [],
                    // ties at the row limit are cut by label
                    static function (DataTable $table) use ($columnToSortBy): void {
                        $table->filter('Sort', [$columnToSortBy, 'desc', false, true, static function (): string {
                            return 'label';
                        }]);
                    }
                );
            }
        }

        if (!empty($numericRecordNames)) {
            $archiveProcessor->aggregateNumericMetrics($numericRecordNames);
        }
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $maxRows = Records::getMaxRows($this->getIdSite($archiveProcessor));
        $logAggregator = $archiveProcessor->getLogAggregator();

        $records = [];
        foreach ($this->getIdActionsToBuild($archiveProcessor) as $idAction) {
            $records = array_merge($records, $this->aggregatePage($logAggregator, $idAction, $maxRows));
        }

        return $records;
    }

    /**
     * @return array<string, DataTable|int>
     */
    private function aggregatePage(LogAggregator $logAggregator, int $idAction, int $maxRows): array
    {
        $model = StaticContainer::get(Model::class);
        $records = [];

        $internalReferrers = $model->queryInternalReferrers($logAggregator, $idAction, 'url', $maxRows, -1, RankingQuery::LABEL_SUMMARY_ROW);
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

        // reloads are counted in loops, not as following pages
        $followingActions = $model->queryFollowingActions($logAggregator, $idAction, 'url', $maxRows, false, -1, RankingQuery::LABEL_SUMMARY_ROW);
        $transitionsToFollowingActions = 0;
        foreach ($model->getFollowingActionTypes('url') as $type => $list) {
            $total = 0;
            $records[Records::getRecordName($idAction, $list)] = $this->makeActionsTable($followingActions[$type] ?? [], $type, $total);

            // downloads and outlinks don't end a visit
            if ($type === Action::TYPE_PAGE_URL || $type === Action::TYPE_SITE_SEARCH) {
                $transitionsToFollowingActions += $total;
            }
        }

        $entries = 0;
        $records[Records::getRecordName($idAction, Records::REFERRERS)] = $this->makeReferrersTable(
            $model->queryExternalReferrers($logAggregator, $idAction, 'url', $maxRows, -1, RankingQuery::LABEL_SUMMARY_ROW),
            $entries
        );

        $records[Records::getRecordName($idAction, Records::PAGEVIEWS)] = $pageviews;
        $records[Records::getRecordName($idAction, Records::LOOPS)] = $loops;
        $records[Records::getRecordName($idAction, Records::ENTRIES)] = $entries;
        $records[Records::getRecordName($idAction, Records::EXITS)] = $pageviews - $transitionsToFollowingActions - $loops;

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
     * The selected pages of the website that have been tracked, resolved once per archive.
     *
     * @return list<int>
     */
    private function getIdActions(ArchiveProcessor $archiveProcessor): array
    {
        if (!isset($this->idActionsByArchive[$archiveProcessor])) {
            $idActions = StaticContainer::get(Service::class)->getIdActionsForSite($this->getIdSite($archiveProcessor));
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
