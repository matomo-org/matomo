<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\Dimension\VisitDimension;

/**
 * Dimension for the log_visit.config_id column. This column is added in the CREATE TABLE
 * statement, so this dimension exists only to configure a segment.
 */
class VisitorFingerprint extends VisitDimension
{
    protected $columnName = 'config_id';
    protected $metricId = 'visitors_fingerprints';
    protected $nameSingular = 'General_VisitorFingerprint';
    protected $namePlural = 'General_Visitors';
    protected $segmentName = 'fingerprint';
    protected $allowAnonymous = false;
    protected $sqlFilterValue = array('Piwik\Common', 'convertVisitorIdToBin');

    public function getAcceptValues()
    {
        return Piwik::translate('General_VisitorFingerprintSegmentHelp', ['1eceaa833348b187', 'API.getLastVisitsDetails']);
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_UNIQUE);
        $metric->setTranslatedName(Piwik::translate('General_VisitorFingerprint'));
        $metricsList->addMetric($metric);
    }
}
