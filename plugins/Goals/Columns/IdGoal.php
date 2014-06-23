<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Segment;
use Piwik\Plugin\VisitDimension;

// TODO this is a conversion dimension
class IdGoal extends VisitDimension
{
    protected $fieldName = 'idgoal';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setCategory(Piwik::translate('General_Visit'));
        $segment->setName('General_VisitConvertedGoalId');
        $segment->setSegment('visitConvertedGoalId');
        $segment->setSqlSegment('log_conversion.idgoal');
        $segment->setAcceptedValues('1, 2, 3, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('General_VisitConvertedGoalId');
    }
}