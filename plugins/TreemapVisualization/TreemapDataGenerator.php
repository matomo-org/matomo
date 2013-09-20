<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package TreemapVisualization
 */
namespace Piwik\Plugins\TreemapVisualization;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Map;
use Piwik\DataTable\Filter\CalculateEvolutionFilter;

/**
 * A utility class that generates JSON data meant to be used with the JavaScript
 * Infovis Toolkit's treemap visualization.
 */
class TreemapDataGenerator
{
    const DEFAULT_MAX_ELEMENTS = 10;
    const MIN_NODE_AREA = 400; // 20px * 20px

    /**
     * The list of row metadata that should appear in treemap JSON data, if in the row.
     * 
     * @var array
     */
    private static $rowMetadataToCopy = array('logo', 'url');

    /**
     * The name of the root node.
     * 
     * @var string
     */
    private $rootName = '';

    /**
     * The offset of the first row in the DataTable. When exploring aggregate rows (ie, the
     * 'Others' row), the DataTable used won't have the initial rows, so the row offsets
     * aren't the same as the row IDs. In order to make sure each node has a unique ID,
     * we need to to know the actual row offset of each row.
     * 
     * @var int
     */
    private $firstRowOffset = 0;

    /**
     * The name of the metric to generate a treemap for.
     * 
     * @var string
     */
    private $metricToGraph;

    /**
     * The internationalized label of the metric to graph. Used in the tooltip of each node.
     * 
     * @var string
     */
    private $metricTranslation;

    /**
     * Whether to include evolution values in the output JSON.
     * 
     * @var bool
     */
    private $showEvolutionValues = false;

    /**
     * Holds the date of the past period. Implementation detail.
     * 
     * @var string
     */
    private $pastDataDate = null;

    /**
     * Holds the available screen width in pixels for the treemap.
     * 
     * @var int
     */
    private $availableWidth = false;

    /**
     * Holds the available screen height in pixels for the treemap.
     * 
     * @var int
     */
    private $availableHeight = false;

    /**
     * Constructor.
     *
     * @param string $metricToGraph @see self::$metricToGraph
     * @param string $metricTranslation
     */
    public function __construct($metricToGraph, $metricTranslation)
    {
        $this->metricToGraph = $metricToGraph;
        $this->metricTranslation = $metricTranslation;
    }

    /**
     * Sets the name of the root node.
     * 
     * @param string $name
     */
    public function setRootNodeName($name)
    {
        $this->rootName = $name;
    }

    /**
     * Sets the offset of the first row in the converted DataTable.
     * 
     * @param int $offset
     */
    public function setInitialRowOffset($offset)
    {
        $this->firstRowOffset = (int)$offset;
    }

    /**
     * Configures the generator to calculate the evolution of column values and include
     * this data in the outputted tree structure.
     */
    public function showEvolutionValues()
    {
        $this->showEvolutionValues = true;
    }

    /**
     * Sets the available screen width & height for this treemap.
     * 
     * @param int $availableWidth
     * @param int $availableHeight
     */
    public function setAvailableDimensions($availableWidth, $availableHeight)
    {
        $this->availableWidth = $availableWidth;
        $this->availableHeight = $availableHeight;
    }

    /**
     * Generates an array that can be encoded as JSON and used w/ the JavaScript Infovis Toolkit.
     * 
     * @param \Piwik\DataTable $dataTable
     * @return array
     */
    public function generate($dataTable)
    {
        // sanity check: if the dataTable is not a Map, we don't have the data to calculate evolution
        // values, so make sure we don't try
        if (!($dataTable instanceof Map)) {
            $this->showEvolutionValues = false;
        }

        // if showEvolutionValues is true, $dataTable must be a DataTable\Map w/ two child tables
        $pastData = false;
        if ($this->showEvolutionValues) {
            list($pastData, $dataTable) = array_values($dataTable->getArray());
            $this->pastDataDate = $pastData->getMetadata('period')->getLocalizedShortString();
        }

        // handle extra truncation (only for current data)
        $truncateAfter = $this->getDynamicMaxElementCount($dataTable);
        if ($truncateAfter > 0) {
            $dataTable->filter('Truncate', array($truncateAfter));
        }

        $tableId = Common::getRequestVar('idSubtable', '');

        $root = $this->makeNode('treemap-root', $this->rootName);
        $this->addDataTableToNode($root, $dataTable, $pastData, $tableId, $this->firstRowOffset);
        return $root;
    }

