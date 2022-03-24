<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Metrics\Formatter;
use Piwik\Plugin\Dimension\ActionDimension;

class ServerMinute extends ActionDimension
{
    protected $columnName = 'server_time';
    protected $segmentName = 'actionServerMinute';
    protected $sqlSegment = 'MINUTE(log_link_visit_action.server_time)';
    protected $nameSingular = 'VisitTime_ColumnUTCMinute';
    protected $type = self::TYPE_DATETIME;
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
