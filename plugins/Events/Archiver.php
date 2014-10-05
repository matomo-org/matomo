<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events;

use Piwik\Config;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;

/**
 * Processing reports for Events

    EVENT
    - Category
    - Action
    - Name
    - Value

    METRICS (Events Overview report)
    - Total number of events
    - Unique events
    - Visits with events
    - Events/visit
    - Event value
    - Average event value AVG(custom_float)

    MAIN REPORTS:
    - Top Event Category (total events, unique events, event value, avg+min+max value)
    - Top Event Action   (total events, unique events, event value, avg+min+max value)
    - Top Event Name     (total events, unique events, event value, avg+min+max value)

    COMPOSED REPORTS
    - Top Category > Actions     X
    - Top Category > Names       X
    - Top Actions  > Categories  X
    - Top Actions  > Names       X
    - Top Names    > Actions     X
    - Top Names    > Categories  X

    UI
    - Overview at the top (graph + Sparklines)
    - Below show the left menu, defaults to Top Event Category

    Not MVP:
    - On hover on any row: Show % of total events
    - Add min value metric, max value metric in tooltip
    - List event scope Custom Variables Names > Custom variables values > Event Names > Event Actions
    - List event scope Custom Variables Value > Event Category > Event Names > Event Actions

    NOTES:
    - For a given Name, Category is often constant

 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const EVENTS_CATEGORY_ACTION_RECORD_NAME = 'Events_category_action';
    const EVENTS_CATEGORY_NAME_RECORD_NAME = 'Events_category_name';
    const EVENTS_ACTION_CATEGORY_RECORD_NAME = 'Events_action_category';
    const EVENTS_ACTION_NAME_RECORD_NAME = 'Events_action_name';
    const EVENTS_NAME_ACTION_RECORD_NAME = 'Events_name_action';
    const EVENTS_NAME_CATEGORY_RECORD_NAME = 'Events_name_category';
    const EVENT_NAME_NOT_SET = 'Piwik_EventNameNotSet';

    /**
     * @var DataArray[]
     */
    protected $arrays = array();

    function __construct($processor)
    {
        parent::__construct($processor);
        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
        $this->maximumRowsInDataTable = Config::getInstance()->General['datatable_archiving_maximum_rows_events'];
        $this->maximumRowsInSubDataTable = Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_events'];
    }

    protected function getRecordToDimensions()
    {
        return array(
            self::EVENTS_CATEGORY_ACTION_RECORD_NAME => array("eventCategory", "eventAction"),
            self::EVENTS_CATEGORY_NAME_RECORD_NAME   => array("eventCategory", "eventName"),
            self::EVENTS_ACTION_NAME_RECORD_NAME     => array("eventAction", "eventName"),
            self::EVENTS_ACTION_CATEGORY_RECORD_NAME => array("eventAction", "eventCategory"),
            self::EVENTS_NAME_ACTION_RECORD_NAME     => array("eventName", "eventAction"),
            self::EVENTS_NAME_CATEGORY_RECORD_NAME   => array("eventName", "eventCategory"),
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
        $this->aggregateDayEvents();
        $this->insertDayReports();
    }

    protected function aggregateDayEvents()
    {
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

        $from = array(
            "log_link_visit_action",
            array(
                "table"      => "log_action",
                "tableAlias" => "log_action_event_category",
                "joinOn"     => "log_link_visit_action.idaction_event_category = log_action_event_category.idaction"
            ),
            array(
                "table"      => "log_action",
                "tableAlias" => "log_action_event_action",
                "joinOn"     => "log_link_visit_action.idaction_event_action = log_action_event_action.idaction"
            ),
            array(
                "table"      => "log_action",
                "tableAlias" => "log_action_event_name",
                "joinOn"     => "log_link_visit_action.idaction_name = log_action_event_name.idaction"
            )
        );

        $where = "log_link_visit_action.server_time >= ?
                    AND log_link_visit_action.server_time <= ?
                    AND log_link_visit_action.idsite = ?
                    AND log_link_visit_action.idaction_event_category IS NOT NULL";

        $groupBy = "log_action_event_category.idaction,
                    log_action_event_action.idaction,
                    log_action_event_name.idaction";

        $orderBy = "`" . Metrics::INDEX_NB_VISITS . "` DESC";

        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();
        $rankingQuery = null;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->setOthersLabel(DataTable::LABEL_SUMMARY_ROW);
            $rankingQuery->addLabelColumn(array('eventCategory', 'eventAction', 'eventName'));
            $rankingQuery->addColumn(array(Metrics::INDEX_NB_UNIQ_VISITORS));
            $rankingQuery->addColumn(array(Metrics::INDEX_EVENT_NB_HITS, Metrics::INDEX_NB_VISITS, Metrics::INDEX_EVENT_NB_HITS_WITH_VALUE), 'sum');
            $rankingQuery->addColumn(Metrics::INDEX_EVENT_SUM_EVENT_VALUE, 'sum');
            $rankingQuery->addColumn(Metrics::INDEX_EVENT_MIN_EVENT_VALUE, 'min');
            $rankingQuery->addColumn(Metrics::INDEX_EVENT_MAX_EVENT_VALUE, 'max');
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
            $this->aggregateEventRow($row);
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
        if (empty($this->arrays[$name])) {
            $this->arrays[$name] = new DataArray();
        }
        return $this->arrays[$name];
    }

    protected function aggregateEventRow($row)
    {
        foreach ($this->getRecordToDimensions() as $record => $dimensions) {
            $dataArray = $this->getDataArray($record);

            $mainDimension = $dimensions[0];
            $mainLabel = $row[$mainDimension];

            // Event name is optional
            if ($mainDimension == 'eventName'
                && empty($mainLabel)) {
                $mainLabel = self::EVENT_NAME_NOT_SET;
            }
            $dataArray->sumMetricsEvents($mainLabel, $row);

            $subDimension = $dimensions[1];
            $subLabel = $row[$subDimension];
            if (empty($subLabel)) {
                continue;
            }
            $dataArray->sumMetricsEventsPivot($mainLabel, $subLabel, $row);
        }
    }

}
