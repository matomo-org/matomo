<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Contents\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\Contents\Archiver;
use Piwik\RankingQuery;

class ContentRecords extends RecordBuilder
{
    public function __construct()
    {
        parent::__construct();

        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
        $this->maxRowsInTable = ArchivingHelper::$maximumRowsInDataTableLevelZero;
        $this->maxRowsInSubtable = ArchivingHelper::$maximumRowsInSubDataTable;
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::CONTENTS_NAME_PIECE_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::CONTENTS_PIECE_NAME_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $reports = [
            Archiver::CONTENTS_PIECE_NAME_RECORD_NAME => new DataTable(),
            Archiver::CONTENTS_NAME_PIECE_RECORD_NAME => new DataTable(),
        ];

        $logAggregator = $archiveProcessor->getLogAggregator();

        $this->aggregateDayImpressions($reports, $logAggregator);
        $this->aggregateDayInteractions($reports, $logAggregator);

        return $reports;
    }


    private function aggregateDayImpressions(array $reports, LogAggregator $logAggregator): void
    {
        $select = "
                log_action_content_piece.name as contentPiece,
                log_action_content_name.name as contentName,

				count(distinct log_link_visit_action.idvisit) as `" . Metrics::INDEX_NB_VISITS . "`,
				count(distinct log_link_visit_action.idvisitor) as `" . Metrics::INDEX_NB_UNIQ_VISITORS . "`,
				count(*) as `" . Metrics::INDEX_CONTENT_NB_IMPRESSIONS . "`
        ";

        $from = array(
            "log_link_visit_action",
            array(
                "table"      => "log_action",
                "tableAlias" => "log_action_content_piece",
                "joinOn"     => "log_link_visit_action.idaction_content_piece = log_action_content_piece.idaction"
            ),
            array(
                "table"      => "log_action",
                "tableAlias" => "log_action_content_target",
                "joinOn"     => "log_link_visit_action.idaction_content_target = log_action_content_target.idaction"
            ),
            array(
                "table"      => "log_action",
                "tableAlias" => "log_action_content_name",
                "joinOn"     => "log_link_visit_action.idaction_content_name = log_action_content_name.idaction"
            )
        );

        $where = $logAggregator->getWhereStatement('log_link_visit_action', 'server_time');
        $where .= " AND log_link_visit_action.idaction_content_name IS NOT NULL
                    AND log_link_visit_action.idaction_content_interaction IS NULL";

        $groupBy = "log_link_visit_action.idaction_content_piece,
                    log_link_visit_action.idaction_content_target,
                    log_link_visit_action.idaction_content_name";

        $orderBy = "`" . Metrics::INDEX_NB_VISITS . "` DESC, `contentName`";

        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();
        $rankingQuery = null;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn(array('contentPiece', 'contentName'));
            $rankingQuery->addColumn(array(Metrics::INDEX_NB_UNIQ_VISITORS));
            $rankingQuery->addColumn(array(Metrics::INDEX_CONTENT_NB_IMPRESSIONS, Metrics::INDEX_NB_VISITS), 'sum');
        }

        $resultSet = $this->archiveDayQueryProcess($logAggregator, $select, $from, $where, $groupBy, $orderBy, $rankingQuery);

