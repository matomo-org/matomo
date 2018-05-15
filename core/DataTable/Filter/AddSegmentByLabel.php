<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;
use Piwik\Development;

/**
 * Executes a filter for each row of a {@link DataTable} and generates a segment filter for each row.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('AddSegmentByLabel', array('segmentName'));
 *     $dataTable->filter('AddSegmentByLabel', array(array('segmentName1', 'segment2'), ';');
 *
 * @api
 */
class AddSegmentByLabel extends BaseFilter
{
    private $segments;
    private $delimiter;

    /**
     * Generates a segment filter based on the label column and the given segment names
     *
     * @param DataTable $table
     * @param string|array $segmentOrSegments Either one segment or an array of segments.
     *                                        If more than one segment is given a delimter has to be defined.
     * @param string $delimiter               The delimiter by which the label should be splitted.
     */
    public function __construct($table, $segmentOrSegments, $delimiter = '')
    {
        parent::__construct($table);

        if (!is_array($segmentOrSegments)) {
            $segmentOrSegments = array($segmentOrSegments);
        }

        $this->segments  = $segmentOrSegments;
        $this->delimiter = $delimiter;
    }

    /**
     * See {@link AddSegmentByLabel}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        if (empty($this->segments)) {
            $msg = 'AddSegmentByLabel is called without having any segments defined';
            Development::error($msg);
            return;
        }

        if (count($this->segments) === 1) {
            $segment = reset($this->segments);

            foreach ($table->getRowsWithoutSummaryRow() as $key => $row) {
                $label = $row->getColumn('label');

                if (!empty($label)) {
                    $row->setMetadata('segment', $segment . '==' . urlencode($label));
                }
            }
        } elseif (!empty($this->delimiter)) {
            $numSegments  = count($this->segments);
            $conditionAnd = ';';

            foreach ($table->getRowsWithoutSummaryRow() as $key => $row) {
                $label = $row->getColumn('label');
                if (!empty($label)) {
                    $parts = explode($this->delimiter, $label);

                    if (count($parts) === $numSegments) {
                        $filter = array();
                        foreach ($this->segments as $index => $segment) {
                            if (!empty($segment)) {
                                $filter[] = $segment . '==' . urlencode($parts[$index]);
                            }
                        }
                        $row->setMetadata('segment', implode($conditionAnd, $filter));
                    }
                }
            }
        } else {
            $names = implode(', ', $this->segments);
            $msg   = 'Multiple segments are given but no delimiter defined. Segments: ' . $names;
            Development::error($msg);
        }
    }
}
