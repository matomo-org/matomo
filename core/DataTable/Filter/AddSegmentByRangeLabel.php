<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
 *     $dataTable->filter('AddSegmentByRangeLabel', array('segmentName'));
 *
 * @api
 */
class AddSegmentByRangeLabel extends BaseFilter
{
    private $segments;
    private $delimiter;
    private $segment;

    /**
     * Generates a segment filter based on the label column and the given segment name
     *
     * @param DataTable $table
     * @param string $segment  one segment
     */
    public function __construct($table, $segment)
    {
        parent::__construct($table);

        $this->segment   = $segment;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        if (empty($this->segment)) {
            $msg = 'AddSegmentByRangeLabel is called without having any segment defined';
            Development::error($msg);
            return;
        }

        foreach ($table->getRowsWithoutSummaryRow() as $key => $row) {
            $label = $row->getColumn('label');

            if (empty($label)) {
                return;
            }

            if ($label === 'General_NewVisits') {
                $row->setMetadata('segment', 'visitorType==new');
                continue;
            }

            // if there's more than one element, handle as a range w/ an upper bound
            if (strpos($label, "-") !== false) {
                // get the range
                sscanf($label, "%d - %d", $lowerBound, $upperBound);

                if ($lowerBound == $upperBound) {
                    $row->setMetadata('segment', $this->segment . '==' . urlencode($lowerBound));
                } else {
                    $row->setMetadata('segment', $this->segment . '>=' . urlencode($lowerBound) . ';' .
                                                              $this->segment . '<=' . urlencode($upperBound));
                }
            } // if there's one element, handle as a range w/ no upper bound
            else {
                // get the lower bound
                sscanf($label, "%d", $lowerBound);

                if ($lowerBound !== null) {
                    $row->setMetadata('segment', $this->segment . '>=' . urlencode($lowerBound));
                }
            }
        }
    }
}
