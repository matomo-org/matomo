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
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Segment;

class IdGoal extends ConversionDimension
{
    protected $columnName = 'idgoal';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setCategory('General_Visit');
        $segment->setName('General_VisitConvertedGoalId');
        $segment->setSegment('visitConvertedGoalId');
        $segment->setAcceptedValues('1, 2, 3, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('General_VisitConvertedGoalId');
    }
}