        while ($row = $resultSet->fetch()) {
            $this->aggregateImpressionRow($reports, $row);
        }
    }

    private function aggregateDayInteractions(array $reports, LogAggregator $logAggregator): void
    {
        $select = "
                log_action_content_name.name as contentName,
                log_action_content_interaction.name as contentInteraction,
                log_action_content_piece.name as contentPiece,

				count(*) as `" . Metrics::INDEX_CONTENT_NB_INTERACTIONS . "`
        ";

        $from = array(
            "log_link_visit_action",
            array(
                "table"      => "log_action",
                "tableAlias" => "log_action_content_piece",
                "joinOn"     => "log_link_visit_action.idaction_content_piece = log_action_content_piece.idaction"
            ),
            array(
                "table"      => "log_action",
                "tableAlias" => "log_action_content_interaction",
                "joinOn"     => "log_link_visit_action.idaction_content_interaction = log_action_content_interaction.idaction"
            ),
            array(
                "table"      => "log_action",
                "tableAlias" => "log_action_content_name",
                "joinOn"     => "log_link_visit_action.idaction_content_name = log_action_content_name.idaction"
            )
        );

        $where  = $logAggregator->getWhereStatement('log_link_visit_action', 'server_time');
        $where .= " AND log_link_visit_action.idaction_content_name IS NOT NULL
                    AND log_link_visit_action.idaction_content_interaction IS NOT NULL";

        $groupBy = "log_action_content_piece.idaction,
                    log_action_content_interaction.idaction,
                    log_action_content_name.idaction";

        $orderBy = "`" . Metrics::INDEX_CONTENT_NB_INTERACTIONS . "` DESC";

        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();
        $rankingQuery = null;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn(array('contentPiece', 'contentInteraction', 'contentName'));
            $rankingQuery->addColumn(array(Metrics::INDEX_CONTENT_NB_INTERACTIONS), 'sum');
        }

        $resultSet = $this->archiveDayQueryProcess($logAggregator, $select, $from, $where, $groupBy, $orderBy, $rankingQuery);

        while ($row = $resultSet->fetch()) {
            $this->aggregateInteractionRow($reports, $row);
        }
    }

    private function archiveDayQueryProcess(
        LogAggregator $logAggregator,
        string $select,
        array $from,
        string $where,
        string $groupBy,
        string $orderBy,
        ?RankingQuery $rankingQuery = null
    ) {
        // get query with segmentation
        $query = $logAggregator->generateQuery($select, $from, $where, $groupBy, $orderBy);

        // apply ranking query
        if ($rankingQuery) {
            $query['sql'] = $rankingQuery->generateRankingQuery($query['sql']);
        }

        // get result
        $resultSet = $logAggregator->getDb()->query($query['sql'], $query['bind']);
        return $resultSet;
    }


    private function aggregateImpressionRow(array $reports, $row): void
    {
        foreach ($this->getRecordToDimensions() as $record => $dimensions) {
            /** @var DataTable $table */
            $table = $reports[$record];

            $mainDimension = $dimensions[0];
            $mainLabel     = $row[$mainDimension];

            $subDimension = $dimensions[1];
            $subLabel     = $row[$subDimension];

            $columns = [
                Metrics::INDEX_NB_UNIQ_VISITORS => $row[Metrics::INDEX_NB_UNIQ_VISITORS],
                Metrics::INDEX_NB_VISITS => $row[Metrics::INDEX_NB_VISITS],
                Metrics::INDEX_CONTENT_NB_IMPRESSIONS => $row[Metrics::INDEX_CONTENT_NB_IMPRESSIONS],
                Metrics::INDEX_CONTENT_NB_INTERACTIONS => 0,
            ];

            // content piece is optional
            if (
                $mainDimension == 'contentPiece'
                && empty($mainLabel)
            ) {
                $mainLabel = Archiver::CONTENT_PIECE_NOT_SET;
            }

            $topLevelRow = $table->sumRowWithLabel($mainLabel, $columns);

            if (empty($subLabel)) {
                continue;
            }

            // content piece is optional
            if (
                $subDimension == 'contentPiece'
                && empty($subLabel)
            ) {
                $subLabel = Archiver::CONTENT_PIECE_NOT_SET;
            }

            $topLevelRow->sumRowWithLabelToSubtable($subLabel, $columns);
        }
    }

    private function aggregateInteractionRow(array $reports, array $row): void
    {
        foreach ($this->getRecordToDimensions() as $record => $dimensions) {
            /** @var DataTable $table */
            $table = $reports[$record];

            $mainDimension = $dimensions[0];
            $mainLabel     = $row[$mainDimension];

            $subDimension = $dimensions[1];
            $subLabel     = $row[$subDimension];

            $columns = [
                Metrics::INDEX_CONTENT_NB_INTERACTIONS => $row[Metrics::INDEX_CONTENT_NB_INTERACTIONS],
            ];

            // ignore interactions that do not have an impression
            if (!$table->getRowFromLabel($mainLabel)) {
                continue;
            }

            $topLevelRow = $table->sumRowWithLabel($mainLabel, $columns);

            if (empty($subLabel)) {
                continue;
            }

            $topLevelRow->sumRowWithLabelToSubtable($subLabel, $columns);
        }
    }

    private function getRecordToDimensions(): array
    {
        return array(
            Archiver::CONTENTS_PIECE_NAME_RECORD_NAME => array('contentPiece', 'contentName'),
            Archiver::CONTENTS_NAME_PIECE_RECORD_NAME => array('contentName', 'contentPiece')
        );
    }
}
