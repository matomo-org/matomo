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

/**
 * This class is used to hold and transform archive data for the Piwik_Archive class.
 * 
 * Archive data is loaded into an instance of this type, can be indexed by archive
 * metadata (such as the site ID, period string, etc.), and can be transformed into
 * Piwik_DataTable and Piwik_DataTable_Array instances.
 */
class Piwik_Archive_DataCollection
{
    /**
     * The archive data, indexed first by site ID and then by period date range. Eg,
     * 
     * array(
     *     '0' => array(
     *         array(
     *             '2012-01-01,2012-01-01' => array(...),
     *             '2012-01-02,2012-01-02' => array(...),
     *         )
     *     ),
     *     '1' => array(
     *         array(
     *             '2012-01-01,2012-01-01' => array(...),
     *         )
     *     )
     * )
     * 
     * Archive data can be either a numeric value or a serialized string blob. Every
     * piece of archive data is associated by it's archive name. For example,
     * the array(...) above could look like:
     * 
     * array(
     *    'nb_visits' => 1,
     *    'nb_actions' => 2
     * )
     * 
     * There is a special element '_metadata' in data rows that holds values treated
     * as DataTable metadata.
     */
    private $data = array();
    
    /**
     * The whole list of metric/record names that were used in the archive query.
     * 
     * @var array
     */
    private $dataNames;
    
    /**
     * The type of data that was queried for (ie, "blob" or "numeric").
     * 
     * @var string
     */
    private $dataType;
    
    /**
     * The default values to use for each metric/record name that's being queried
     * for.
     * 
     * @var array
     */
    private $defaultRow;
    
    /**
     * The list of all site IDs that were queried for.
     * 
     * @var array
     */
    private $sitesId;
    
    /**
     * The list of all periods that were queried for. Each period is associated with
     * the period's range string. Eg,
     * 
     * array(
     *     '2012-01-01,2012-01-31' => new Piwik_Period(...),
     *     '2012-02-01,2012-02-28' => new Piwik_Period(...),
     * )
     * 
     * @var array
     */
    private $periods;
    
    /**
     * Constructor.
     * 
     * @param array $dataNames @see $this->dataNames
     * @param string $dataType @see $this->dataType
     * @param array $sitesId @see $this->sitesId
     * @param array $periods @see $this->periods
     * @param array $defaultRow @see $this->defaultRow
     */
    public function __construct($dataNames, $dataType, $sitesId, $periods, $defaultRow = null)
    {
        $this->dataNames = $dataNames;
        $this->dataType = $dataType;
        
        if ($defaultRow === null) {
            $defaultRow = array_fill_keys($dataNames, 0);
        }

        //FIXMEA
        $this->sitesId = $sitesId;

        foreach ($periods as $period) {
            $this->periods[$period->getRangeString()] = $period;
        }
        $this->defaultRow = $defaultRow;
    }
    
    /**
     * Returns a reference to the data for a specific site & period. If there is
     * no data for the given site ID & period, it is set to the default row.
     * 
     * @param int $idSite
     * @param string $period eg, '2012-01-01,2012-01-31'
     */
    public function &get($idSite, $period)
    {
        if (!isset($this->data[$idSite][$period])) {
            $this->data[$idSite][$period] = $this->defaultRow;
        }
        return $this->data[$idSite][$period];
    }
    
    /**
     * Adds a new metadata to the data for specific site & period. If there is no
     * data for the given site ID & period, it is set to the default row.
     * 
     * Note: Site ID and period range string are two special types of metadata. Since
     * the data stored in this class is indexed by site & period, this metadata is not
     * stored in individual data rows.
     * 
     * @param int $idSite
     * @param string $period eg, '2012-01-01,2012-01-31'
     * @param string $name The metadata name.
     * @param mixed $value The metadata name.
     */
    public function addMetadata($idSite, $period, $name, $value)
    {
        $row = &$this->get($idSite, $period);
        $row['_metadata'][$name] = $value;
    }
    
    /**
     * Returns archive data as an array indexed by metadata.
     * 
     * @param array $resultIndices An array mapping metadata names to pretty labels
     *                             for them. Each archive data row will be indexed
     *                             by the metadata specified here.
     *                             
     *                             Eg, array('site' => 'idSite', 'period' => 'Date')
     * @return array
     */
    public function getArray($resultIndices)
    {
        $indexKeys = array_keys($resultIndices);
        
        $result = $this->createEmptyIndex($indexKeys);
        foreach ($this->data as $idSite => $rowsByPeriod) {
            foreach ($rowsByPeriod as $period => $row) {
                // FIXME: This hack works around a strange bug that occurs when getting
                //         archive IDs through ArchiveProcessing instances. When a table
                //         does not already exist, for some reason the archive ID for
                //         today (or from two days ago) will be added to the Archive
                //         instances list. The Archive instance will then select data
                //         for periods outside of the requested set.
                //         working around the bug here, but ideally, we need to figure
                //         out why incorrect idarchives are being selected.
                if (empty($this->periods[$period])) {
                    continue;
                }
                
                $indexRowKeys = $this->getRowKeys($indexKeys, $row, $idSite, $period);
                
                $this->setIndexRow($result, $indexRowKeys, $row);
            }
        }
        return $result;
    }
    
