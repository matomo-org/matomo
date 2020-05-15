<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables\Columns;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Piwik;
use Piwik\Segment\SegmentsList;

class CustomVariableValue extends Base
{
    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        $this->configureSegmentsFor('Value', $segmentsList, $dimensionSegmentFactory);
    }

    public function getName()
    {
        return Piwik::translate('CustomVariables_ColumnCustomVariableValue');
    }

}