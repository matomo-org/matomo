<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables\Columns;

use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Plugins\CustomVariables\CustomVariables;

class Base extends VisitDimension
{
    protected function configureSegmentsFor($segmentNameSuffix)
    {
        $numCustomVariables = CustomVariables::getNumUsableCustomVariables();

        $segment = new Segment();
        $segment->setType('dimension');
        $segment->setSegment('customVariable' . $segmentNameSuffix);
        $segment->setName($this->getName() . ' (' . Piwik::translate('CustomVariables_ScopeVisit') . ')');
        $segment->setCategory('CustomVariables_CustomVariables');
        $segment->setUnionOfSegments($this->getSegmentColumns('customVariable' . $segmentNameSuffix, $numCustomVariables));
        $this->addSegment($segment);

        $segment = new Segment();
        $segment->setType('dimension');
        $segment->setSegment('customVariablePage' . $segmentNameSuffix);
        $segment->setName($this->getName() . ' (' . Piwik::translate('CustomVariables_ScopePage') . ')');
        $segment->setCategory('CustomVariables_CustomVariables');
        $segment->setUnionOfSegments($this->getSegmentColumns('customVariablePage' . $segmentNameSuffix, $numCustomVariables));
        $this->addSegment($segment);
    }

    private function getSegmentColumns($column, $numCustomVariables)
    {
        $columns = array();
        for ($i = 1; $i <= $numCustomVariables; ++$i) {
            $columns[] = $column . $i;
        }
        return $columns;
    }
}