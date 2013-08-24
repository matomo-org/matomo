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

/**
 * A utility class that generates JSON data meant to be used with the JavaScript
 * Infovis Toolkit's treemap visualization.
 */
class TreemapDataGenerator
{
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
     * Constructor.
     * 
     * @param string $metricToGraph @see self::$metricToGraph
     */
    public function __construct($metricToGraph)
    {
        $this->metricToGraph = $metricToGraph;
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
     * Generates an array that can be encoded as JSON and used w/ the JavaScript Infovis Toolkit.
     * 
     * @param Piwik\DataTable $dataTable
     * @return array
     */
    public function generate($dataTable)
    {
        $root = $this->makeNode('treemap-root', $this->rootName);
        $this->addDataTableToNode($root, $dataTable, $tableId = '', $this->firstRowOffset);
        return $root;
    }

    private function addDataTableToNode(&$node, $dataTable, $tableId = '', $offset = 0)
    {
        foreach ($dataTable->getRows() as $rowId => $row) {
            $id = $this->getNodeId($tableId, $rowId);
            
            $columnValue = $row->getColumn($this->metricToGraph) ?: 0;
            $childNode = $this->makeNode($id, $row->getColumn('label'), $data = array('$area' => $columnValue));

            if ($rowId == DataTable::ID_SUMMARY_ROW) {
                $childNode['data']['aggregate_offset'] = $offset + $dataTable->getRowsCount() - 1;
            } else if ($row->getIdSubDataTable() !== null) {
                $this->addSubtableToNode($childNode, $row);
            }

            $node['children'][] = $childNode;
        }
    }

    private function addSubtableToNode(&$childNode, $subTableRow)
    {
        $childNode['data']['idSubtable'] = $subTableRow->getIdSubDataTable();
        $childNode['data']['loaded'] = 1;

        $subTable = $subTableRow->getSubtable();
        $subTable->filter('AddSummaryRow', array(4, Piwik_Translate('General_Others'), $columnToSort = $this->metricToGraph)); //TODO: make constants customizable

        $this->addDataTableToNode($childNode, $subTable, $subTableRow->getIdSubDataTable());
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