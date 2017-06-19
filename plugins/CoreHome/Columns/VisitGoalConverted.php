<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitGoalConverted extends VisitDimension
{
    protected $columnName = 'visit_goal_converted';
    protected $columnType = 'TINYINT(1) NULL';
    protected $type = self::TYPE_BOOL;
    protected $segmentName = 'visitConverted';
    protected $nameSingular = 'General_VisitConvertedGoal';
    protected $acceptValues = '0, 1';
    protected $category = 'General_Visit';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return 0;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        return 1;
    }
}