    /**
     * Returns archive data as a DataTable indexed by metadata. Indexed data will
     * be represented by Piwik_DataTable_Array instances.
     * 
     * @param array $resultIndices An array mapping metadata names to pretty labels
     *                             for them. Each archive data row will be indexed
     *                             by the metadata specified here.
     *                             
     *                             Eg, array('site' => 'idSite', 'period' => 'Date')
     * @return Piwik_DataTable|Piwik_DataTable_Array
     */
    public function getDataTable($resultIndices)
    {
        $dataTableFactory = new Piwik_Archive_DataTableFactory(
            $this->dataNames, $this->dataType, $this->sitesId, $this->periods, $this->defaultRow);
        
        $index = $this->getArray($resultIndices);
        return $dataTableFactory->make($index, $resultIndices);
    }
    
    /**
     * Returns archive data as a DataTable indexed by metadata. Indexed data will
     * be represented by Piwik_DataTable_Array instances. Each DataTable will have
     * its subtable IDs set.
     * 
     * This function will only work if blob data was loaded and only one record
     * was loaded (not including subtables of the record).
     * 
     * @param array $resultIndices An array mapping metadata names to pretty labels
     *                             for them. Each archive data row will be indexed
     *                             by the metadata specified here.
     *                             
     *                             Eg, array('site' => 'idSite', 'period' => 'Date')
     * @param int $idSubtable The subtable to return.
     * @param bool $addMetadataSubtableId Whether to add the DB subtable ID as metadata
     *                                    to each datatable, or not.
     */
    public function getExpandedDataTable($resultIndices, $idSubtable = null, $addMetadataSubtableId = false)
    {
        if ($this->dataType != 'blob') {
            throw new Exception("Piwik_Archive_DataCollection: cannot call getExpandedDataTable with "
                               . "{$this->dataType} data types. Only works with blob data.");
        }
        
        if (count($this->dataNames) !== 1) {
            throw new Exception("Piwik_Archive_DataCollection: cannot call getExpandedDataTable with "
                               . "more than one record.");
        }
        
        $dataTableFactory = new Piwik_Archive_DataTableFactory(
            $this->dataNames, 'blob', $this->sitesId, $this->periods, $this->defaultRow);
        $dataTableFactory->expandDataTable($addMetadataSubtableId);
        $dataTableFactory->useSubtable($idSubtable);
        
        $index = $this->getArray($resultIndices);
        return $dataTableFactory->make($index, $resultIndices);
    }
    
    /**
     * Returns metadata for a data row.
     * 
     * @param array $data The data row.
     */
    public static function getDataRowMetadata($data)
    {
        if (isset($data['_metadata'])) {
            return $data['_metadata'];
        } else {
            return array();
        }
    }
    
    /**
     * Removes all table metadata from a data row.
     * 
     * @param array $data The data row.
     */
    public static function removeMetadataFromDataRow(&$data)
    {
        unset($data['_metadata']);
    }
    
    /**
     * Creates an empty index using a list of metadata names. If the 'site' and/or
     * 'period' metadata names are supplied, empty rows are added for every site/period
     * that was queried for.
     * 
     * @param array $indexKeys List of metadata names to index archive data by.
     * @return array
     */
    private function createEmptyIndex($indexKeys)
    {
        $result = array();
        
        if (!empty($indexKeys)) {
            $index = array_shift($indexKeys);
            if ($index == 'site') {
                foreach ($this->sitesId as $idSite) {
                    $result[$idSite] = $this->createEmptyIndex($indexKeys);
                }
            } else if ($index == 'period') {
                foreach ($this->periods as $period => $periodObject) {
                    $result[$period] = $this->createEmptyIndex($indexKeys);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Sets a row in an index by the index keys of the row.
     */
    private function setIndexRow(&$result, $keys, $row)
    {
        $keyCount = count($keys);
        
        if ($keyCount > 1) {
            $firstKey = array_shift($keys);
            $this->setIndexRow($result[$firstKey], $keys, $row);
        } else if ($keyCount == 1) {
            $result[reset($keys)] = $row;
        } else {
            $result = $row;
        }
    }
    
    /**
     * Returns the index keys for a row based on a set of metadata names.
     * 
     * @param array $metadataNames
     * @param array $row
     * @param int $idSite The site ID for the row (needed since site ID is not
     *                    stored as metadata).
     * @param string $period eg, '2012-01-01,2012-01-31'. The period for the
     *                       row (needed since period is not stored as metadata).
     */
    private function getRowKeys($metadataNames, $row, $idSite, $period)
    {
        $result = array();
        foreach ($metadataNames as $name) {
            if ($name == 'site') {
                $result['site'] = $idSite;
            } else if ($name == 'period') {
                $result['period'] = $period;
            } else if (isset($row['_metadata'][$name])) {
                $result[$name] = $row['_metadata'][$name];
            }
        }
        return $result;
    }
}
