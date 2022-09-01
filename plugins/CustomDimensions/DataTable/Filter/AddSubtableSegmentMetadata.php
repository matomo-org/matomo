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

class AddSubtableSegmentMetadata extends BaseFilter
{
    private $idDimension;
    private $dimensionValue;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table, $idDimension, $dimensionValue)
    {
        parent::__construct($table);
        $this->idDimension = $idDimension;
        $this->dimensionValue = $dimensionValue;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        if (!$this->dimensionValue) {
            return;
        }

        $dimension = CustomDimensionsRequestProcessor::buildCustomDimensionTrackingApiName($this->idDimension);

        if ($this->dimensionValue === Archiver::LABEL_CUSTOM_VALUE_NOT_DEFINED) {
            $dimensionValue = '';
        } else {
            $dimensionValue = urlencode($this->dimensionValue);
        }

        $conditionAnd  = ';';
        $partDimension = $dimension . '==' . $dimensionValue . $conditionAnd;

        foreach ($table->getRows() as $row) {
            $label = $row->getColumn('label');
            if ($label !== false) {
                $row->setMetadata('segment', $partDimension . 'actionUrl=$' . urlencode($label));
                $row->setMetadata('url', urlencode($label));
            }
        }
    }
}