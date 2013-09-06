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

use Piwik\DataTable;
use Piwik\DataTable\Filter\CalculateEvolutionFilter;

/**
 * A utility class that generates JSON data meant to be used with the JavaScript
 * Infovis Toolkit's treemap visualization.
 */
class TreemapDataGenerator
{
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
     * The row offset to apply an additional truncation to (the first truncation occurs in
     * DataTableGenericFilter).
     * 
     * @var int
     */
    private $truncateAfter = false;

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
     * Sets the row offset to apply additional truncation after.
     * 
     * @param int $truncateAfter
     */
    public function setTruncateAfter($truncateAfter)
    {
        $this->truncateAfter = $truncateAfter;
    }

    /**
     * Generates an array that can be encoded as JSON and used w/ the JavaScript Infovis Toolkit.
     * 
     * @param \Piwik\DataTable $dataTable
     * @return array
     */
    public function generate($dataTable)
    {
        // handle extra truncation
        if ($this->truncateAfter) {
            $dataTable->filter('Truncate', array($this->truncateAfter));
        }

        // if showEvolutionValues is true, $dataTable must be a DataTable\Map w/ two child tables
        $pastData = false;
        if ($this->showEvolutionValues) {
            list($pastData, $dataTable) = array_values($dataTable->getArray());
        }

        $root = $this->makeNode('treemap-root', $this->rootName);
        $this->addDataTableToNode($root, $dataTable, $pastData, $tableId = '', $this->firstRowOffset);
        return $root;
    }

    private function addDataTableToNode(&$node, $dataTable, $pastData = false, $tableId = '', $offset = 0)
    {
        foreach ($dataTable->getRows() as $rowId => $row) {
            $pastRow = $pastData ? $pastData->getRowFromLabel($row->getColumn('label')) : false;

            $childNode = $this->makeNodeFromRow($tableId, $rowId, $row, $pastRow);

            if ($rowId == DataTable::ID_SUMMARY_ROW) {
                $childNode['data']['aggregate_offset'] = $offset + $dataTable->getRowsCount() - 1;
            } else if ($row->getIdSubDataTable() !== null) {
                $this->addSubtableToNode($childNode, $row, $pastRow);
            }

            $node['children'][] = $childNode;
        }
    }

    private function makeNodeFromRow($tableId, $rowId, $row, $pastRow)
    {
        $label = $row->getColumn('label');
        $columnValue = $row->getColumn($this->metricToGraph) ?: 0;

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
        $data['metadata']['tooltip'] = ' ' . $columnValue . ' ' . $this->metricTranslation;
        if (isset($data['evolution'])) {
            $greaterOrLess = $data['evolution'] > 0 ? '>' : '<';
            $data['metadata']['tooltip'] .= ' ' . $greaterOrLess . ' ' . abs($data['evolution']) . '%';
        }

        return $this->makeNode($this->getNodeId($tableId, $rowId), $label, $data);
    }

    private function addSubtableToNode(&$childNode, $subTableRow, $pastRow)
    {
        $childNode['data']['idSubtable'] = $subTableRow->getIdSubDataTable();
        $childNode['data']['loaded'] = 1;

        $subTable = $subTableRow->getSubtable();
        $subTable->filter('AddSummaryRow', array(4, Piwik_Translate('General_Others'), $columnToSort = $this->metricToGraph)); //TODO: make constants customizable

        $pastSubtable = false;
        if ($pastRow) {
            $pastSubtable = $pastRow->getSubtable();
        }

        $this->addDataTableToNode($childNode, $subTable, $pastSubtable, $subTableRow->getIdSubDataTable());
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