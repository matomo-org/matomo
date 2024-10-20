<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Events\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Config\GeneralConfig;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\Events\Archiver;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;

class EventReports extends RecordBuilder
{
    public function __construct()
    {
        parent::__construct();
        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
        $this->columnAggregationOps = [
            Metrics::INDEX_EVENT_MIN_EVENT_VALUE => 'min',
            Metrics::INDEX_EVENT_MAX_EVENT_VALUE => 'max',
        ];
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        $idSite = $archiveProcessor->getParams()->getSite()->getId();
        $maximumRowsInDataTable = GeneralConfig::getConfigValue('datatable_archiving_maximum_rows_events', $idSite);
        $maximumRowsInSubDataTable = GeneralConfig::getConfigValue('datatable_archiving_maximum_rows_subtable_events', $idSite);

        /** @var Record[] $records */
        $records = [
            Record::make(Record::TYPE_BLOB, Archiver::EVENTS_CATEGORY_ACTION_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::EVENTS_CATEGORY_NAME_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::EVENTS_ACTION_CATEGORY_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::EVENTS_ACTION_NAME_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::EVENTS_NAME_ACTION_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::EVENTS_NAME_CATEGORY_RECORD_NAME),
        ];

        foreach ($records as $record) {
            $record->setMaxRowsInTable($maximumRowsInDataTable)
                ->setMaxRowsInSubtable($maximumRowsInSubDataTable)
                ->setBlobColumnAggregationOps($this->columnAggregationOps);
        }

        return $records;
    }

    protected function getRecordToDimensions()
    {
        return [
            Archiver::EVENTS_CATEGORY_ACTION_RECORD_NAME => ["eventCategory", "eventAction"],
            Archiver::EVENTS_CATEGORY_NAME_RECORD_NAME   => ["eventCategory", "eventName"],
            Archiver::EVENTS_ACTION_NAME_RECORD_NAME     => ["eventAction", "eventName"],
            Archiver::EVENTS_ACTION_CATEGORY_RECORD_NAME => ["eventAction", "eventCategory"],
            Archiver::EVENTS_NAME_ACTION_RECORD_NAME     => ["eventName", "eventAction"],
            Archiver::EVENTS_NAME_CATEGORY_RECORD_NAME   => ["eventName", "eventCategory"],
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $logAggregator = $archiveProcessor->getLogAggregator();

        $reports = [];

        $select = "
                log_action_event_category.name as eventCategory,
                log_action_event_action.name as eventAction,
                log_action_event_name.name as eventName,

				count(distinct log_link_visit_action.idvisit) as `" . Metrics::INDEX_NB_VISITS . "`,
				count(distinct log_link_visit_action.idvisitor) as `" . Metrics::INDEX_NB_UNIQ_VISITORS . "`,
				count(*) as `" . Metrics::INDEX_EVENT_NB_HITS . "`,

				sum(
					case when " . Action::DB_COLUMN_CUSTOM_FLOAT . " is null
						then 0
						else " . Action::DB_COLUMN_CUSTOM_FLOAT . "
					end
				) as `" . Metrics::INDEX_EVENT_SUM_EVENT_VALUE . "`,
				sum( case when " . Action::DB_COLUMN_CUSTOM_FLOAT . " is null then 0 else 1 end )
				    as `" . Metrics::INDEX_EVENT_NB_HITS_WITH_VALUE . "`,
				min(" . Action::DB_COLUMN_CUSTOM_FLOAT . ") as `" . Metrics::INDEX_EVENT_MIN_EVENT_VALUE . "`,
				max(" . Action::DB_COLUMN_CUSTOM_FLOAT . ") as `" . Metrics::INDEX_EVENT_MAX_EVENT_VALUE . "`
        ";

        $from = [
            "log_link_visit_action",
            [
                "table"      => "log_action",
                "tableAlias" => "log_action_event_category",
                "joinOn"     => "log_link_visit_action.idaction_event_category = log_action_event_category.idaction"
            ],
            [
                "table"      => "log_action",
                "tableAlias" => "log_action_event_action",
                "joinOn"     => "log_link_visit_action.idaction_event_action = log_action_event_action.idaction"
            ],
            [
                "table"      => "log_action",
                "tableAlias" => "log_action_event_name",
                "joinOn"     => "log_link_visit_action.idaction_name = log_action_event_name.idaction"
            ]
        ];

        $where  = $logAggregator->getWhereStatement('log_link_visit_action', 'server_time');
        $where .= " AND log_link_visit_action.idaction_event_category IS NOT NULL";

        $groupBy = "log_link_visit_action.idaction_event_category,
                    log_link_visit_action.idaction_event_action,
                    log_link_visit_action.idaction_name";

        $orderBy = "`" . Metrics::INDEX_NB_VISITS . "` DESC, `eventName`";

        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();
        $rankingQuery = null;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn(['eventCategory', 'eventAction', 'eventName']);
            $rankingQuery->addColumn([Metrics::INDEX_NB_UNIQ_VISITORS]);
            $rankingQuery->addColumn([Metrics::INDEX_EVENT_NB_HITS, Metrics::INDEX_NB_VISITS, Metrics::INDEX_EVENT_NB_HITS_WITH_VALUE], 'sum');
            $rankingQuery->addColumn(Metrics::INDEX_EVENT_SUM_EVENT_VALUE, 'sum');
            $rankingQuery->addColumn(Metrics::INDEX_EVENT_MIN_EVENT_VALUE, 'min');
            $rankingQuery->addColumn(Metrics::INDEX_EVENT_MAX_EVENT_VALUE, 'max');
        }

        $this->archiveDayQueryProcess($reports, $logAggregator, $select, $from, $where, $groupBy, $orderBy, $rankingQuery);

        return $reports;
    }

