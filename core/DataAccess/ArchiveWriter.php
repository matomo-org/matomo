<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataAccess;

use Exception;
use Piwik\Archive;
use Piwik\Archive\Chunk;
use Piwik\ArchiveProcessor\Rules;
use Piwik\ArchiveProcessor;
use Piwik\Db;
use Piwik\Db\BatchInsert;
use Piwik\Period;

/**
 * This class is used to create a new Archive.
 * An Archive is a set of reports (numeric and data tables).
 * New data can be inserted in the archive with insertRecord/insertBulkRecords
 */
class ArchiveWriter
{
    /**
     * Flag stored at the end of the archiving
     *
     * @var int
     */
    const DONE_OK = 1;
    /**
     * Flag stored at the start of the archiving
     * When requesting an Archive, we make sure that non-finished archive are not considered valid
     *
     * @var int
     */
    const DONE_ERROR = 2;
    /**
     * Flag indicates the archive is over a period that is not finished, eg. the current day, current week, etc.
     * Archives flagged will be regularly purged from the DB.
     *
     * @var int
     */
    const DONE_OK_TEMPORARY = 3;

    /**
     * Flag indicated that archive is done but was marked as invalid later and needs to be re-processed during next archiving process
     *
     * @var int
     */
    const DONE_INVALIDATED = 4;

    protected $fields = array('idarchive',
        'idsite',
        'date1',
        'date2',
        'period',
        'ts_archived',
        'name',
        'value');

    public function __construct(ArchiveProcessor\Parameters $params, $isArchiveTemporary)
    {
        $this->idArchive = false;
        $this->idSite    = $params->getSite()->getId();
        $this->segment   = $params->getSegment();
        $this->period    = $params->getPeriod();

        $idSites = array($this->idSite);
        $this->doneFlag = Rules::getDoneStringFlagFor($idSites, $this->segment, $this->period->getLabel(), $params->getRequestedPlugin());
        $this->isArchiveTemporary = $isArchiveTemporary;

        $this->dateStart = $this->period->getDateStart();
    }

    /**
     * @param string $name
     * @param string|string[] $values  A blob string or an array of blob strings. If an array
     *                                 is used, the first element in the array will be inserted
     *                                 with the `$name` name. The others will be splitted into chunks. All subtables
     *                                 within one chunk will be serialized as an array where the index is the
     *                                 subtableId.
     */
    public function insertBlobRecord($name, $values)
    {
        if (is_array($values)) {
            $clean = array();

            if (isset($values[0])) {
                // we always store the root table in a single blob for fast access
                $clean[] = array($name, $this->compress($values[0]));
                unset($values[0]);
            }

            if (!empty($values)) {
                // we move all subtables into chunks
                $chunk  = new Chunk();
                $chunks = $chunk->moveArchiveBlobsIntoChunks($name, $values);
                foreach ($chunks as $index => $subtables) {
                    $clean[] = array($index, $this->compress(serialize($subtables)));
                }
            }

            $this->insertBulkRecords($clean);
            return;
        }

        $values = $this->compress($values);
        $this->insertRecord($name, $values);
    }

    public function getIdArchive()
    {
        if ($this->idArchive === false) {
            throw new Exception("Must call allocateNewArchiveId() first");
        }

        return $this->idArchive;
    }

    public function initNewArchive()
    {
        $this->allocateNewArchiveId();
        $this->logArchiveStatusAsIncomplete();
    }

    public function finalizeArchive()
    {
        $numericTable = $this->getTableNumeric();
        $idArchive    = $this->getIdArchive();

        $this->getModel()->deletePreviousArchiveStatus($numericTable, $idArchive, $this->doneFlag);

        $this->logArchiveStatusAsFinal();
    }

    protected function compress($data)
    {
        if (Db::get()->hasBlobDataType()) {
            return gzcompress($data);
        }

        return $data;
    }

    protected function allocateNewArchiveId()
    {
        $numericTable = $this->getTableNumeric();

        $this->idArchive = $this->getModel()->allocateNewArchiveId($numericTable);
        return $this->idArchive;
    }

    private function getModel()
    {
        return new Model();
    }

    protected function logArchiveStatusAsIncomplete()
    {
        $this->insertRecord($this->doneFlag, self::DONE_ERROR);
    }

    protected function logArchiveStatusAsFinal()
    {
        $status = self::DONE_OK;

        if ($this->isArchiveTemporary) {
            $status = self::DONE_OK_TEMPORARY;
        }

        $this->insertRecord($this->doneFlag, $status);
    }

    protected function insertBulkRecords($records)
    {
        // Using standard plain INSERT if there is only one record to insert
        if ($DEBUG_DO_NOT_USE_BULK_INSERT = false
            || count($records) == 1
        ) {
            foreach ($records as $record) {
                $this->insertRecord($record[0], $record[1]);
            }

            return true;
        }

        $bindSql = $this->getInsertRecordBind();
        $values  = array();

        $valueSeen = false;
        foreach ($records as $record) {
            // don't record zero
            if (empty($record[1])) {
                continue;
            }

            $bind     = $bindSql;
            $bind[]   = $record[0]; // name
            $bind[]   = $record[1]; // value
            $values[] = $bind;

            $valueSeen = $record[1];
        }

        if (empty($values)) {
            return true;
        }

        $tableName = $this->getTableNameToInsert($valueSeen);
        $fields    = $this->getInsertFields();

        BatchInsert::tableInsertBatch($tableName, $fields, $values);

        return true;
    }

    /**
     * Inserts a record in the right table (either NUMERIC or BLOB)
     *
     * @param string $name
     * @param mixed $value
     *
     * @return bool
     */
    public function insertRecord($name, $value)
    {
        if ($this->isRecordZero($value)) {
            return false;
        }

        $tableName = $this->getTableNameToInsert($value);
        $fields    = $this->getInsertFields();
        $record    = $this->getInsertRecordBind();

        $this->getModel()->insertRecord($tableName, $fields, $record, $name, $value);

        return true;
    }

    protected function getInsertRecordBind()
    {
        return array($this->getIdArchive(),
            $this->idSite,
            $this->dateStart->toString('Y-m-d'),
            $this->period->getDateEnd()->toString('Y-m-d'),
            $this->period->getId(),
            date("Y-m-d H:i:s"));
    }

    protected function getTableNameToInsert($value)
    {
        if (is_numeric($value)) {
            return $this->getTableNumeric();
        }

        return ArchiveTableCreator::getBlobTable($this->dateStart);
    }

    protected function getTableNumeric()
    {
        return ArchiveTableCreator::getNumericTable($this->dateStart);
    }

    protected function getInsertFields()
    {
        return $this->fields;
    }

    protected function isRecordZero($value)
    {
        return ($value === '0' || $value === false || $value === 0 || $value === 0.0);
    }
}
