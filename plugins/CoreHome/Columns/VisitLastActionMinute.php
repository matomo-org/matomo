<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Metrics\Formatter;

require_once PIWIK_INCLUDE_PATH . '/plugins/VisitTime/functions.php';

/**
 * This dimension holds visit's last action time in a visit.
 *
 * Note: though it is named 'visit end time time' it actually refers to the visit's last action.
 */
class VisitLastActionMinute extends VisitDimension
{
    protected $columnName = 'visit_last_action_time';
    protected $type = self::TYPE_DATETIME;
    protected $segmentName = 'visitEndServerMinute';
    protected $nameSingular = 'VisitTime_ColumnVisitEndUTCMinute';
    protected $sqlSegment = 'MINUTE(log_visit.visit_last_action_time)';
    protected $acceptValues = '0, 1, 2, 3, ..., 56, 57, 58, 59';

    public function __construct()
    {
        $this->suggestedValuesCallback = function ($idSite, $maxValuesToReturn) {
            return range(0, min(59, $maxValuesToReturn));
        };
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        // no metrics for this dimension
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return $value;
    }
}