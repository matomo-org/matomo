<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Archive;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Site;

/**
 * Creates a DataTable or Set instance based on an array
 * index created by DataCollection.
 *
 * This class is only used by DataCollection.
 */
class DataTableFactory
{
    /**
     * @see DataCollection::$dataNames.
     */
    private $dataNames;

    /**
     * @see DataCollection::$dataType.
     */
    private $dataType;

    /**
     * Whether to expand the DataTables that're created or not. Expanding a DataTable
     * means creating DataTables using subtable blobs and correctly setting the subtable
     * IDs of all DataTables.
     *
     * @var bool
     */
    private $expandDataTable = false;

    /**
     * Whether to add the subtable ID used in the database to the in-memory DataTables
     * as metadata or not.
     *
     * @var bool
     */
    private $addMetadataSubtableId = false;

    /**
     * The maximum number of subtable levels to create when creating an expanded
     * DataTable.
     *
     * @var int
     */
    private $maxSubtableDepth = null;

    /**
     * @see DataCollection::$sitesId.
     */
    private $sitesId;

    /**
     * @see DataCollection::$periods.
     */
    private $periods;

    /**
     * The ID of the subtable to create a DataTable for. Only relevant for blob data.
     *
     * @var int|null
     */
    private $idSubtable = null;

    /**
     * @see DataCollection::$defaultRow.
     */
    private $defaultRow;

    const TABLE_METADATA_SITE_INDEX = 'site';
    const TABLE_METADATA_PERIOD_INDEX = 'period';

    /**
     * Constructor.
     */
    public function __construct($dataNames, $dataType, $sitesId, $periods, $defaultRow)
    {
        $this->dataNames = $dataNames;
        $this->dataType = $dataType;
        $this->sitesId = $sitesId;

        //here index period by string only
        $this->periods = $periods;
        $this->defaultRow = $defaultRow;
    }

    /**
     * Tells the factory instance to expand the DataTables that are created by
     * creating subtables and setting the subtable IDs of rows w/ subtables correctly.
     *
     * @param null|int $maxSubtableDepth max depth for subtables.
     * @param bool $addMetadataSubtableId Whether to add the subtable ID used in the
     *                                    database to the in-memory DataTables as
     *                                    metadata or not.
     */
    public function expandDataTable($maxSubtableDepth = null, $addMetadataSubtableId = false)
    {
        $this->expandDataTable = true;
        $this->maxSubtableDepth = $maxSubtableDepth;
        $this->addMetadataSubtableId = $addMetadataSubtableId;
    }

    /**
     * Tells the factory instance to create a DataTable using a blob with the
     * supplied subtable ID.
     *
     * @param int $idSubtable An in-database subtable ID.
     * @throws \Exception
     */
    public function useSubtable($idSubtable)
    {
        if (count($this->dataNames) !== 1) {
            throw new \Exception("DataTableFactory: Getting subtables for multiple records in one"
                . " archive query is not currently supported.");
        }

        $this->idSubtable = $idSubtable;
    }

    /**
     * Creates a DataTable|Set instance using an index of
     * archive data.
     *
     * @param array $index @see DataCollection
     * @param array $resultIndices an array mapping metadata names with pretty metadata
     *                             labels.
     * @return DataTable|DataTable\Map
     */
    public function make($index, $resultIndices)
    {
        if (empty($resultIndices)) {
            // for numeric data, if there's no index (and thus only 1 site & period in the query),
            // we want to display every queried metric name
            if (empty($index)
                && $this->dataType == 'numeric'
            ) {
                $index = $this->defaultRow;
            }

            $dataTable = $this->createDataTable($index, $keyMetadata = array());
        } else {
            $dataTable = $this->createDataTableMapFromIndex($index, $resultIndices, $keyMetadata = array());
        }

        $this->transformMetadata($dataTable);

        return $dataTable;
    }

