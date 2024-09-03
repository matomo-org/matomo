<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Exception;
use Piwik\Archive\Chunk;
use Piwik\ArchiveProcessor\Rules;
use Piwik\ArchiveProcessor;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Db\BatchInsert;
use Piwik\Log\LoggerInterface;
use Piwik\SettingsServer;

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
    public const DONE_OK = 1;
    /**
     * Flag stored at the start of the archiving
     * When requesting an Archive, we make sure that non-finished archive are not considered valid
     *
     * @var int
     */
    public const DONE_ERROR = 2;

    /**
     * Flag indicates the archive is over a period that is not finished, eg. the current day, current week, etc.
     * Archives flagged will be regularly purged from the DB.
     *
     * This flag is deprecated, new archives should not be written as temporary.
     *
     * @var int
     * @deprecated it should not be used anymore as temporary archives have been removed. It still exists though for
     *             historical reasons.
     */
    public const DONE_OK_TEMPORARY = 3;

    /**
     * Flag indicated that archive is done but was marked as invalid later and needs to be re-processed during next archiving process
     *
     * @var int
     */
    public const DONE_INVALIDATED = 4;

    /**
     * Flag indicating that the archive is
     *
     * @var int
     */
    public const DONE_PARTIAL = 5;

    /**
     * Flag indicates an archive that is currently being processed, but has already been invalidated again
     */
    public const DONE_ERROR_INVALIDATED = 6;

    protected $fields = ['idarchive',
        'idsite',
        'date1',
        'date2',
        'period',
        'ts_archived',
        'name',
        'value'];

    private $recordsToWriteSpool = [
        'numeric' => [],
        'blob' => []
    ];

    public const MAX_SPOOL_SIZE = 50;

    /**
     * @var int|false
     */
    public $idArchive;

    /**
     * @var int|null
     */
    private $idSite;

    /**
     * @var \Piwik\Segment
     */
    private $segment;

    /**
     * @var \Piwik\Period
     */
    private $period;

    /**
     * @var ArchiveProcessor\Parameters
     */
    private $parameters;

    /**
     * @var string
     */
    private $earliestNow;

    /**
     * @var string
     */
    private $doneFlag;

    /**
     * @var Date|null
     */
    private $dateStart;

    /**
     * ArchiveWriter constructor.
     * @param ArchiveProcessor\Parameters $params
     * @param bool $isArchiveTemporary Deprecated. Has no effect.
     * @throws Exception
     */
    public function __construct(ArchiveProcessor\Parameters $params)
    {
        $this->idArchive = false;
        $this->idSite    = $params->getSite()->getId();
        $this->segment   = $params->getSegment();
        $this->period    = $params->getPeriod();
        $this->parameters = $params;

        $idSites = [$this->idSite];
        $this->doneFlag = Rules::getDoneStringFlagFor($idSites, $this->segment, $this->period->getLabel(), $params->getRequestedPlugin());

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
            if (isset($values[0])) {
                // we always store the root table in a single blob for fast access
                $this->insertRecord($name, $this->compress($values[0]));
                unset($values[0]);
            }

            if (!empty($values)) {
                // we move all subtables into chunks
                $chunk  = new Chunk();
                $chunks = $chunk->moveArchiveBlobsIntoChunks($name, $values);
                foreach ($chunks as $index => $subtables) {
                    $this->insertRecord($index, $this->compress(serialize($subtables)));
                }
            }
        } else {
            $values = $this->compress($values);
            $this->insertRecord($name, $values);
        }
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
        $idArchive = $this->allocateNewArchiveId();
        $this->logArchiveStatusAsIncomplete();
        return $idArchive;
    }

    public function finalizeArchive()
    {
        if (
            empty($this->recordsToWriteSpool['blob'])
            && count($this->recordsToWriteSpool['numeric']) === 1
            && $this->recordsToWriteSpool['numeric'][0][0] === $this->doneFlag
            && $this->parameters->isPartialArchive()
        ) {
            // This part avoids writing done flags for empty partial archives:
            // We skip writing the records to the database if there aren't any blob records to write,
            // the only available numeric record to write would be the done flag and the archive would only be partial
            return;
        }

        $this->flushSpools();

        $numericTable = $this->getTableNumeric();
        $idArchive    = $this->getIdArchive();

        $doneValue = $this->parameters->isPartialArchive() ? self::DONE_PARTIAL : self::DONE_OK;
        $this->checkDoneValueIsOnlyPartialForPluginArchives($doneValue); // check and log

        $currentStatus = $this->getModel()->getArchiveStatus($numericTable, $idArchive, $this->doneFlag);

        // If the current archive was already invalidated during runtime, directly update status to invalidated instead of done
        if (self::DONE_ERROR_INVALIDATED === $currentStatus) {
            $doneValue = self::DONE_INVALIDATED;
        }

        $this->getModel()->updateArchiveStatus($numericTable, $idArchive, $this->doneFlag, $doneValue);

        if (
            !$this->parameters->isPartialArchive()
            // sanity check, just in case nothing was inserted (the archive status should always be inserted)
            && !empty($this->earliestNow)
        ) {
            $this->getModel()->deleteOlderArchives($this->parameters, $this->doneFlag, $this->earliestNow, $idArchive);
        }
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

    private function batchInsertSpool($valueType)
    {
        $records = $this->recordsToWriteSpool[$valueType];

        $bindSql = $this->getInsertRecordBind();
        $values  = [];

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

        // For numeric records it's faster to do the insert directly; for blobs the data infile is better
        if ($valueType === 'numeric') {
            BatchInsert::tableInsertBatchSql($tableName, $fields, $values);
        } else {
            BatchInsert::tableInsertBatch($tableName, $fields, $values, $throwException = false, $charset = 'latin1');
        }

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

        $valueType = $this->isRecordNumeric($value) ? 'numeric' : 'blob';
        $this->recordsToWriteSpool[$valueType][] = [
            0 => $name,
            1 => $value
        ];

        if (count($this->recordsToWriteSpool[$valueType]) >= self::MAX_SPOOL_SIZE) {
            $this->flushSpool($valueType);
        }

        return true;
    }

    public function flushSpools()
    {
        if (SettingsServer::isArchivePhpTriggered()) {
            Db::executeWithDatabaseWriterReconnectionAttempt(function () {
                $this->flushSpool('numeric');
                $this->flushSpool('blob');
            });
        } else {
            $this->flushSpool('numeric');
            $this->flushSpool('blob');
        }
    }

    private function flushSpool($valueType)
    {
        $numRecords = count($this->recordsToWriteSpool[$valueType]);

        if ($numRecords > 1) {
            $this->batchInsertSpool($valueType);
        } elseif ($numRecords === 1) {
            [$name, $value] = $this->recordsToWriteSpool[$valueType][0];
            $tableName = $this->getTableNameToInsert($value);
            $fields    = $this->getInsertFields();
            $record    = $this->getInsertRecordBind();

            $this->getModel()->insertRecord($tableName, $fields, $record, $name, $value);
        }
        $this->recordsToWriteSpool[$valueType] = [];
    }

    protected function getInsertRecordBind()
    {
        $now = Date::now()->getDatetime();
        if (empty($this->earliestNow)) {
            $this->earliestNow = $now;
        }
        return [$this->getIdArchive(),
            $this->idSite,
            $this->dateStart->toString('Y-m-d'),
            $this->period->getDateEnd()->toString('Y-m-d'),
            $this->period->getId(),
            $now];
    }

    protected function getTableNameToInsert($value)
    {
        if ($this->isRecordNumeric($value)) {
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

    private function isRecordNumeric($value)
    {
        return is_numeric($value);
    }

    private function checkDoneValueIsOnlyPartialForPluginArchives($doneValue)
    {
        // if the done flag is not like done%.PluginName, then it shouldn't be a partial archive.
        // log a warning.
        if ($doneValue == self::DONE_PARTIAL && strpos($this->doneFlag, '.') == false) {
            $ex = new \Exception(sprintf(
                "Trying to create a partial archive w/ an all plugins done flag (done flag = %s). This should not happen.",
                $this->doneFlag
            ));
            StaticContainer::get(LoggerInterface::class)->warning('{exception}', [
                'exception' => $ex,
            ]);
        }
    }
}
