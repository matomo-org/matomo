<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;

/**
 * Converts for each row of a {@link DataTable} a segmentValue to a segment (expression). The name of the segment
 * is automatically detected based on the given report.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('AddSegmentBySegmentValue', array($reportInstance));
 *
 * @api
 */
class AddSegmentBySegmentValue extends BaseFilter
{
    /**
     * @var \Piwik\Plugin\Report
     */
    private $report;

    /**
     * @param DataTable $table
     * @param $report
     */
    public function __construct($table, $report)
    {
        parent::__construct($table);
        $this->report = $report;
    }

    /**
     * See {@link AddSegmentBySegmentValue}.
     *
     * @param DataTable $table
     * @return int The number of deleted rows.
     */
    public function filter($table)
    {
        if (empty($this->report) || !$table->getRowsCount()) {
            return;
        }

        $dimension = $this->report->getDimension();

        if (empty($dimension)) {
            return;
        }

        $segments = $dimension->getSegments();

        if (empty($segments)) {
            return;
        }

        /** @var \Piwik\Plugin\Segment $segment */
        $segment     = reset($segments);
        $segmentName = $segment->getSegment();

        foreach ($table->getRows() as $row) {
            $value  = $row->getMetadata('segmentValue');
            $filter = $row->getMetadata('segment');

            if ($value !== false && $filter === false) {
                $row->setMetadata('segment', sprintf('%s==%s', $segmentName, urlencode($value)));
            }
        }
    }
}
