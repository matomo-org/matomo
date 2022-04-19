<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Date;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Metrics\Formatter;

require_once PIWIK_INCLUDE_PATH . '/plugins/VisitTime/functions.php';

class VisitFirstActionTime extends VisitDimension
{
    protected $columnName = 'visit_first_action_time';
    protected $columnType = 'DATETIME NOT NULL';
    protected $type = self::TYPE_DATETIME;

    protected $sqlSegment = 'HOUR(log_visit.visit_first_action_time)';
    protected $segmentName = 'visitStartServerHour';
    protected $acceptValues = '0, 1, 2, 3, ..., 20, 21, 22, 23';
    protected $nameSingular = 'VisitTime_ColumnVisitStartSiteHour';

    public function __construct()
    {
        $this->suggestedValuesCallback = function ($idSite, $maxValuesToReturn) {
            return range(0, min(23, $maxValuesToReturn));
        };
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        $hourInTz = VisitLastActionTime::convertHourToHourInSiteTimezone($value, $idSite);
        return \Piwik\Plugins\VisitTime\getTimeLabel($hourInTz);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return Date::getDatetimeFromTimestamp($request->getCurrentTimestamp());
    }
}