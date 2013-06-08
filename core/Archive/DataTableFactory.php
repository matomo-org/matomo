<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

const FIX_ME_OMG = 'this is a warning and reminder to fix this code ';

/**
 * Creates a Piwik_DataTable or Piwik_DataTable_Array instance based on an array
 * index created by Piwik_Archive_DataCollection.
 * 
 * This class is only used by Piwik_Archive_DataCollection.
 */
class Piwik_Archive_DataTableFactory
{
    /**
     * @see Piwik_Archive_DataCollection::$dataNames.
     */
    private $dataNames;
    
    /**
     * @see Piwik_Archive_DataCollection::$dataType.
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
     * @see Piwik_Archive_DataCollection::$sitesId.
     */
    private $sitesId;
    
    /**
     * @see Piwik_Archive_DataCollection::$periods.
     */
    private $periods;
    
    /**
     * The ID of the subtable to create a DataTable for. Only relevant for blob data.
     * 
     * @var int|null
     */
    private $idSubtable = null;
    
    /**
     * @see Piwik_Archive_DataCollection::$defaultRow.
     */
    private $defaultRow;
    
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
     * @param bool $addMetadataSubtableId Whether to add the subtable ID used in the
     *                                    database to the in-memory DataTables as
     *                                    metadata or not.
     */
    public function expandDataTable($addMetadataSubtableId = false)
    {
        $this->expandDataTable = true;
        $this->addMetadataSubtableId = $addMetadataSubtableId;
    }
    
    /**
     * Tells the factory instance to create a DataTable using a blob with the
     * supplied subtable ID.
     * 
     * @param int $idSubtable An in-database subtable ID.
     */
    public function useSubtable($idSubtable)
    {
        if (count($this->dataNames) !== 1) {
            throw new Exception("Piwik_Archive_DataTableFactory: Getting subtables for multiple records in one"
                               . " archive query is not currently supported.");
        }
        
        $this->idSubtable = $idSubtable;
    }
    
