<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId;
use Piwik\Config;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Metrics;

/**
 * Archiver that aggregates metrics per user ID (user_id field).
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const USERID_ARCHIVE_RECORD = "UserId_users";

    const VISITOR_ID_FIELD = 'idvisitor';
    const USER_ID_FIELD = 'user_id';

    protected $maximumRowsInDataTableLevelZero;

    function __construct($processor)
    {
        parent::__construct($processor);

        $this->maximumRowsInDataTableLevelZero = Config::getInstance()->General['datatable_archiving_maximum_rows_userid_users'];
    }

    /**
     * @var DataArray
     */
    protected $arrays;

    /**
     * Array to save visitor IDs for every user ID met during archiving process. We use it to
     * fill metadata before actual inserting rows to DB.
     * @var array
     */
    protected $visitorIdsUserIdsMap = array();

    /**
     * Archives data for a day period.
     */
    public function aggregateDayReport()
    {
        $this->arrays = new DataArray();
        $this->aggregateUsers();
        $this->insertDayReports();
    }
    /**
     * Period archiving: simply sums up daily archives
     */
    public function aggregateMultipleReports()
    {
        $dataTableRecords = array(self::USERID_ARCHIVE_RECORD);
        $columnsAggregationOperation = null;
        $this->getProcessor()->aggregateDataTableRecords(
            $dataTableRecords,
            $this->maximumRowsInDataTableLevelZero,
            $this->maximumRowsInDataTableLevelZero,
            $columnToSort = 'nb_visits',
            $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array());
    }

    /**
     * Used to aggregate daily data per user ID
     */
    protected function aggregateUsers()
    {
        $userIdFieldName = self::USER_ID_FIELD;
        $visitorIdFieldName = self::VISITOR_ID_FIELD;

        /** @var \Zend_Db_Statement $query */
        $query = $this->getLogAggregator()->queryVisitsByDimension(
            array(self::USER_ID_FIELD),
            "$userIdFieldName IS NOT NULL AND $userIdFieldName != ''",
            array("LOWER(HEX($visitorIdFieldName)) as $visitorIdFieldName")
        );

        if ($query === false) {
            return;
        }

        $rowsCount = 0;
        while ($row = $query->fetch()) {
            $rowsCount++;
            $this->arrays->sumMetricsVisits($row[$userIdFieldName], $row);
            $this->rememberVisitorId($row);
        }
    }

    /**
     * Insert aggregated daily data serialized
     *
     * @throws \Exception
     */
    protected function insertDayReports()
    {
        /** @var DataTable $dataTable */
        $dataTable = $this->arrays->asDataTable();
        $this->setVisitorIds($dataTable);
        $report = $dataTable->getSerialized($this->maximumRowsInDataTableLevelZero, null, Metrics::INDEX_NB_VISITS);
        $this->getProcessor()->insertBlobRecord(self::USERID_ARCHIVE_RECORD, $report);
    }

    /**
     * Remember visitor ID per user. We use it to fill metadata before actual inserting rows to DB.
     *
     * @param array $row
     */
    private function rememberVisitorId($row)
    {
        if (!empty($row[self::USER_ID_FIELD]) && !empty($row[self::VISITOR_ID_FIELD])) {
            $this->visitorIdsUserIdsMap[$row[self::USER_ID_FIELD]] = $row[self::VISITOR_ID_FIELD];
        }
    }

    /**
     * Fill visitor ID as metadata before actual inserting rows to DB.
     *
     * @param DataTable $dataTable
     */
    private function setVisitorIds(DataTable $dataTable)
    {
        foreach ($dataTable->getRows() as $row) {
            $userId = $row->getColumn('label');
            if (isset($this->visitorIdsUserIdsMap[$userId])) {
                $row->setMetadata(self::VISITOR_ID_FIELD, $this->visitorIdsUserIdsMap[$userId]);
            }
        }
    }

}