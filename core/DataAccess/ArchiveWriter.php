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
use Piwik\ArchiveProcessor\Rules;
use Piwik\ArchiveProcessor;
use Piwik\Common;
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
    const PREFIX_SQL_LOCK = "locked_";
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
        $this->idSite = $params->getSite()->getId();
        $this->segment = $params->getSegment();
        $this->period = $params->getPeriod();
        $idSites = array($this->idSite);
        $this->doneFlag = Rules::getDoneStringFlagFor($idSites, $this->segment, $this->period->getLabel(), $params->getRequestedPlugin(), $params->isSkipAggregationOfSubTables());
        $this->isArchiveTemporary = $isArchiveTemporary;

        $this->dateStart = $this->period->getDateStart();
    }

    /**
     * @param string $name
     * @param string[] $values
     */
    public function insertBlobRecord($name, $values)
    {
        if (is_array($values)) {
            $clean = array();
            foreach ($values as $id => $value) {
                // for the parent Table we keep the name
                // for example for the Table of searchEngines we keep the name 'referrer_search_engine'
                // but for the child table of 'Google' which has the ID = 9 the name would be 'referrer_search_engine_9'
                $newName = $name;
                if ($id != 0) {
                    //FIXMEA: refactor
                    $newName = $name . '_' . $id;
                }

                $value = $this->compress($value);
                $clean[] = array($newName, $value);
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
        $this->deletePreviousArchiveStatus();
        $this->logArchiveStatusAsFinal();
    }

    protected static function compress($data)
    {
        if (Db::get()->hasBlobDataType()) {
            return gzcompress($data);
        }
        return $data;
    }

    protected function getArchiveLockName()
    {
        $numericTable = $this->getTableNumeric();
        $dbLockName = "allocateNewArchiveId.$numericTable";
        return $dbLockName;
    }

    protected function acquireArchiveTableLock()
    {
        $dbLockName = $this->getArchiveLockName();
        if (Db::getDbLock($dbLockName, $maxRetries = 30) === false) {
            throw new Exception("allocateNewArchiveId: Cannot get named lock $dbLockName.");
        }
    }

    protected function releaseArchiveTableLock()
    {
        $dbLockName = $this->getArchiveLockName();
        Db::releaseDbLock($dbLockName);
    }

    protected function allocateNewArchiveId()
    {
        $this->idArchive = $this->insertNewArchiveId();
        return $this->idArchive;
    }

    /**
     * Locks the archive table to generate a new archive ID.
     *
     * We lock to make sure that
     * if several archiving processes are running at the same time (for different websites and/or periods)
     * then they will each use a unique archive ID.
     *
     * @return int
     */
    protected function insertNewArchiveId()
    {
        $numericTable = $this->getTableNumeric();
        $idSite = $this->idSite;

        $this->acquireArchiveTableLock();

        $locked = self::PREFIX_SQL_LOCK . Common::generateUniqId();
        $date = date("Y-m-d H:i:s");
        $insertSql = "INSERT INTO $numericTable "
            . " SELECT IFNULL( MAX(idarchive), 0 ) + 1,
								'" . $locked . "',
								" . (int)$idSite . ",
								'" . $date . "',
								'" . $date . "',
								0,
								'" . $date . "',
								0 "
            . " FROM $numericTable as tb1";
        Db::get()->exec($insertSql);

        $this->releaseArchiveTableLock();

        $selectIdSql = "SELECT idarchive FROM $numericTable WHERE name = ? LIMIT 1";
        $id = Db::get()->fetchOne($selectIdSql, $locked);
        return $id;
    }

    protected function logArchiveStatusAsIncomplete()
    {
        $statusWhileProcessing = self::DONE_ERROR;
        $this->insertRecord($this->doneFlag, $statusWhileProcessing);
    }

    protected function deletePreviousArchiveStatus()
    {
        // without advisory lock here, the DELETE would acquire Exclusive Lock
        $this->acquireArchiveTableLock();

        Db::query("DELETE FROM " . $this->getTableNumeric() . "
					WHERE idarchive = ? AND (name = '" . $this->doneFlag
                    . "' OR name LIKE '" . self::PREFIX_SQL_LOCK . "%')",
            array($this->getIdArchive())
        );

        $this->releaseArchiveTableLock();
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
        $values = array();

        $valueSeen = false;
        foreach ($records as $record) {
            // don't record zero
            if (empty($record[1])) continue;

            $bind = $bindSql;
            $bind[] = $record[0]; // name
            $bind[] = $record[1]; // value
            $values[] = $bind;

            $valueSeen = $record[1];
        }
        if (empty($values)) return true;

        $tableName = $this->getTableNameToInsert($valueSeen);
        BatchInsert::tableInsertBatch($tableName, $this->getInsertFields(), $values);
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

        // duplicate idarchives are Ignored, see https://github.com/piwik/piwik/issues/987
        $query = "INSERT IGNORE INTO " . $tableName . "
					(" . implode(", ", $this->getInsertFields()) . ")
					VALUES (?,?,?,?,?,?,?,?)";
        $bindSql = $this->getInsertRecordBind();
        $bindSql[] = $name;
        $bindSql[] = $value;
        Db::query($query, $bindSql);
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