    /**
     * Creates a Piwik_DataTable|Piwik_DataTable_Array instance using an index of
     * archive data.
     * 
     * @param array $index @see Piwik_Archive_DataCollection
     * @param array $resultIndices an array mapping metadata names with pretty metadata
     *                             labels.
     * @return Piwik_DataTable|Piwik_DataTable_Array
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
            $dataTable = $this->createDataTableArrayFromIndex($index, $resultIndices);
        }
        
        $this->transformMetadata($dataTable);
        return $dataTable;
    }

    /**
     * Creates a Piwik_DataTable|Piwik_DataTable_Array instance using an array
     * of blobs.
     * 
     * If only one record is being queried, a single DataTable will
     * be returned. Otherwise, a DataTable_Array is returned that indexes
     * DataTables by record name.
     * 
     * If expandDataTable was called, and only one record is being queried,
     * the created DataTable's subtables will be expanded.
     * 
     * @param array $blobRow
     * @return Piwik_DataTable|Piwik_DataTable_Array
     */
    private function makeFromBlobRow($blobRow)
    {
        if ($blobRow === false) {
            return new Piwik_DataTable();
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
     * @return Piwik_DataTable
     */
    private function makeDataTableFromSingleBlob($blobRow)
    {
        $recordName = reset($this->dataNames);
        if ($this->idSubtable !== null) {
            $recordName .= '_' . $this->idSubtable;
        }
        
        if (!empty($blobRow[$recordName])) {
            $table = Piwik_DataTable::fromSerializedArray($blobRow[$recordName]);
        } else {
            $table = new Piwik_DataTable();
        }
        
        // set table metadata
        $table->metadata = Piwik_Archive_DataCollection::getDataRowMetadata($blobRow);
        
        if ($this->expandDataTable) {
            $table->enableRecursiveFilters();
            $this->setSubtables($table, $blobRow);
        }
        
        return $table;
    }
    
    /**
     * Creates a DataTable for every record in an archive data row and puts them
     * in a DataTable_Array instance.
     * 
     * @param array $blobRow
     * @return Piwik_DataTable_Array
     */
    private function makeIndexedByRecordNameDataTable($blobRow)
    {
        $table = new Piwik_DataTable_Array();
        $table->setKeyName('recordName');
        
        $tableMetadata = Piwik_Archive_DataCollection::getDataRowMetadata($blobRow);
        
        foreach ($blobRow as $name => $blob) {
            $newTable = Piwik_DataTable::fromSerializedArray($blob);
            $newTable->metadata = $tableMetadata;
            
            $table->addTable($newTable, $name);
        }
        
        return $table;
    }
    
    /**
     * Creates a Piwik_DataTable_Array from an array index.
     * 
     * @param array $index @see Piwik_Archive_DataCollection
     * @param array $resultIndices @see make
     * @param array $keyMetadata The metadata to add to the table when it's created.
     */
    private function createDataTableArrayFromIndex($index, $resultIndices, $keyMetadata = array())
    {
        $resultIndexLabel = reset($resultIndices);
        $resultIndex = key($resultIndices);
        
        array_shift($resultIndices);
        
        $result = new Piwik_DataTable_Array();
        $result->setKeyName($resultIndexLabel);
        
        foreach ($index as $label => $value) {
            $keyMetadata[$resultIndex] = $label;
            
            if (empty($resultIndices)) {
                $newTable = $this->createDataTable($value, $keyMetadata);
            } else {
                $newTable = $this->createDataTableArrayFromIndex($value, $resultIndices, $keyMetadata);
            }
            
            $result->addTable($newTable, $this->prettifyIndexLabel($resultIndex, $label));
        }
        
        return $result;
    }
    
    /**
     * Creates a Piwik_DataTable instance from an index row.
     * 
     * @param array|false $data An archive data row.
     * @param array $keyMetadata The metadata to add to the table(s) when created.
     * @return Piwik_DataTable|Piwik_DataTable_Array
     */
    private function createDataTable($data, $keyMetadata)
    {
        if ($this->dataType == 'blob') {
            $result = $this->makeFromBlobRow($data);
        } else {
            $table = new Piwik_DataTable_Simple();
            
            if (!empty($data)) {
                $table->metadata = Piwik_Archive_DataCollection::getDataRowMetadata($data);
                
                Piwik_Archive_DataCollection::removeMetadataFromDataRow($data);
                
                $table->addRow(new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => $data)));
            } else {
                // if we're querying numeric data, we couldn't find any, and we're only
                // looking for one metric, add a row w/ one column w/ value 0. this is to
                // ensure that the PHP renderer outputs 0 when only one column is queried.
                // w/o this code, an empty array would be created, and other parts of Piwik
                // would break.
                if (count($this->dataNames) == 1) {
                    $name = reset($this->dataNames);
                    $table->addRow(new Piwik_DataTable_Row(array(
                        Piwik_DataTable_Row::COLUMNS => array($name => 0)
                    )));
                }
            }
            
            $result = $table;
        }
        
        if (!isset($keyMetadata['site'])) {
            $keyMetadata['site'] = reset($this->sitesId);
        }
        
        if (!isset($keyMetadata['period'])) {
            reset($this->periods);
            $keyMetadata['period'] = key($this->periods);
        }
        
        // Note: $result can be a DataTable_Array
        $result->filter(function ($table) use ($keyMetadata) {
            foreach ($keyMetadata as $name => $value) {
                $table->setMetadata($name, $value);
            }
        });
        
        return $result;
    }
    
    /**
     * Creates DataTables from $dataTable's subtable blobs (stored in $blobRow) and sets
     * the subtable IDs of each DataTable row.
     * 
     * @param Piwik_DataTable $dataTable
     * @param array $blobRow An array associating record names (w/ subtable if applicable)
     *                       with blob values. This should hold every subtable blob for
     *                       the loaded DataTable.
     */
    private function setSubtables($dataTable, $blobRow)
    {
        $dataName = reset($this->dataNames);
        
        foreach ($dataTable->getRows() as $row) {
            $sid = $row->getIdSubDataTable();
            if ($sid === null) {
                continue;
            }
            
            $blobName = $dataName."_".$sid;
            if (isset($blobRow[$blobName])) {
                $subtable = Piwik_DataTable::fromSerializedArray($blobRow[$blobName]);
                $this->setSubtables($subtable, $blobRow);
                
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
     * Converts site IDs and period string ranges into Piwik_Site instances and
     * Piwik_Period instances in DataTable metadata.
     */
    private function transformMetadata($table)
    {
        $periods = $this->periods;
        $table->filter(function ($table) use($periods) {
            $table->metadata['site'] = new Piwik_Site($table->metadata['site']);
            $table->metadata['period'] = empty($periods[$table->metadata['period']])
                                            ? FIX_ME_OMG
                                            : $periods[$table->metadata['period']];
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
        if(empty($this->periods[$label])) {
            return $label; // BAD BUG FIXME
        }
        if ($labelType == 'period') { // prettify period labels
            return $this->periods[$label]->getPrettyString();
        }
        return $label;
    }
}
