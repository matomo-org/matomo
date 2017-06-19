<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class LocalTime extends VisitDimension
{
    protected $columnName = 'visitor_localtime';
    protected $columnType = 'TIME NULL';
    protected $type = self::TYPE_TIME;
    protected $segmentName = 'visitLocalHour';
    protected $nameSingular = 'VisitTime_ColumnLocalTime';
    protected $sqlSegment = 'HOUR(log_visit.visitor_localtime)';
    protected $acceptValues = '0, 1, 2, 3, ..., 20, 21, 22, 23';
    protected $category = 'General_Visit';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return $request->getLocalTime();
    }
}