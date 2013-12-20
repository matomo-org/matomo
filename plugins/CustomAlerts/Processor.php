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
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;
use Piwik\ScheduledTime;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;

/**
 *
 * @package Piwik_CustomAlerts
 */
class Processor extends \Piwik\Plugin
{

	public function processDailyAlerts()
	{
		$this->processAlerts('day');
	}

	public function processWeeklyAlerts()
	{
		$this->processAlerts('week');
	}

	public function processMonthlyAlerts()
	{
		$this->processAlerts('month');
	}

	public function processAlerts($period)
	{
		$alerts = API::getInstance()->getAllAlerts($period);

		foreach ($alerts as $alert) {
			$report = $alert['report'];
			$metric = $alert['metric'];
			$idSite = $alert['idsite'];
			$idAlert = $alert['idalert'];

			$params = array(
			    "method" => $report,
			    "format" => "original",
			    "idSite" => $idSite,
			    "period" => $period,
			    "date" => Date::today()->subPeriod(1, $period)->toString()
			);

			// Get the data for the API request
			$request = new Piwik\API\Request($params);
			$result  = $request->process();

			$metricOne = $this->getMetricFromTable($result, $alert['metric'], $alert['report_condition'], $alert['report_matched']);

			// Do we have data? Continue otherwise.
			if (is_null($metricOne)) {
				continue;
			}

			// Can we already trigger the alert?
			switch ($alert['metric_condition']) {
				case 'greater_than':
					if ($metricOne > floatval($alert['metric_matched'])) {
						$this->triggerAlert($idAlert, $idSite);
					}
					continue;
					break;
				case 'less_than':
					if ($metricOne < floatval($alert['metric_matched'])) {
						$this->triggerAlert($idAlert, $idSite);
					}
					continue;
					break;
				default:
					break;
			}

			$params['date'] = Date::today()->subPeriod(2, $period)->toString();

			// Get the data for the API request
			$request = new Piwik\API\Request($params);
			$result  = $request->process();

			$metricTwo = $this->getMetricFromTable($result, $alert['metric'], $alert['report_condition'], $alert['report_matched']);

            $percentage = ($metricOne / 100) * $metricTwo;

			switch ($alert['metric_condition']) {
				case 'decrease_more_than':
					if (($metricTwo - $metricOne) > $alert['metric_matched'])
						$this->triggerAlert($idAlert, $idSite);
					break;
				case 'increase_more_than':
					if (($metricOne - $metricTwo) > $alert['metric_matched'])
						$this->triggerAlert($idAlert, $idSite);
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

		//$this->sendNewAlerts($period);
	}

	private function triggerAlert($idAlert, $idSite)
	{
        $db = Db::get();
		$db->insert(
			Common::prefixTable('alert_log'),
			array(
			    'idalert' => $idAlert,
			    'idsite' => $idSite,
			    'ts_triggered' => Date::now()->getDatetime()
			)
		);
	}

	/**
	 * @param DataTable $dataTable DataTable
	 * @param string $metric Metric to fetch from row.
	 * @param string $filterCond Condition to filter for.
	 * @param string $filterValue Value to find
	 */
	private function getMetricFromTable($dataTable, $metric, $filterCond = '', $filterValue = '')
	{
		// Do we have a condition? Then filter..
		if (!empty($filterValue)) {

			$value = $filterValue;

			$invert = false;

			// Some escaping?
			switch ($filterCond) {
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
	 * Sends a list of the triggered alerts to
	 * $recipient.
	 *
	 * @param string $recipient Email address of recipient.
	 */
	private function sendNewAlerts($period)
	{
		$triggeredAlerts = API::getInstance()->getTriggeredAlerts($period, Date::today());

        $alertsPerLogin = array();
		foreach($triggeredAlerts as $triggeredAlert) {
            $login = $triggeredAlert['login'];

            if (!array_key_exists($login, $alertsPerLogin)) {
                $alertsPerLogin[$login] = array();
            }

            $alertsPerLogin[$login][] = $triggeredAlert;
		}

        foreach ($alertsPerLogin as $login => $alerts) {
            $this->sendNewAlertsToLogin($alerts, $login);
        }
	}

    private function sendNewAlertsToLogin($alerts, $login)
    {
        if (empty($login)) {
            return;
        }

        $user = UsersManagerApi::getInstance()->getUser($login);
        if (empty($user) || empty($user['email'])) {
            return;
        }

        // TODO automatically remove alert in case user does no longer exist?

        $recipient = $user['email'];

        $mail = new Piwik\Mail();
        $mail->addTo($recipient);
        $mail->setSubject('Piwik alert [' . Date::today() . ']');

        $viewHtml = new Piwik\View('@CustomAlerts/alertHtmlMail');
        $viewHtml->assign('triggeredAlerts', $this->formatAlerts($alerts, 'html'));
        $mail->setBodyHtml($viewHtml->render());

        $viewText = new Piwik\View('@CustomAlerts/alertTextMail');
        $viewText->assign('triggeredAlerts', $this->formatAlerts($alerts, 'tsv'));
        $viewText->setContentType('text/plain');
        $mail->setBodyText($viewText->render());

        $mail->send();
    }

    /**
     * Returns the Alerts that were triggered in $format.
     *
     * @param array $triggeredAlerts
     * @param string $format Can be 'html', 'tsv' or empty for php array
     * @return array|string
     */
	private function formatAlerts($triggeredAlerts, $format = null)
	{
		switch ($format) {
			case 'html':
				$view = new Piwik\View('@CustomAlerts/htmlTriggeredAlerts');
				$view->triggeredAlerts = $triggeredAlerts;
				return $view->render();
				break;
			case 'tsv':
				$tsv = '';
				$showedTitle = false;
				foreach ($triggeredAlerts as $alert) {
					if (!$showedTitle) {
						$showedTitle = true;
						$tsv .= implode("\t", array_keys($alert)) . "\n";
					}
					$tsv .= implode("\t", array_values($alert)) . "\n";
				}
				return $tsv;
				break;
			default:
				return $triggeredAlerts;
		}
	}

}
?>
