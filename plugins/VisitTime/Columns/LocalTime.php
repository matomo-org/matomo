<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Columns;

use Piwik\Metrics\Formatter;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

require_once PIWIK_INCLUDE_PATH . '/plugins/VisitTime/functions.php';

class LocalTime extends VisitDimension
{
    protected $columnName = 'visitor_localtime';
    protected $columnType = 'TIME NULL';
    protected $type = self::TYPE_TIME;
    protected $segmentName = 'visitLocalHour';
    protected $nameSingular = 'VisitTime_ColumnLocalHour';
    protected $sqlSegment = 'HOUR(log_visit.visitor_localtime)';
    protected $acceptValues = '0, 1, 2, 3, ..., 20, 21, 22, 23';

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\VisitTime\getTimeLabel($value);
    }

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