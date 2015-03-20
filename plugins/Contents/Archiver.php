<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\RankingQuery;

/**
 * Processing reports for Contents
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const CONTENTS_PIECE_NAME_RECORD_NAME = 'Contents_piece_name';
    const CONTENTS_NAME_PIECE_RECORD_NAME = 'Contents_name_piece';
    const CONTENT_TARGET_NOT_SET          = 'Piwik_ContentTargetNotSet';
    const CONTENT_PIECE_NOT_SET           = 'Piwik_ContentPieceNotSet';

    /**
     * @var DataArray[]
     */
    protected $arrays   = array();
    protected $metadata = array();

    public function __construct($processor)
    {
        parent::__construct($processor);
        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
        $this->maximumRowsInDataTable         = ArchivingHelper::$maximumRowsInDataTableLevelZero;
        $this->maximumRowsInSubDataTable      = ArchivingHelper::$maximumRowsInSubDataTable;
    }

    private function getRecordToDimensions()
    {
        return array(
            self::CONTENTS_PIECE_NAME_RECORD_NAME => array('contentPiece', 'contentName'),
            self::CONTENTS_NAME_PIECE_RECORD_NAME => array('contentName', 'contentPiece')
        );
    }

    public function aggregateMultipleReports()
    {
        $dataTableToSum = $this->getRecordNames();
        $columnsAggregationOperation = null;
        $this->getProcessor()->aggregateDataTableRecords(
            $dataTableToSum,
            $this->maximumRowsInDataTable,
            $this->maximumRowsInSubDataTable,
            $this->columnToSortByBeforeTruncation,
            $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array());
    }

    private function getRecordNames()
    {
        $mapping = $this->getRecordToDimensions();
        return array_keys($mapping);
    }

    public function aggregateDayReport()
    {
        $this->aggregateDayImpressions();
        $this->aggregateDayInteractions();
        $this->insertDayReports();
    }

    private function aggregateDayImpressions()
    {
        $select = "
                log_action_content_piece.name as contentPiece,
                log_action_content_target.name as contentTarget,
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

        $where = "log_link_visit_action.server_time >= ?
                    AND log_link_visit_action.server_time <= ?
                    AND log_link_visit_action.idsite = ?
                    AND log_link_visit_action.idaction_content_name IS NOT NULL
                    AND log_link_visit_action.idaction_content_interaction IS NULL";

        $groupBy = "log_action_content_piece.idaction,
                    log_action_content_target.idaction,
                    log_action_content_name.idaction";

        $orderBy = "`" . Metrics::INDEX_NB_VISITS . "` DESC";

        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();
        $rankingQuery = null;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->setOthersLabel(DataTable::LABEL_SUMMARY_ROW);
            $rankingQuery->addLabelColumn(array('contentPiece', 'contentTarget', 'contentName'));
            $rankingQuery->addColumn(array(Metrics::INDEX_NB_UNIQ_VISITORS));
            $rankingQuery->addColumn(array(Metrics::INDEX_CONTENT_NB_IMPRESSIONS, Metrics::INDEX_NB_VISITS), 'sum');
        }

        $resultSet = $this->archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, $rankingQuery);

        while ($row = $resultSet->fetch()) {
            $this->aggregateImpressionRow($row);
        }
    }

    private function aggregateDayInteractions()
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

        $where = "log_link_visit_action.server_time >= ?
                    AND log_link_visit_action.server_time <= ?
                    AND log_link_visit_action.idsite = ?
                    AND log_link_visit_action.idaction_content_name IS NOT NULL
                    AND log_link_visit_action.idaction_content_interaction IS NOT NULL";

        $groupBy = "log_action_content_piece.idaction,
                    log_action_content_interaction.idaction,
                    log_action_content_name.idaction";

        $orderBy = "`" . Metrics::INDEX_CONTENT_NB_INTERACTIONS . "` DESC";

        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();
        $rankingQuery = null;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->setOthersLabel(DataTable::LABEL_SUMMARY_ROW);
            $rankingQuery->addLabelColumn(array('contentPiece', 'contentInteraction', 'contentName'));
            $rankingQuery->addColumn(array(Metrics::INDEX_CONTENT_NB_INTERACTIONS), 'sum');
        }

        $resultSet = $this->archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, $rankingQuery);

        while ($row = $resultSet->fetch()) {
            $this->aggregateInteractionRow($row);
        }
    }

    private function archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, RankingQuery $rankingQuery)
    {
        // get query with segmentation
        $query = $this->getLogAggregator()->generateQuery($select, $from, $where, $groupBy, $orderBy);

        // apply ranking query
        if ($rankingQuery) {
            $query['sql'] = $rankingQuery->generateRankingQuery($query['sql']);
        }

        // get result
        $resultSet = $this->getLogAggregator()->getDb()->query($query['sql'], $query['bind']);

        if ($resultSet === false) {
            return;
        }

        return $resultSet;
    }

    /**
     * Records the daily datatables
     */
    private function insertDayReports()
    {
        foreach ($this->arrays as $recordName => $dataArray) {

            $dataTable = $dataArray->asDataTable();

            foreach ($dataTable->getRows() as $row) {
                $label = $row->getColumn('label');

                if (!empty($this->metadata[$label])) {
                    foreach ($this->metadata[$label] as $name => $value) {
                        $row->addMetadata($name, $value);
                    }
                }

            }
            $blob = $dataTable->getSerialized(
                $this->maximumRowsInDataTable,
                $this->maximumRowsInSubDataTable,
                $this->columnToSortByBeforeTruncation);
            $this->getProcessor()->insertBlobRecord($recordName, $blob);
        }
    }

    /**
     * @param string $name
     * @return DataArray
     */
    private function getDataArray($name)
    {
        if (empty($this->arrays[$name])) {
            $this->arrays[$name] = new DataArray();
        }

        return $this->arrays[$name];
    }

    private function aggregateImpressionRow($row)
    {
        foreach ($this->getRecordToDimensions() as $record => $dimensions) {
            $dataArray = $this->getDataArray($record);

            $mainDimension = $dimensions[0];
            $mainLabel     = $row[$mainDimension];

            // content piece is optional
            if ($mainDimension == 'contentPiece'
                && empty($mainLabel)) {
                $mainLabel = self::CONTENT_PIECE_NOT_SET;
            }

            $dataArray->sumMetricsImpressions($mainLabel, $row);
            $this->rememberMetadataForRow($row, $mainLabel);

            $subDimension = $dimensions[1];
            $subLabel     = $row[$subDimension];

            if (empty($subLabel)) {
                continue;
            }

            // content piece is optional
            if ($subDimension == 'contentPiece'
                && empty($subLabel)) {
                $subLabel = self::CONTENT_PIECE_NOT_SET;
            }

            $dataArray->sumMetricsContentsImpressionPivot($mainLabel, $subLabel, $row);
        }
    }

    private function aggregateInteractionRow($row)
    {
        foreach ($this->getRecordToDimensions() as $record => $dimensions) {
            $dataArray = $this->getDataArray($record);

            $mainDimension = $dimensions[0];
            $mainLabel     = $row[$mainDimension];

            $dataArray->sumMetricsInteractions($mainLabel, $row);

            $subDimension = $dimensions[1];
            $subLabel     = $row[$subDimension];

            if (empty($subLabel)) {
                continue;
            }

            $dataArray->sumMetricsContentsInteractionPivot($mainLabel, $subLabel, $row);
        }
    }

    private function rememberMetadataForRow($row, $mainLabel)
    {
        $this->metadata[$mainLabel] = array();

        $target = $row['contentTarget'];
        if (empty($target)) {
            $target = Archiver::CONTENT_TARGET_NOT_SET;
        }

        // there can be many different targets
        $this->metadata[$mainLabel]['contentTarget'] = $target;
    }

}
