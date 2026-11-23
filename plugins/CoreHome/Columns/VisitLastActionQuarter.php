<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Db\Schema;
use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Metrics\Formatter;

class VisitLastActionQuarter extends VisitDimension
{
    protected $columnName = 'visit_last_action_time';
    protected $type = self::TYPE_DATETIME;
    protected $segmentName = 'visitEndServerQuarter';
    protected $nameSingular = 'VisitTime_ColumnVisitEndUTCQuarter';
    protected $sqlSegment = 'QUARTER(log_visit.visit_last_action_time)';
    protected $acceptValues = '1, 2, 3, 4';

    public function getSqlSegment()
    {
        // the declared MySQL expression, unless a subclass or setSqlSegment() replaced it, is spelled by the database in use
        if ($this->sqlSegment === 'QUARTER(log_visit.visit_last_action_time)') {
            return Schema::getInstance()->getDatePartSql('QUARTER', 'log_visit.visit_last_action_time');
        }

        return parent::getSqlSegment();
    }

    public function __construct()
    {
        $this->suggestedValuesCallback = function ($idSite, $maxValuesToReturn) {
            return range(1, min(4, $maxValuesToReturn));
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
