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

class ServerMinute extends VisitDimension
{
    protected $columnName = 'visit_last_action_time';
    protected $type = self::TYPE_DATETIME;
    protected $segmentName = 'visitServerMinute';
    protected $nameSingular = 'VisitTime_ColumnServerMinute';
    protected $sqlSegment = 'MINUTE(log_visit.visit_last_action_time)';
    protected $acceptValues = '0, 1, 2, 3, ..., 56, 57, 58, 59';

    public function __construct()
    {
        $this->suggestedValuesCallback = function ($idSite, $maxValuesToReturn) {
            return range(0, min(59, $maxValuesToReturn));
        };
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return $value;
    }

}