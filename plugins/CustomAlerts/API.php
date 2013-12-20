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

use Exception;
use Piwik\Piwik;
use Piwik\Common;
use Piwik\Date;
use Piwik\Period;
use Piwik\Db;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;
use Piwik\Plugins\API\API as MetadataApi;

/**
 *
 * @package Piwik_Alerts
 */
class API extends \Piwik\Plugin\API
{

	/**
	 * Returns a single Alert
	 *
	 * @param int $idAlert
	 */
	public function getAlert($idAlert)
	{
		$alert = Db::fetchAll("SELECT * FROM "
						. Common::prefixTable('alert')
						. " WHERE idalert = ?",
						intval($idAlert)
		);

		if (!$alert) {
			throw new Exception(Piwik::translate('CustomAlerts_AlertDoesNotExist', $idAlert));
		}

		$alert = $alert[0];

		if (!Piwik::isUserIsSuperUserOrTheUser($alert['login'])) {
			throw new Exception(Piwik::translate('CustomAlerts_AccessException', $idAlert));
		}

//		$idSites = Piwik_FetchAll("SELECT * FROM "
//				. Common::prefixTable('alert_site')
//				. " WHERE idalert = ?",
//				intval($idAlert)
//		);
//
//		$alert['idsites'] = $idSites;

		return $alert;
	}

	/**
	 * Returns the Alerts that are defined on the idSites given.
	 * If no value is given, all Alerts for the current user will
	 * be returned.
	 *
	 * @param array $idSites
	 */
	public function getAlerts($idSites = array())
	{
		if (count($idSites)) {
			Piwik::checkUserHasViewAccess($idSites);
		} else {
			Piwik::checkUserHasSomeViewAccess();
			$idSites = SitesManagerApi::getInstance()->getSitesIdWithAtLeastViewAccess();
		}

		$alerts = Db::fetchAll(("SELECT * FROM "
						. Common::prefixTable('alert')
						. " WHERE idalert IN (
					 SELECT pas.idalert FROM " . Common::prefixTable('alert_site')
						. "  pas WHERE idsite IN (" . implode(",", $idSites) . ")) "
						. "AND deleted = 0"
		));

		return $alerts;
	}

	public function getTriggeredAlerts($period, $date, $login = false)
	{
		Piwik::checkUserIsSuperUserOrTheUser($login);

		$this->checkPeriod($period);
		$piwikDate = Date::factory($date);
		$date = Period::factory($period, $piwikDate);

        $db = Db::get();

		$sql = "SELECT pa.idalert AS idalert,
				pal.idsite AS idsite,
				pa.name    AS alert_name,
				ps.name    AS site_name,
				login,
				period,
				report,
				report_condition,
				report_matched,
				metric,
				metric_condition,
				metric_matched
			FROM   ". Common::prefixTable('alert_log') ." pal
				JOIN ". Common::prefixTable('alert') ." pa
				ON pal.idalert = pa.idalert
				JOIN ". Common::prefixTable('site') ." ps
				ON pal.idsite = ps.idsite
			WHERE  period = ?
				AND ts_triggered BETWEEN ? AND ?";

		if ($login !== false) {
			$sql .= " AND login = \"" . $login . "\"";
		}

		return $db->fetchAll($sql, array(
			$period,
			$date->getDateStart()->getDateStartUTC(),
			$date->getDateEnd()->getDateEndUTC())
		);

	}

	public function getAllAlerts($period)
	{
		Piwik::checkUserIsSuperUser();

		$sql = "SELECT * FROM "
				. Common::prefixTable('alert_site') . " alert, "
				. Common::prefixTable('alert') . " alert_site "
				. "WHERE alert.idalert = alert_site.idalert "
				. "AND deleted = 0 ";

		if ($this->isValidPeriod($period)) {
			$sql .= sprintf("AND period = '%s'", $period);
		} else {
			throw new Exception("Invalid period given.");
		}

		return Db::fetchAll($sql);
	}

	/**
	 * Creates an Alert for given website(s).
	 *
	 * @param string $name
	 * @param mixed $idSites
	 * @param string $period
	 * @param bool $email
	 * @param string $metric (nb_uniq_visits, sum_visit_length, ..)
	 * @param string $metricCondition
	 * @param float $metricValue
	 * @param string $report
	 * @param string $reportCondition
	 * @param string $reportValue
	 * @return int ID of new Alert
	 */
	public function addAlert($name, $idSites, $period, $email, $metric, $metricCondition, $metricValue, $report, $reportCondition = '', $reportValue = '')
	{
		if (!is_array($idSites)) {
			$idSites = array($idSites);
		}

		Piwik::checkUserHasViewAccess($idSites);
		
		$name = $this->checkName($name);
		$this->checkPeriod($period);

		// save in db
		$db = Db::get();
		$idAlert = Db::fetchOne("SELECT max(idalert) + 1 FROM " . Common::prefixTable('alert'));
		if ($idAlert == false) {
			$idAlert = 1;
		}

		$newAlert = array(
			'idalert' => $idAlert,
			'name' => $name,
			'period' => $period,
			'login' => Piwik::getCurrentUserLogin(),
			'enable_mail' => (int) $email,
			'metric' => $metric,
			'metric_condition' => $metricCondition,
			'metric_matched' => (float) $metricValue,
			'report' => $report,
			'deleted' => 0,
		);

		if (!empty($reportCondition) && !empty($reportCondition)) {
			$newAlert['report_condition'] = $reportCondition;
			$newAlert['report_matched'] = $reportValue;
		}

		// Do we have a valid alert for all given idSites?
		foreach ($idSites as $idSite) {
			if (!$this->isValidAlert($newAlert, $idSite)) {
				throw new Exception(Piwik::translate('Alerts_ReportOrMetricIsInvalid'));
			}
		}

		$db->insert(Common::prefixTable('alert'), $newAlert);
		foreach ($idSites as $idSite) {
			$db->insert(Common::prefixTable('alert_site'), array(
				'idalert' => $idAlert,
				'idsite' => $idSite
			));
		}
		return $idAlert;
	}

