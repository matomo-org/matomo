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
use Piwik\Date;
use Piwik\Db;
use Exception;
use Piwik\Menu\MenuTop;
use Piwik\ScheduledTask;
use Piwik\ScheduledTime;

/**
 *
 * @package Piwik_Alerts
 */
class CustomAlerts extends \Piwik\Plugin
{

	public function getListHooksRegistered()
	{
		return array(
		    'Menu.Top.addItems' => 'addTopMenu',
		    'TaskScheduler.getScheduledTasks' => 'getScheduledTasks',
		    'AssetManager.getJavaScriptFiles' => 'getJsFiles',
		    'AssetManager.getStylesheetFiles' => 'getCssFiles',
		);
	}

	public function getJsFiles(&$jsFiles)
	{
		$jsFiles[] = "plugins/CustomAlerts/javascripts/ui.dropdownchecklist.js";
	}

	public function getCssFiles(&$cssFiles)
	{
		$cssFiles[] = "plugins/CustomAlerts/stylesheets/ui.dropdownchecklist.css";
	}

	public function install()
	{
		$tableAlert = "CREATE TABLE " . Common::prefixTable('alert') . " (
			`idalert` INT NOT NULL PRIMARY KEY ,
			`name` VARCHAR(100) NOT NULL ,
			`login` VARCHAR(100) NOT NULL ,
			`period` VARCHAR(5) NOT NULL ,
			`report` VARCHAR(150) NOT NULL ,
			`report_condition` VARCHAR(50) ,
			`report_matched` VARCHAR(255) ,
			`metric` VARCHAR(150) NOT NULL ,
			`metric_condition` VARCHAR(50) NOT NULL ,
			`metric_matched` FLOAT NOT NULL ,
			`enable_mail` BOOLEAN NOT NULL ,
			`deleted` BOOLEAN NOT NULL
		) DEFAULT CHARSET=utf8 ;";

		$tableAlertSite = "CREATE TABLE " . Common::prefixTable('alert_site') . "(
			`idalert` INT( 11 ) NOT NULL ,
			`idsite` INT( 11 ) NOT NULL ,
			PRIMARY KEY ( idalert, idsite )
		) DEFAULT CHARSET=utf8 ;";

		$tableAlertLog = "CREATE TABLE " . Common::prefixTable('alert_log') . " (
			`idalert` INT( 11 ) NOT NULL ,
			`idsite` INT( 11 ) NOT NULL ,
			`ts_triggered` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			KEY `ts_triggered` (`ts_triggered`)
		)";

		try {
			Db::exec($tableAlert);
            Db::exec($tableAlertLog);
            Db::exec($tableAlertSite);
		} catch (Exception $e) {
			// mysql code error 1050:table already exists
			// see bug #153 http://dev.piwik.org/trac/ticket/153
			if (!Db::get()->isErrNo($e, '1050')) {
				throw $e;
			}
		}
	}

	public function uninstall()
	{
		$tables = array('alert', 'alert_log', 'alert_site');
		foreach ($tables as $table) {
			$sql = "DROP TABLE " . Common::prefixTable($table);
			Db::exec($sql);
		}
	}

	public function addTopMenu()
	{
        MenuTop::addEntry("Alerts", array("module" => "CustomAlerts", "action" => "index"), true, 9);
	}

	public function getScheduledTasks(&$tasks)
	{
		$tasks[] = new ScheduledTask(
				__CLASS__,
				'processDailyAlerts',
                null,
				ScheduledTime::factory('daily')
		);

		$tasks[] = new ScheduledTask(
				__CLASS__,
				'processWeeklyAlerts',
                null,
                ScheduledTime::factory('weekly')
		);

		$tasks[] = new ScheduledTask(
				__CLASS__,
				'processMonthlyAlerts',
                null,
                ScheduledTime::factory('monthly')
		);
	}

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
			$result = $request->process();

			$metric_one = $this->getMetricFromTable($result, $alert['metric'], $alert['report_condition'], $alert['report_matched']);

			// Do we have data? Continue otherwise.
			if (is_null($metric_one)) {
				continue;
			}

			// Can we already trigger the alert?
			switch ($alert['metric_condition']) {
				case 'greater_than':
					if ($metric_one > floatval($alert['metric_matched'])) {						
						$this->triggerAlert($idAlert, $idSite);
					}
					continue;
					break;
				case 'less_than':
					if ($metric_one < floatval($alert['metric_matched'])) {
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
			$result = $request->process();

			$metric_two = $this->getMetricFromTable($result, $alert['metric'], $alert['report_condition'], $alert['report_matched']);

			switch ($alert['metric_condition']) {
				case 'decrease_more_than':
					if (($metric_two - $metric_one) > $alert['metric_matched'])
						$this->triggerAlert($idAlert, $idSite);
					break;
				case 'increase_more_than':
					if (($metric_one - $metric_two) > $alert['metric_matched'])
						$this->triggerAlert($idAlert, $idSite);
					break;
				case 'percentage_decrease_more_than':
					// ToDo
					break;
				case 'percentage_increase_more_than':
					// ToDo
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
	 *
	 * @param array $dataTable DataTable
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
		} else {
			return null;
		}
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

		foreach($triggeredAlerts as $triggeredAlert) {
			// collect $triggered[$login] = array(of Alerts)
		}
		
		$mail = new Piwik\Mail();
		$mail->addTo($recipient);
		$mail->setSubject('Piwik alert [' . Date::today() . ']');		

		$viewHtml = new Piwik\View('@CustomAlerts/alertHtmlMail');
		$viewHtml->assign('triggeredAlerts', $this->getTriggeredAlerts('html'));
		$mail->setBodyHtml($viewHtml->render());

		$viewText = new Piwik\View('@CustomAlerts/alertTextMail');
		$viewText->assign('triggeredAlerts', $this->getTriggeredAlerts('tsv'));
		$viewText->setContentType('text/plain');
		$mail->setBodyText($viewText->render());

		$mail->send();
	}

	/**
	 * Returns the Alerts that were triggered in $format.
	 *
	 * @param string $format Can be 'html', 'tsv' or empty for php array
	 */
	private function getTriggeredAlerts($format = null)
	{
		switch ($format) {
			case 'html':
				$view = new Piwik\View('@CustomAlerts/htmlTriggeredAlerts');
				$view->triggeredAlerts = $this->triggeredAlerts;
				return $view->render();
				break;
			case 'tsv':
				$tsv = '';
				$showedTitle = false;
				foreach ($this->triggeredAlerts as $alert) {
					if (!$showedTitle) {
						$showedTitle = true;
						$tsv .= implode("\t", array_keys($alert)) . "\n";
					}
					$tsv .= implode("\t", array_values($alert)) . "\n";
				}
				return $tsv;
				break;
			default:
				return $this->triggeredAlerts;
		}
	}

}
?>