    protected function archiveDayQueryProcess(
        array &$reports,
        LogAggregator $logAggregator,
        string $select,
        array $from,
        string $where,
        string $groupBy,
        string $orderBy,
        ?RankingQuery $rankingQuery = null
    ): void {
        // get query with segmentation
        $query = $logAggregator->generateQuery($select, $from, $where, $groupBy, $orderBy);

        // apply ranking query
        if ($rankingQuery) {
            $query['sql'] = $rankingQuery->generateRankingQuery($query['sql']);
        }

        // get result
        $resultSet = $logAggregator->getDb()->query($query['sql'], $query['bind']);

        if ($resultSet === false) {
            return;
        }

        while ($row = $resultSet->fetch()) {
            $this->aggregateEventRow($reports, $row);
        }
    }

    protected function aggregateEventRow(array &$reports, array $row): void
    {
        foreach ($this->getRecordToDimensions() as $record => $dimensions) {
            if (empty($reports[$record])) {
                $reports[$record] = new DataTable();
            }

            /** @var DataTable $table */
            $table = $reports[$record];

            $mainDimension = $dimensions[0];
            $mainLabel = $row[$mainDimension];

            // Event name is optional
            if (
                $mainDimension == 'eventName'
                && empty($mainLabel)
            ) {
                $mainLabel = Archiver::EVENT_NAME_NOT_SET;
            }

            $columns = [
                Metrics::INDEX_NB_UNIQ_VISITORS         => $row[Metrics::INDEX_NB_UNIQ_VISITORS] ?? 0,
                Metrics::INDEX_NB_VISITS                => $row[Metrics::INDEX_NB_VISITS] ?? 0,
                Metrics::INDEX_EVENT_NB_HITS            => $row[Metrics::INDEX_EVENT_NB_HITS] ?? 0,
                Metrics::INDEX_EVENT_NB_HITS_WITH_VALUE => $row[Metrics::INDEX_EVENT_NB_HITS_WITH_VALUE] ?? 0,
                Metrics::INDEX_EVENT_SUM_EVENT_VALUE    => round($row[Metrics::INDEX_EVENT_SUM_EVENT_VALUE] ?? 0, 2),
                Metrics::INDEX_EVENT_MIN_EVENT_VALUE    => is_numeric($row[Metrics::INDEX_EVENT_MIN_EVENT_VALUE]) ? round($row[Metrics::INDEX_EVENT_MIN_EVENT_VALUE], 2) : null,
                Metrics::INDEX_EVENT_MAX_EVENT_VALUE    => is_numeric($row[Metrics::INDEX_EVENT_MAX_EVENT_VALUE]) ? round($row[Metrics::INDEX_EVENT_MAX_EVENT_VALUE], 2) : null,
            ];

            $topLevelRow = $table->sumRowWithLabel($mainLabel, $columns, $this->columnAggregationOps);

            $subDimension = $dimensions[1];
            $subLabel = $row[$subDimension];
            if (empty($subLabel)) {
                continue;
            }

            $topLevelRow->sumRowWithLabelToSubtable($subLabel, $columns, $this->columnAggregationOps);
        }
    }
}