    /**
     * Creates a DataTable|Set instance using an array
     * of blobs.
     *
     * If only one record is being queried, a single DataTable will
     * be returned. Otherwise, a DataTable\Map is returned that indexes
     * DataTables by record name.
     *
     * If expandDataTable was called, and only one record is being queried,
     * the created DataTable's subtables will be expanded.
     *
     * @param array $blobRow
     * @return DataTable|DataTable\Map
     */
    private function makeFromBlobRow($blobRow)
    {
        if ($blobRow === false) {
            return new DataTable();
        }

        if (count($this->dataNames) === 1) {
            return $this->makeDataTableFromSingleBlob($blobRow);
        } else {
            return $this->makeIndexedByRecordNameDataTable($blobRow);
        }
    }

    /**
     * Creates a DataTable for one record from an archive data row.
     *
     * @see makeFromBlobRow
     *
     * @param array $blobRow
     * @return DataTable
     */
    private function makeDataTableFromSingleBlob($blobRow)
    {
        $recordName = reset($this->dataNames);
        if ($this->idSubtable !== null) {
            $recordName .= '_' . $this->idSubtable;
        }

        if (!empty($blobRow[$recordName])) {
            $table = DataTable::fromSerializedArray($blobRow[$recordName]);
        } else {
            $table = new DataTable();
        }

        // set table metadata
        $table->setMetadataValues(DataCollection::getDataRowMetadata($blobRow));

        if ($this->expandDataTable) {
            $table->enableRecursiveFilters();
            $this->setSubtables($table, $blobRow);
        }

        return $table;
    }

    /**
     * Creates a DataTable for every record in an archive data row and puts them
     * in a DataTable\Map instance.
     *
     * @param array $blobRow
     * @return DataTable\Map
     */
    private function makeIndexedByRecordNameDataTable($blobRow)
    {
        $table = new DataTable\Map();
        $table->setKeyName('recordName');

        $tableMetadata = DataCollection::getDataRowMetadata($blobRow);

        foreach ($blobRow as $name => $blob) {
            $newTable = DataTable::fromSerializedArray($blob);
            $newTable->setAllTableMetadata($tableMetadata);

            $table->addTable($newTable, $name);
        }

        return $table;
    }

    /**
     * Creates a Set from an array index.
     *
     * @param array $index @see DataCollection
     * @param array $resultIndices @see make
     * @param array $keyMetadata The metadata to add to the table when it's created.
     * @return DataTable\Map
     */
    private function createDataTableMapFromIndex($index, $resultIndices, $keyMetadata = array())
    {
        $resultIndexLabel = reset($resultIndices);
        $resultIndex = key($resultIndices);

        array_shift($resultIndices);

        $result = new DataTable\Map();
        $result->setKeyName($resultIndexLabel);

        foreach ($index as $label => $value) {
            $keyMetadata[$resultIndex] = $label;

            if (empty($resultIndices)) {
                $newTable = $this->createDataTable($value, $keyMetadata);
            } else {
                $newTable = $this->createDataTableMapFromIndex($value, $resultIndices, $keyMetadata);
            }

            $result->addTable($newTable, $this->prettifyIndexLabel($resultIndex, $label));
        }

        return $result;
    }

    /**
     * Creates a DataTable instance from an index row.
     *
     * @param array $data An archive data row.
     * @param array $keyMetadata The metadata to add to the table(s) when created.
     * @return DataTable|DataTable\Map
     */
    private function createDataTable($data, $keyMetadata)
    {
        if ($this->dataType == 'blob') {
            $result = $this->makeFromBlobRow($data);
        } else {
            $result = $this->makeFromMetricsArray($data);
        }
        $this->setTableMetadata($keyMetadata, $result);
        return $result;
    }

