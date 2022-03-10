<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomDimensions\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Plugins\CustomDimensions\Archiver;
use Piwik\Plugins\CustomDimensions\Tracker\CustomDimensionsRequestProcessor;

class AddSegmentMetadata extends BaseFilter
{
    private $idDimension;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table, $idDimension)
    {
        parent::__construct($table);
        $this->idDimension = $idDimension;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $dimension = CustomDimensionsRequestProcessor::buildCustomDimensionTrackingApiName($this->idDimension);

        foreach ($table->getRows() as $row) {
            $label = $row->getColumn('label');
            if ($label !== false) {
                if ($label === Archiver::LABEL_CUSTOM_VALUE_NOT_DEFINED) {
                    $label = '';
                }
                $row->setMetadata('segment', $dimension . '==' . urlencode($label));
            }

            $subTable = $row->getSubtable();
            if ($subTable) {
                $subTable->filter('Piwik\Plugins\CustomDimensions\DataTable\Filter\AddSubtableSegmentMetadata', array($this->idDimension, $label));
            }
        }
    }
}