    private function getDynamicMaxElementCount($dataTable)
    {
        if (!is_numeric($this->availableWidth)
            || !is_numeric($this->availableHeight)
        ) {
            return self::DEFAULT_MAX_ELEMENTS - 1;
        } else {
            $totalArea = $this->availableWidth * $this->availableHeight;

            $dataTable->filter('ReplaceColumnNames');

            $metricValues = $dataTable->getColumn($this->metricToGraph);
            $metricSum = array_sum($metricValues);

            // find the row index in $dataTable for which all rows after it will have treemap
            // nodes that are too small. this is the row from which we truncate.
            // Note: $dataTable is sorted at this point, so $metricValues is too
            $result = 0;
            foreach ($metricValues as $value) {
                $nodeArea = ($totalArea * $value) / $metricSum;

                if ($nodeArea < self::MIN_NODE_AREA) {
                    break;
                } else {
                    ++$result;
                }
            }
            return $result;
        }
    }

    private function addDataTableToNode(&$node, $dataTable, $pastData = false, $tableId = '', $offset = 0)
    {
        foreach ($dataTable->getRows() as $rowId => $row) {
            $pastRow = $pastData ? $pastData->getRowFromLabel($row->getColumn('label')) : false;

            $childNode = $this->makeNodeFromRow($tableId, $rowId, $row, $pastRow);
            if (empty($childNode)) {
                continue;
            }

            if ($rowId == DataTable::ID_SUMMARY_ROW) {
                $childNode['data']['aggregate_offset'] = $offset + $dataTable->getRowsCount() - 1;
            } else if ($row->getIdSubDataTable() !== null) {
                $childNode['data']['idSubtable'] = $row->getIdSubDataTable();
            }

            $node['children'][] = $childNode;
        }
    }

    private function makeNodeFromRow($tableId, $rowId, $row, $pastRow)
    {
        $label = $row->getColumn('label');
        $columnValue = $row->getColumn($this->metricToGraph) ?: 0;

        if ($columnValue == 0) { // avoid issues in JIT w/ 0 $area values
            return false;
        }

        $data = array();
        $data['$area'] = $columnValue;

        // add metadata
        foreach (self::$rowMetadataToCopy as $metadataName) {
            $metadataValue = $row->getMetadata($metadataName);
            if ($metadataValue !== false) {
                $data['metadata'][$metadataName] = $metadataValue;
            }
        }

        // add evolution
        if ($rowId !== DataTable::ID_SUMMARY_ROW
            && $this->showEvolutionValues
        ) {
            if ($pastRow === false) {
                $data['evolution'] = 100;
            } else {
                $pastValue = $pastRow->getColumn($this->metricToGraph) ?: 0;
                $data['evolution'] = CalculateEvolutionFilter::calculate(
                    $columnValue, $pastValue, $quotientPrecision = 0, $appendPercentSign = false);
            }
        }

        // add node tooltip
        $data['metadata']['tooltip'] = "\n" . $columnValue . ' ' . $this->metricTranslation;
        if (isset($data['evolution'])) {
            $plusOrMinus = $data['evolution'] >= 0 ? '+' : '-';
            $evolutionChange = $plusOrMinus . abs($data['evolution']) . '%';

            $data['metadata']['tooltip'] = Piwik_Translate('General_XComparedToY', array(
                $data['metadata']['tooltip'] . "\n" . $evolutionChange,
                $this->pastDataDate
            ));
        }

        return $this->makeNode($this->getNodeId($tableId, $rowId), $label, $data);
    }

    private function getNodeId($tableId, $rowId)
    {
        if ($rowId == DataTable::ID_SUMMARY_ROW) {
            $rowId = $this->firstRowOffset . '_' . $rowId;
        } else {
            $rowId = $this->firstRowOffset += $rowId;
        }

        return $tableId . '_' . $rowId;
    }

    private function makeNode($id, $title, $data = array())
    {
        return array('id' => $id, 'name' => $title, 'data' => $data, 'children' => array());
    }
}