	/**
	 * Edits an Alert for given website(s).
	 *
	 * @param string $idalert ID of the Alert to edit.
	 * @param string $name Name of Alert
	 * @param mixed $idSites Single int or array of ints of idSites.
	 * @param string $period Period the alert is defined on.
	 * @param bool $email
	 * @param string $metric (nb_uniq_visits, sum_visit_length, ..)
	 * @param string $metricCondition
	 * @param float $metricValue
	 * @param string $report
	 * @param string $reportCondition
	 * @param string $reportValue
	 * @return boolean
	 */
	public function editAlert($idAlert, $name, $idSites, $period, $email, $metric, $metricCondition, $metricValue, $report, $reportCondition = '', $reportValue = '')
	{
		if (!is_array($idSites)) {
			$idSites = array($idSites);
		}

		Piwik::checkUserHasViewAccess($idSites);

		// Is the name in a valid format?
		$name = $this->checkName($name);

		// Is the period valid?
		$this->checkPeriod($period);

		// Save in DB
        $db = Db::get();

		$alert = array(
			'name' => $name,
			'period' => $period,
			'login' => 'admin', //Piwik::getCurrentUserLogin(),
			'enable_mail' => (boolean) $email,
			'metric' => $metric,
			'metric_condition' => $metricCondition,
			'metric_matched' => (float) $metricValue,
			'report' => $report,
			'deleted' => 0,
		);

		//
		if (!empty($reportCondition) && !empty($reportCondition)) {
			$alert['report_condition'] = $reportCondition;
			$alert['report_matched'] = $reportValue;
		} else {
			$alert['report_condition'] = null;
			$alert['report_matched'] = null;
		}

		// Do we have a valid alert for all given idSites?
		foreach ($idSites as $idSite) {
			if (!$this->isValidAlert($alert, $idSites)) {
				throw new Exception(Piwik::translate('CustomAlerts_ReportOrMetricIsInvalid'));
			}
		}

		$db->update(Common::prefixTable('alert'), $alert, "idalert = " . $idAlert);

		$db->query("DELETE FROM " . Common::prefixTable("alert_site") . "
					WHERE idalert = ?", $idAlert);

		foreach ($idSites as $idSite) {
			$db->insert(Common::prefixTable('alert_site'), array(
				'idalert' => $idAlert,
				'idsite' => $idSite
			));
		}
		return $idAlert;
	}

	/**
	 * Delete alert by id.
	 *
	 * @param int $idAlert
	 */
	public function deleteAlert($idAlert)
	{
		$alert = $this->getAlert($idAlert);

		if (!$alert) {
			throw new Exception(Piwik::translate('CustomAlerts_AlertDoesNotExist', $idAlert));
		}

		Piwik::checkUserIsSuperUserOrTheUser($alert['login']);

        $db = Db::get();
		$db->update(
				Common::prefixTable('alert'),
				array("deleted" => 1),
				"idalert = " . $idAlert
		);
	}

	/**
	 * Checks whether a report + metric exists for
	 * the given idSites and if the a dimension is
	 * given (requires report_condition, report_matched)
	 *
	 * @param array alert
	 * @param int $idSite
	 * @return boolean
	 */
	private function isValidAlert($alert, $idSite)
	{
		list($module, $action) = explode(".", $alert['report']);

		$report = MetadataApi::getInstance()->getMetadata($idSite, $module, $action);

		// If there is no report matching module + action for idSite it's not valid.
		if(count($report) == 0) {
			return false;
		}

		// Merge all available metrics
		$allMetrics = $report[0]['metrics'];
		if (isset($report[0]['processedMetrics'])) {
			$allMetrics = array_merge($allMetrics, $report[0]['processedMetrics']);
		}
		if (isset($report[0]['metricsGoal'])) {
			$allMetrics = array_merge($allMetrics, $report[0]['metricsGoal']);
		}
		if (isset($report[0]['processedMetricsGoal'])) {
			$allMetrics = array_merge($allMetrics, $report[0]['processedMetricsGoal']);
		}

		if (!in_array($alert['metric'], array_keys($allMetrics))) {
			return false;
		}

		// If we have a dimension, we need to check if
		// report_condition and report_matched is given.
		if (isset($report[0]['dimension'])
				&& (!isset($alert['report_condition']) || !isset($alert['report_matched']))) {
			return false;
		} else {
			return true;
		}

		return false;
	}

	private function checkName($name)
	{
		return urldecode($name);
	}

	private function checkPeriod($period)
	{
		if (!$this->isValidPeriod($period)) {
			throw new Exception(Piwik::translate('CustomAlerts_InvalidPeriod'));
		}
	}

	private function isValidPeriod($period)
	{
		return in_array($period, array('day', 'week', 'month', 'year'));
	}

}
?>