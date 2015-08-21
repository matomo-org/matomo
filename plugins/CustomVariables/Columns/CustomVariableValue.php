<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables\Columns;

use Piwik\Columns\Dimension;
use Piwik\Piwik;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\CustomVariables\Segment;

class CustomVariableValue extends Dimension
{
    public function getName()
    {
        return Piwik::translate('CustomVariables_ColumnCustomVariableValue');
    }

    protected function configureSegments()
    {
        $maxCustomVariables = CustomVariables::getMaxCustomVariables();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            $segment = new Segment();
            $segment->setSegment('customVariableValue' . $i);
            $segment->setSqlSegment('log_visit.custom_var_v' . $i);
            $segment->setName(Piwik::translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $i
                . ' (' . Piwik::translate('CustomVariables_ScopeVisit') . ')');
            $this->addSegment($segment);

            $segment = new Segment();
            $segment->setSegment('customVariablePageValue' . $i);
            $segment->setSqlSegment('log_link_visit_action.custom_var_v' . $i);
            $segment->setName(Piwik::translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $i
                . ' (' . Piwik::translate('CustomVariables_ScopePage') . ')');
            $this->addSegment($segment);
        }
    }
}