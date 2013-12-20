<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Alerts
 */

namespace Piwik\Plugins\CustomAlerts;

use Piwik;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;

/**
 *
 * @package Piwik_CustomAlerts
 */
class Processor extends \Piwik\Plugin
{
    public static function getGroupConditions()
    {
        return array(
            'CustomAlerts_MatchesExactly'      => 'matches_exactly',
            'CustomAlerts_DoesNotMatchExactly' => 'does_not_match_exactly',
            'CustomAlerts_MatchesRegularExpression'      => 'matches_regex',
            'CustomAlerts_DoesNotMatchRegularExpression' => 'does_not_match_regex',
            'CustomAlerts_Contains'         => 'contains',
            'CustomAlerts_DoesNotContain'   => 'does_not_contain',
            'CustomAlerts_StartsWith'       => 'starts_with',
            'CustomAlerts_DoesNotStartWith' => 'does_not_start_with',
            'CustomAlerts_EndsWith'         => 'ends_with',
            'CustomAlerts_DoesNotEndWith'   => 'does_not_end_with',
        );
    }

    public static function getMetricConditions()
    {
        return array(
            'CustomAlerts_IsLessThan'    => 'less_than',
            'CustomAlerts_IsGreaterThan' => 'greater_than',
            'CustomAlerts_DecreasesMoreThan' => 'decrease_more_than',
            'CustomAlerts_IncreasesMoreThan' => 'increase_more_than',
            'CustomAlerts_PercentageDecreasesMoreThan' => 'percentage_decrease_more_than',
            'CustomAlerts_PercentageIncreasesMoreThan' => 'percentage_increase_more_than',
        );
    }

	public function processAlerts($period)
	{
		$alerts = API::getInstance()->getAllAlerts($period);

		foreach ($alerts as $alert) {
			$this->processAlert($period, $alert);
		}
	}

    private function processAlert($period, $alert)
    {
        $report  = $alert['report'];
        $metric  = $alert['metric'];
        $idSite  = $alert['idsite'];
        $idAlert = $alert['idalert'];

        $params = array(
            "method" => $report,
            "format" => "original",
            "idSite" => $idSite,
            "period" => $period,
            "date"   => Date::today()->subPeriod(1, $period)->toString()
        );

        // Get the data for the API request
        $request = new Piwik\API\Request($params);
        $result  = $request->process();

        // TODO are we always getting a dataTable?
        $metricOne = $this->getMetricFromTable($result, $metric, $alert['report_condition'], $alert['report_matched']);

        // Do we have data? stop otherwise.
        if (is_null($metricOne)) {
            return;
        }

        // Can we already trigger the alert?
        switch ($alert['metric_condition']) {
            case 'greater_than':
                if ($metricOne > floatval($alert['metric_matched'])) {
                    $this->triggerAlert($idAlert, $idSite);
                }
                return;
            case 'less_than':
                if ($metricOne < floatval($alert['metric_matched'])) {
                    $this->triggerAlert($idAlert, $idSite);
                }
                return;
        }

        $params['date'] = Date::today()->subPeriod(2, $period)->toString();

        // Get the data for the API request
        $request = new Piwik\API\Request($params);
        $result  = $request->process();

        $metricTwo = $this->getMetricFromTable($result, $alert['metric'], $alert['report_condition'], $alert['report_matched']);

        $percentage = ($metricOne / 100) * $metricTwo;

        switch ($alert['metric_condition']) {
            case 'decrease_more_than':
                if (($metricTwo - $metricOne) > $alert['metric_matched']) {
                    $this->triggerAlert($idAlert, $idSite);
                }
                break;
            case 'increase_more_than':
                if (($metricOne - $metricTwo) > $alert['metric_matched']) {
                    $this->triggerAlert($idAlert, $idSite);
                }
                break;
            case 'percentage_decrease_more_than':
                if ($metricOne < $metricTwo && $percentage > $alert['metric_matched']) {
                    $this->triggerAlert($idAlert, $idSite);
                }
                break;
            case 'percentage_increase_more_than':
                if ($metricOne > $metricTwo && $percentage > $alert['metric_matched']) {
                    $this->triggerAlert($idAlert, $idSite);
                }
                break;
        }
    }

	private function triggerAlert($idAlert, $idSite)
	{
        $model = new Model();
        $model->triggerAlert($idAlert, $idSite);
	}

    /**
     * @param DataTable $dataTable DataTable
     * @param string $metric Metric to fetch from row.
     * @param string $filterCond Condition to filter for.
     * @param string $filterValue Value to find
     *
     * @return mixed
     */
	private function getMetricFromTable($dataTable, $metric, $filterCond = '', $filterValue = '')
	{
		// Do we have a condition? Then filter..
		if (!empty($filterValue)) {
            $this->filterDataTable($dataTable, $filterCond, $filterValue);
		}

		if ($dataTable->getRowsCount() > 1) {
			$dataTable->filter('Truncate');
		}

		// ToDo
		//$dataTable->filter('AddColumnsProcessedMetrics');

		$dataRow = $dataTable->getFirstRow();
		
		if ($dataRow) {
			return $dataRow->getColumn($metric);
		}

        return null;
	}

    /**
     * @param $dataTable
     * @param $condition
     * @param $value
     */
    private function filterDataTable($dataTable, $condition, $value)
    {
        $invert = false;

        // Some escaping?
        switch ($condition) {
            case 'matches_exactly':
                $pattern = sprintf("^%s$", $value);
                break;
            case 'matches_regex':
                $pattern = $value;
                break;
            case 'does_not_match_exactly':
                $pattern = sprintf("^%s$", $value);
                $invert = true;
                break;
            case 'does_not_match_regex':
                $pattern = sprintf("%s", $value);
                $invert = true;
                break;
            case 'contains':
                $pattern = $value;
                break;
            case 'does_not_contain':
                $pattern = sprintf("[^%s]", $value);
                $invert = true;
                break;
            case 'starts_with':
                $pattern = sprintf("^%s", $value);
                break;
            case 'does_not_start_with':
                $pattern = sprintf("^%s", $value);
                $invert = true;
                break;
            case 'ends_with':
                $pattern = sprintf("%s$", $value);
                break;
            case 'does_not_end_with':
                $pattern = sprintf("%s$", $value);
                $invert = true;
                break;
        }

        $dataTable->filter('Pattern', array('label', $pattern, $invert));
    }

}
?>
