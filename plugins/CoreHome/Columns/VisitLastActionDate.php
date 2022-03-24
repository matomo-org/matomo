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
use Piwik\Date;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Metrics\Formatter;

class VisitLastActionDate extends VisitDimension
{
    protected $columnName = 'visit_last_action_time';
    protected $type = self::TYPE_DATETIME;
    protected $segmentName = 'visitEndServerDate';
    protected $nameSingular = 'VisitTime_ColumnVisitEndUTCDate';
    protected $sqlSegment = 'DATE(log_visit.visit_last_action_time)';
    protected $acceptValues = '2018-12-31, 2018-03-20, ...';

    public function __construct()
    {
        $this->suggestedValuesCallback = function ($idSite, $maxValuesToReturn) {
            if (defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE) {
                $date = Date::factory('2018-02-02');
            } else {
                $date = Date::now();
            }
            $return = array($date->toString());
            for ($i = 0; $i < $maxValuesToReturn; $i++) {
                $date = $date->subDay(1);
                $return[] = $date->toString();
            }
            return $return;
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