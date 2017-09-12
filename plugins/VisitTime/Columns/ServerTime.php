<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Columns;

use Piwik\Metrics\Formatter;
use Piwik\Plugin\Dimension\VisitDimension;

require_once PIWIK_INCLUDE_PATH . '/plugins/VisitTime/functions.php';

class ServerTime extends VisitDimension
{
    protected $columnName = 'visit_last_action_time';
    protected $type = self::TYPE_DATETIME;
    protected $segmentName = 'visitServerHour';
    protected $nameSingular = 'VisitTime_ColumnServerHour';
    protected $sqlSegment = 'HOUR(log_visit.visit_last_action_time)';
    protected $acceptValues = '0, 1, 2, 3, ..., 20, 21, 22, 23';

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\VisitTime\getTimeLabel($value);
    }

}