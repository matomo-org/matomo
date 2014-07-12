<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites\Reports;

use Piwik\Piwik;
use Piwik\Plugins\MultiSites\API;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->category = 'General_MultiSitesSummary';

        $metadataMetrics = array();
        foreach (API::getApiMetrics($enhanced = true) as $metricName => $metricSettings) {
            $metadataMetrics[$metricName] =
                Piwik::translate($metricSettings[API::METRIC_TRANSLATION_KEY]);
            $metadataMetrics[$metricSettings[API::METRIC_EVOLUTION_COL_NAME_KEY]] =
                Piwik::translate($metricSettings[API::METRIC_TRANSLATION_KEY]) . " " . Piwik::translate('MultiSites_Evolution');
        }

        $this->metrics = array_keys($metadataMetrics);
    }

}
