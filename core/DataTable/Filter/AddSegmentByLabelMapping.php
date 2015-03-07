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

/**
 * Executes a filter for each row of a {@link DataTable} and generates a segment filter for each row.
 * It will map the label column to a segmentValue by searching for the label in the index of the given
 * mapping array.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('AddSegmentByLabelMapping', array('segmentName', array('1' => 'smartphone, '2' => 'desktop')));
 *
 * @api
 */
class AddSegmentByLabelMapping extends BaseFilter
{
    private $segment;
    private $mapping;

    /**
     * @param DataTable $table
     * @param string $segment
     * @param array $mapping
     */
    public function __construct($table, $segment, $mapping)
    {
        parent::__construct($table);

        $this->segment = $segment;
        $this->mapping = $mapping;
    }

    /**
     * See {@link AddSegmentByLabelMapping}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        if (empty($this->segment) || empty($this->mapping)) {
            return;
        }

        foreach ($table->getRows() as $row) {
            $label = $row->getColumn('label');

            if (!empty($this->mapping[$label])) {
                $label = $this->mapping[$label];
                $row->setMetadata('segment', $this->segment . '==' . urlencode($label));
            }
        }
    }
}
