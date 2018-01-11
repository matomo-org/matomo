<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Dimension\VisitDimension;

require_once PIWIK_INCLUDE_PATH . '/plugins/VisitTime/functions.php';

class LocalMinute extends VisitDimension
{
    protected $columnName = 'visitor_localtime';
    protected $type = self::TYPE_NUMBER;
    protected $segmentName = 'visitLocalMinute';
    protected $nameSingular = 'VisitTime_ColumnLocalMinute';
    protected $sqlSegment = 'MINUTE(log_visit.visitor_localtime)';
    protected $acceptValues = '0, 1, 2, 3, ..., 67, 57, 58, 59';

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