    /**
     * Creates DataTables from $dataTable's subtable blobs (stored in $blobRow) and sets
     * the subtable IDs of each DataTable row.
     *
     * @param DataTable $dataTable
     * @param array $blobRow An array associating record names (w/ subtable if applicable)
     *                           with blob values. This should hold every subtable blob for
     *                           the loaded DataTable.
     * @param int $treeLevel
     */
    private function setSubtables($dataTable, $blobRow, $treeLevel = 0)
    {
        if ($this->maxSubtableDepth
            && $treeLevel >= $this->maxSubtableDepth
        ) {
            // unset the subtables so DataTableManager doesn't throw
            foreach ($dataTable->getRows() as $row) {
                $row->removeSubtable();
            }

            return;
        }

        $dataName = reset($this->dataNames);

        foreach ($dataTable->getRows() as $row) {
            $sid = $row->getIdSubDataTable();
            if ($sid === null) {
                continue;
            }

            $blobName = $dataName . "_" . $sid;
            if (isset($blobRow[$blobName])) {
                $subtable = DataTable::fromSerializedArray($blobRow[$blobName]);
                $this->setSubtables($subtable, $blobRow, $treeLevel + 1);

                // we edit the subtable ID so that it matches the newly table created in memory
                // NB: we dont overwrite the datatableid in the case we are displaying the table expanded.
                if ($this->addMetadataSubtableId) {
                    // this will be written back to the column 'idsubdatatable' just before rendering,
                    // see Renderer/Php.php
                    $row->addMetadata('idsubdatatable_in_db', $row->getIdSubDataTable());
                }

                $row->setSubtable($subtable);
            }
        }
    }

    /**
     * Converts site IDs and period string ranges into Site instances and
     * Period instances in DataTable metadata.
     */
    private function transformMetadata($table)
    {
        $periods = $this->periods;
        $table->filter(function ($table) use ($periods) {
            $table->setMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX, new Site($table->getMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX)));
            $table->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, $periods[$table->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)]);
        });
    }

    /**
     * Returns the pretty version of an index label.
     *
     * @param string $labelType eg, 'site', 'period', etc.
     * @param string $label eg, '0', '1', '2012-01-01,2012-01-31', etc.
     * @return string
     */
    private function prettifyIndexLabel($labelType, $label)
    {
        if ($labelType == self::TABLE_METADATA_PERIOD_INDEX) { // prettify period labels
            return $this->periods[$label]->getPrettyString();
        }
        return $label;
    }

    /**
     * @param $keyMetadata
     * @param $result
     */
    private function setTableMetadata($keyMetadata, $result)
    {
        if (!isset($keyMetadata[DataTableFactory::TABLE_METADATA_SITE_INDEX])) {
            $keyMetadata[DataTableFactory::TABLE_METADATA_SITE_INDEX] = reset($this->sitesId);
        }

        if (!isset($keyMetadata[DataTableFactory::TABLE_METADATA_PERIOD_INDEX])) {
            reset($this->periods);
            $keyMetadata[DataTableFactory::TABLE_METADATA_PERIOD_INDEX] = key($this->periods);
        }

        // Note: $result can be a DataTable\Map
        $result->filter(function ($table) use ($keyMetadata) {
            foreach ($keyMetadata as $name => $value) {
                $table->setMetadata($name, $value);
            }
        });
    }

    /**
     * @param $data
     * @return DataTable\Simple
     */
    private function makeFromMetricsArray($data)
    {
        $table = new DataTable\Simple();

        if (!empty($data)) {
            $table->setAllTableMetadata(DataCollection::getDataRowMetadata($data));

            DataCollection::removeMetadataFromDataRow($data);

            $table->addRow(new Row(array(Row::COLUMNS => $data)));
        } else {
            // if we're querying numeric data, we couldn't find any, and we're only
            // looking for one metric, add a row w/ one column w/ value 0. this is to
            // ensure that the PHP renderer outputs 0 when only one column is queried.
            // w/o this code, an empty array would be created, and other parts of Piwik
            // would break.
            if (count($this->dataNames) == 1
                && $this->dataType == 'numeric'
            ) {
                $name = reset($this->dataNames);
                $table->addRow(new Row(array(Row::COLUMNS => array($name => 0))));
            }
        }

        $result = $table;
        return $result;
    }
}

