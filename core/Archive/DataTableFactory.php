<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Archive;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Segment;
use Piwik\Site;

/**
 * Creates a DataTable or Set instance based on an array
 * index created by DataCollection.
 *
 * This class is only used by DataCollection.
 */
class DataTableFactory
{
    const TABLE_METADATA_SEGMENT_INDEX = 'segment';
    const TABLE_METADATA_SEGMENT_PRETTY_INDEX = 'segmentPretty';

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
     * @var Segment
     */
    private $segment;

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
    public function __construct($dataNames, $dataType, $sitesId, $periods, Segment $segment, $defaultRow)
    {
        $this->dataNames = $dataNames;
        $this->dataType = $dataType;
        $this->sitesId = $sitesId;

        //here index period by string only
        $this->periods = $periods;
        $this->segment = $segment;
        $this->defaultRow = $defaultRow;
    }

    /**
     * Returns the ID of the site a table is related to based on the 'site' metadata entry,
     * or null if there is none.
     *
     * @param DataTable $table
     * @return int|null
     */
    public static function getSiteIdFromMetadata(DataTable $table)
    {
        $site = $table->getMetadata(self::TABLE_METADATA_SITE_INDEX);
        if (empty($site)) {
            return null;
        } else {
            return $site->getId();
        }
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

    private function isNumericDataType()
    {
        return $this->dataType == 'numeric';
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
    public function make($index, $resultIndices, $keyMetadata = null)
    {
        $keyMetadata = $keyMetadata ?: $this->getDefaultMetadata();

        if (empty($resultIndices)) {
            // for numeric data, if there's no index (and thus only 1 site & period in the query),
            // we want to display every queried metric name
            if (empty($index)
                && $this->isNumericDataType()
            ) {
                $index = $this->defaultRow;
            }

            $dataTable = $this->createDataTable($index, $keyMetadata);
        } else {
            $dataTable = $this->createDataTableMapFromIndex($index, $resultIndices, $keyMetadata);
        }

        return $dataTable;
    }

    /**
     * Creates a merged DataTable|Map instance using an index of archive data similar to {@link make()}.
     *
     * Whereas {@link make()} creates a Map for each result index (period and|or site), this will only create a Map
     * for a period result index and move all site related indices into one dataTable. This is the same as doing
     * `$dataTableFactory->make()->mergeChildren()` just much faster. It is mainly useful for reports across many sites
     * eg `MultiSites.getAll`. Was done as part of https://github.com/piwik/piwik/issues/6809
     *
     * @param array $index @see DataCollection
     * @param array $resultIndices an array mapping metadata names with pretty metadata labels.
     *
     * @return DataTable|DataTable\Map
     * @throws \Exception
     */
    public function makeMerged($index, $resultIndices)
    {
        if (!$this->isNumericDataType()) {
            throw new \Exception('This method is supposed to work with non-numeric data types but it is not tested. To use it, remove this exception and write tests to be sure it works.');
        }

        $hasSiteIndex   = isset($resultIndices[self::TABLE_METADATA_SITE_INDEX]);
        $hasPeriodIndex = isset($resultIndices[self::TABLE_METADATA_PERIOD_INDEX]);

        $isNumeric = $this->isNumericDataType();
        // to be backwards compatible use a Simple table if needed as it will be formatted differently
        $useSimpleDataTable = !$hasSiteIndex && $isNumeric;

        if (!$hasSiteIndex) {
            $firstIdSite = reset($this->sitesId);
            $index = array($firstIdSite => $index);
        }

        if ($hasPeriodIndex) {
            $dataTable = $this->makeMergedTableWithPeriodAndSiteIndex($index, $resultIndices, $useSimpleDataTable, $isNumeric);
        } else {
            $dataTable = $this->makeMergedWithSiteIndex($index, $useSimpleDataTable, $isNumeric);
        }

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
    private function makeFromBlobRow($blobRow, $keyMetadata)
    {
        if ($blobRow === false) {
            $table = new DataTable();
            $table->setAllTableMetadata($keyMetadata);
            $this->setPrettySegmentMetadata($table);
            return $table;
        }

        if (count($this->dataNames) === 1) {
            return $this->makeDataTableFromSingleBlob($blobRow, $keyMetadata);
        } else {
            return $this->makeIndexedByRecordNameDataTable($blobRow, $keyMetadata);
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
    private function makeDataTableFromSingleBlob($blobRow, $keyMetadata)
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
        $table->setAllTableMetadata(array_merge($table->getAllTableMetadata(), DataCollection::getDataRowMetadata($blobRow), $keyMetadata));
        $this->setPrettySegmentMetadata($table);

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
    private function makeIndexedByRecordNameDataTable($blobRow, $keyMetadata)
    {
        $table = new DataTable\Map();
        $table->setKeyName('recordName');

        $tableMetadata = array_merge(DataCollection::getDataRowMetadata($blobRow), $keyMetadata);

        foreach ($blobRow as $name => $blob) {
            $newTable = DataTable::fromSerializedArray($blob);
            $newTable->setAllTableMetadata(array_merge($newTable->getAllTableMetadata(), $tableMetadata));
            $this->setPrettySegmentMetadata($newTable);

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
    private function createDataTableMapFromIndex($index, $resultIndices, $keyMetadata)
    {
        $result = new DataTable\Map();
        $result->setKeyName(reset($resultIndices));
        $resultIndex = key($resultIndices);

        array_shift($resultIndices);

        $hasIndices = !empty($resultIndices);

        foreach ($index as $label => $value) {
            $keyMetadata[$resultIndex] = $this->createTableIndexMetadata($resultIndex, $label);

            if ($hasIndices) {
                $newTable = $this->createDataTableMapFromIndex($value, $resultIndices, $keyMetadata);
            } else {
                $newTable = $this->createDataTable($value, $keyMetadata);
            }

            $result->addTable($newTable, $this->prettifyIndexLabel($resultIndex, $label));
        }

        return $result;
    }

    private function createTableIndexMetadata($resultIndex, $label)
    {
        if ($resultIndex === DataTableFactory::TABLE_METADATA_SITE_INDEX) {
            return new Site($label);
        } elseif ($resultIndex === DataTableFactory::TABLE_METADATA_PERIOD_INDEX) {
            return $this->periods[$label];
        }
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
            $result = $this->makeFromBlobRow($data, $keyMetadata);
        } else {
            $result = $this->makeFromMetricsArray($data, $keyMetadata);
        }

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
            foreach ($dataTable->getRowsWithoutSummaryRow() as $row) {
                $row->removeSubtable();
            }
            $summaryRow = $dataTable->getRowFromId(DataTable::ID_SUMMARY_ROW);
            if ($summaryRow) {
                $summaryRow->removeSubtable();
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
            if (!empty($blobRow[$blobName])) {
                $subtable = DataTable::fromSerializedArray($blobRow[$blobName]);
                $subtable->setMetadata(self::TABLE_METADATA_PERIOD_INDEX, $dataTable->getMetadata(self::TABLE_METADATA_PERIOD_INDEX));
                $subtable->setMetadata(self::TABLE_METADATA_SITE_INDEX, $dataTable->getMetadata(self::TABLE_METADATA_SITE_INDEX));
                $subtable->setMetadata(self::TABLE_METADATA_SEGMENT_INDEX, $dataTable->getMetadata(self::TABLE_METADATA_SEGMENT_INDEX));
                $subtable->setMetadata(self::TABLE_METADATA_SEGMENT_PRETTY_INDEX, $dataTable->getMetadata(self::TABLE_METADATA_SEGMENT_PRETTY_INDEX));
                $subtable->setMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME, $dataTable->getMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME));

                $this->setSubtables($subtable, $blobRow, $treeLevel + 1);

                // we edit the subtable ID so that it matches the newly table created in memory
                // NB: we don't overwrite the datatableid in the case we are displaying the table expanded.
                if ($this->addMetadataSubtableId) {
                    // this will be written back to the column 'idsubdatatable' just before rendering,
                    // see Renderer/Php.php
                    $row->addMetadata('idsubdatatable_in_db', $row->getIdSubDataTable());
                }

                $row->setSubtable($subtable);
            }
        }
    }

    private function getDefaultMetadata()
    {
        return array(
            DataTableFactory::TABLE_METADATA_SITE_INDEX => new Site(reset($this->sitesId)),
            DataTableFactory::TABLE_METADATA_PERIOD_INDEX => reset($this->periods),
            DataTableFactory::TABLE_METADATA_SEGMENT_INDEX => $this->segment->getString(),
            DataTableFactory::TABLE_METADATA_SEGMENT_PRETTY_INDEX => $this->segment->getString(),
        );
    }

    public function getTableMetadataFor($idSite, $period)
    {
        return [
            DataTableFactory::TABLE_METADATA_SITE_INDEX => new Site($idSite),
            DataTableFactory::TABLE_METADATA_PERIOD_INDEX => $period,
            DataTableFactory::TABLE_METADATA_SEGMENT_INDEX => $this->segment->getString(),
            DataTableFactory::TABLE_METADATA_SEGMENT_PRETTY_INDEX => $this->segment->getString(),
        ];
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
            $period = $this->periods[$label];
            $label = $period->getLabel();
            if ($label === 'week' || $label === 'range') {
                return $period->getRangeString();
            }

            return $period->getPrettyString();
        }
        return $label;
    }

    /**
     * @param $data
     * @return DataTable\Simple
     */
    private function makeFromMetricsArray($data, $keyMetadata)
    {
        $table = new DataTable\Simple();

        if (!empty($data)) {
            $table->setAllTableMetadata(array_merge($table->getAllTableMetadata(), DataCollection::getDataRowMetadata($data), $keyMetadata));
            $this->setPrettySegmentMetadata($table);

            DataCollection::removeMetadataFromDataRow($data);

            $table->addRow(new Row(array(Row::COLUMNS => $data)));
        } else {
            // if we're querying numeric data, we couldn't find any, and we're only
            // looking for one metric, add a row w/ one column w/ value 0. this is to
            // ensure that the PHP renderer outputs 0 when only one column is queried.
            // w/o this code, an empty array would be created, and other parts of Piwik
            // would break.
            if (count($this->dataNames) == 1
                && $this->isNumericDataType()
            ) {
                $name = reset($this->dataNames);
                $table->addRow(new Row(array(Row::COLUMNS => array($name => 0))));
            }

            $table->setAllTableMetadata(array_merge($table->getAllTableMetadata(), $keyMetadata));
            $this->setPrettySegmentMetadata($table);
        }

        $result = $table;
        return $result;
    }

    private function makeMergedTableWithPeriodAndSiteIndex($index, $resultIndices, $useSimpleDataTable, $isNumeric)
    {
        $map = new DataTable\Map();
        $map->setKeyName($resultIndices[self::TABLE_METADATA_PERIOD_INDEX]);

        // we save all tables of the map in this array to be able to add rows fast
        $tables = array();

        foreach ($this->periods as $range => $period) {
            // as the resulting table is "merged", we do only set Period metedata and no metadata for site. Instead each
            // row will have an idsite metadata entry.
            $metadata = array(self::TABLE_METADATA_PERIOD_INDEX => $period);

            if ($useSimpleDataTable) {
                $table = new DataTable\Simple();
            } else {
                $table = new DataTable();
            }

            $table->setAllTableMetadata(array_merge($table->getAllTableMetadata(), $metadata));
            $this->setPrettySegmentMetadata($table);
            $map->addTable($table, $this->prettifyIndexLabel(self::TABLE_METADATA_PERIOD_INDEX, $range));

            $tables[$range] = $table;
        }

        foreach ($index as $idsite => $table) {
            $rowMeta = array('idsite' => $idsite);

            foreach ($table as $range => $row) {
                if (!empty($row)) {
                    $tables[$range]->addRow(new Row(array(
                        Row::COLUMNS  => $row,
                        Row::METADATA => $rowMeta)
                    ));
                } elseif ($isNumeric) {
                    $tables[$range]->addRow(new Row(array(
                        Row::COLUMNS  => $this->defaultRow,
                        Row::METADATA => $rowMeta)
                    ));
                }
            }
        }

        return $map;
    }

    private function makeMergedWithSiteIndex($index, $useSimpleDataTable, $isNumeric)
    {
        if ($useSimpleDataTable) {
            $table = new DataTable\Simple();
        } else {
            $table = new DataTable();
        }

        $table->setAllTableMetadata(array(DataTableFactory::TABLE_METADATA_PERIOD_INDEX => reset($this->periods)));
        $this->setPrettySegmentMetadata($table);

        foreach ($index as $idsite => $row) {

            $meta = array();
            if (isset($row[DataCollection::METADATA_CONTAINER_ROW_KEY])) {
                $meta = $row[DataCollection::METADATA_CONTAINER_ROW_KEY];
            }
            $meta['idsite'] = $idsite;

            if (!empty($row)) {
                $table->addRow(new Row(array(
                    Row::COLUMNS  => $row,
                    Row::METADATA => $meta)
                ));
            } elseif ($isNumeric) {
                $table->addRow(new Row(array(
                    Row::COLUMNS  => $this->defaultRow,
                    Row::METADATA => $meta)
                ));
            }
        }

        return $table;
    }

    private function setPrettySegmentMetadata(DataTable $table)
    {
        $site = $table->getMetadata(self::TABLE_METADATA_SITE_INDEX);
        $idSite = $site ? $site->getId() : false;

        $segmentPretty = $this->segment->getStoredSegmentName($idSite);

        $table->setMetadata('segment', $this->segment->getString());
        $table->setMetadata('segmentPretty', $segmentPretty);
    }
}
