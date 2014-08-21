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
    const CONTENTS_PIECE_RECORD_NAME  = 'Contents_piece';
    const CONTENTS_TARGET_RECORD_NAME = 'Contents_target';
    const CONTENTS_NAME_RECORD_NAME   = 'Contents_name';
    const CONTENT_TARGET_NOT_SET      = 'Piwik_ContentNameNotSet';

    /**
     * @var DataArray[]
     */
    protected $arrays = array();

    function __construct($processor)
    {
        parent::__construct($processor);
        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
        $this->maximumRowsInDataTable         = ArchivingHelper::$maximumRowsInDataTableLevelZero;
        $this->maximumRowsInSubDataTable      = ArchivingHelper::$maximumRowsInSubDataTable;
    }

    protected function getRecordToDimensions()
    {
        return array(
            self::CONTENTS_PIECE_RECORD_NAME  => array('contentPiece'),
            self::CONTENTS_TARGET_RECORD_NAME => array('contentTarget'),
            self::CONTENTS_NAME_RECORD_NAME   => array('contentName')
        );
    }

    public function aggregateMultipleReports()
    {
        $dataTableToSum = $this->getRecordNames();
        $this->getProcessor()->aggregateDataTableRecords($dataTableToSum, $this->maximumRowsInDataTable, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation);
    }

    protected function getRecordNames()
    {
        $mapping = $this->getRecordToDimensions();
        return array_keys($mapping);
    }

    public function aggregateDayReport()
    {
        $this->aggregateDayContents();
        $this->insertDayReports();
    }

    protected function aggregateDayContents()
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
                "joinOn"     => "log_link_visit_action.idaction_name = log_action_content_name.idaction"
            )
        );

        $where = "log_link_visit_action.server_time >= ?
                    AND log_link_visit_action.server_time <= ?
                    AND log_link_visit_action.idsite = ?
                    AND log_link_visit_action.idaction_content_piece IS NOT NULL";

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

        $this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, $rankingQuery);
    }

    protected function archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, RankingQuery $rankingQuery)
    {
        // get query with segmentation
        $query = $this->getLogAggregator()->generateQuery($select, $from, $where, $groupBy, $orderBy);

        // apply ranking query
        if ($rankingQuery) {
            $query['sql'] = $rankingQuery->generateQuery($query['sql']);
        }

        // get result
        $resultSet = $this->getLogAggregator()->getDb()->query($query['sql'], $query['bind']);

        if ($resultSet === false) {
            return;
        }

        while ($row = $resultSet->fetch()) {
            $this->aggregateContentRow($row);
        }
    }

    /**
     * Records the daily datatables
     */
    protected function insertDayReports()
    {
        foreach ($this->arrays as $recordName => $dataArray) {
            $dataTable = $dataArray->asDataTable();
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
    protected function getDataArray($name)
    {
        if(empty($this->arrays[$name])) {
            $this->arrays[$name] = new DataArray();
        }
        return $this->arrays[$name];
    }

    protected function aggregateContentRow($row)
    {
        foreach ($this->getRecordToDimensions() as $record => $dimensions) {
            $dataArray = $this->getDataArray($record);

            $mainDimension = $dimensions[0];
            $mainLabel = $row[$mainDimension];

            // Content target is optional
            if ($mainDimension == 'contentTarget'
                && empty($mainLabel)) {
                $mainLabel = self::CONTENT_TARGET_NOT_SET;
            }
            $dataArray->sumMetricsContents($mainLabel, $row);
        }
    }

}
