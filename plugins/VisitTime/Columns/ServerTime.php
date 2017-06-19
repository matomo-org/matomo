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

class ServerTime extends VisitDimension
{
    protected $columnName = 'visit_last_action_time';
    protected $type = self::TYPE_DATETIME;
    protected $category = 'General_Visit';
    protected $segmentName = 'visitServerHour';
    protected $nameSingular = 'VisitTime_ColumnServerTime';
    protected $sqlSegment = 'HOUR(log_visit.visit_last_action_time)';
    protected $acceptValues = '0, 1, 2, 3, ..., 20, 21, 22, 23';

}