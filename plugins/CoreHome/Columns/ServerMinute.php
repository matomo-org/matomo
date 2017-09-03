<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Date;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class ServerMinute extends ActionDimension
{
    protected $columnName = 'server_time';
    protected $segmentName = 'actionServerMinute';
    protected $sqlSegment = 'MINUTE(log_link_visit_action.server_time)';
    protected $nameSingular = 'VisitTime_ColumnServerMinute';
    protected $type = self::TYPE_DATETIME;

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return $value;
    }

}
