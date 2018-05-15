<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CustomVariables\Segment;
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
        $segment->setUnionOfSegments($this->getSegmentColumns('customVariable' . $segmentNameSuffix, $numCustomVariables));
        $this->addSegment($segment);

        $segment = new Segment();
        $segment->setType('dimension');
        $segment->setSegment('customVariablePage' . $segmentNameSuffix);
        $segment->setName($this->getName() . ' (' . Piwik::translate('CustomVariables_ScopePage') . ')');
        $segment->setUnionOfSegments($this->getSegmentColumns('customVariablePage' . $segmentNameSuffix, $numCustomVariables));
        $this->addSegment($segment);

        $segmentSuffix = 'v';
        if (strtolower($segmentNameSuffix) === 'name') {
            $segmentSuffix = 'k';
        }

        for ($i = 1; $i <= $numCustomVariables; $i++) {
            $segment = new Segment();
            $segment->setSegment('customVariable' . $segmentNameSuffix . $i);
            $segment->setSqlSegment('log_visit.custom_var_' . $segmentSuffix . $i);
            $segment->setName(Piwik::translate('CustomVariables_ColumnCustomVariable' . $segmentNameSuffix) . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopeVisit') . ')');
            $this->addSegment($segment);

            $segment = new Segment();
            $segment->setSegment('customVariablePage' . $segmentNameSuffix . $i);
            $segment->setSqlSegment('log_link_visit_action.custom_var_' . $segmentSuffix . $i);
            $segment->setName(Piwik::translate('CustomVariables_ColumnCustomVariable' . $segmentNameSuffix) . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopePage') . ')');
            $this->addSegment($segment);
        }
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