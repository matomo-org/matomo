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
class Model
{

    public static function install()
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

    public static function uninstall()
    {
        $tables = array('alert', 'alert_log', 'alert_site');
        foreach ($tables as $table) {
            $sql = "DROP TABLE " . Common::prefixTable($table);
            Db::exec($sql);
        }
    }


    /**
     * Returns a single Alert
     *
     * @param int $idAlert
     *
     * @return array
     */
	public function getAlert($idAlert)
	{
        $query = sprintf('SELECT * FROM %s WHERE idalert = ?', Common::prefixTable('alert'), intval($idAlert));
		$alert = Db::fetchAll($query);

		if (empty($alert)) {
			throw new Exception(Piwik::translate('CustomAlerts_AlertDoesNotExist', $idAlert));
		}

		$alert = array_shift($alert);

		if (!Piwik::isUserIsSuperUserOrTheUser($alert['login'])) {
			throw new Exception(Piwik::translate('CustomAlerts_AccessException', $idAlert));
		}

        $alert['idSites'] = $this->fetchSiteIdsTheAlertWasDefinedOn($idAlert);

		return $alert;
	}

	/**
	 * Returns the Alerts that are defined on the idSites given.
	 * If no value is given, all Alerts for the current user will
	 * be returned.
	 *
	 * @param array $idSites
	 */
	public function getAlerts($idSites)
	{
		if (count($idSites)) {
			Piwik::checkUserHasViewAccess($idSites);
		} else {
			Piwik::checkUserHasSomeViewAccess();
			$idSites = SitesManagerApi::getInstance()->getSitesIdWithAtLeastViewAccess();
		}

        $idSites = array_map('intval', $idSites);

		$alerts = Db::fetchAll(("SELECT * FROM "
						. Common::prefixTable('alert')
						. " WHERE idalert IN (
					 SELECT pas.idalert FROM " . Common::prefixTable('alert_site')
						. "  pas WHERE idsite IN (" . implode(",", $idSites) . ")) "
						. "AND deleted = 0"
		));

		return $alerts;
	}

	public function getTriggeredAlerts($period, $date, $login)
	{
		Piwik::checkUserIsSuperUserOrTheUser($login);

		$this->checkPeriod($period);
		$piwikDate = Date::factory($date);
		$date      = Period::factory($period, $piwikDate);

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

        $values = array(
            $period,
            $date->getDateStart()->getDateStartUTC(),
            $date->getDateEnd()->getDateEndUTC()
        );

		if ($login !== false) {
			$sql     .= " AND login = ?";
            $values[] = $login;
		}

		return $db->fetchAll($sql, $values);

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
     *
     * @throws \Exception
     *
     * @return int ID of new Alert
     */
	public function addAlert($name, $idSites, $period, $email, $metric, $metricCondition, $metricValue, $report, $reportCondition, $reportValue)
	{
		if (!is_array($idSites)) {
			$idSites = array($idSites);
		}

		Piwik::checkUserHasViewAccess($idSites);
		
		$name = $this->checkName($name);
		$this->checkPeriod($period);

        $idAlert = $this->getNextAlertId();
        if (empty($idAlert)) {
			$idAlert = 1;
		}

		$newAlert = array(
			'idalert'          => $idAlert,
			'name'             => $name,
			'period'           => $period,
			'login'            => Piwik::getCurrentUserLogin(),
			'enable_mail'      => (int) $email,
			'metric'           => $metric,
			'metric_condition' => $metricCondition,
			'metric_matched'   => (float) $metricValue,
			'report'           => $report,
			'deleted'          => 0,
		);

		if (!empty($reportCondition) && !empty($reportCondition)) {
			$newAlert['report_condition'] = $reportCondition;
			$newAlert['report_matched']   = $reportValue;
		} else {
            $alert['report_condition'] = null;
            $alert['report_matched']   = null;
        }

		// Do we have a valid alert for all given idSites?
		foreach ($idSites as $idSite) {
			if (!$this->isValidAlert($newAlert, $idSite)) {
				throw new Exception(Piwik::translate('Alerts_ReportOrMetricIsInvalid'));
			}
		}

        // save in db
        $db = Db::get();
		$db->insert(Common::prefixTable('alert'), $newAlert);
		foreach ($idSites as $idSite) {
			$db->insert(Common::prefixTable('alert_site'), array(
				'idalert' => intval($idAlert),
				'idsite'  => intval($idSite)
			));
		}
		return $idAlert;
	}

    /**
     * Edits an Alert for given website(s).
     *
     * @param $idAlert
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
     *
     * @throws \Exception
     *
     * @return boolean
     */
	public function editAlert($idAlert, $name, $idSites, $period, $email, $metric, $metricCondition, $metricValue, $report, $reportCondition, $reportValue)
	{
		if (!is_array($idSites)) {
			$idSites = array($idSites);
		}

		Piwik::checkUserHasViewAccess($idSites);

		$name = $this->checkName($name);
		$this->checkPeriod($period);

		$alert = array(
			'name'             => $name,
			'period'           => $period,
			'login'            => 'admin', //Piwik::getCurrentUserLogin(),
			'enable_mail'      => (boolean) $email,
			'metric'           => $metric,
			'metric_condition' => $metricCondition,
			'metric_matched'   => (float) $metricValue,
			'report'           => $report,
			'deleted'          => 0,
		);

		if (!empty($reportCondition) && !empty($reportCondition)) {
			$alert['report_condition'] = $reportCondition;
			$alert['report_matched']  = $reportValue;
		} else {
			$alert['report_condition'] = null;
			$alert['report_matched']   = null;
		}

		// Do we have a valid alert for all given idSites?
		foreach ($idSites as $idSite) {
			if (!$this->isValidAlert($alert, $idSite)) {
				throw new Exception(Piwik::translate('CustomAlerts_ReportOrMetricIsInvalid'));
			}
		}

        // Save in DB
        $db = Db::get();
		$db->update(Common::prefixTable('alert'), $alert, "idalert = " . intval($idAlert));

		$db->query("DELETE FROM " . Common::prefixTable("alert_site") . "
					WHERE idalert = ?", $idAlert);

		foreach ($idSites as $idSite) {
			$db->insert(Common::prefixTable('alert_site'), array(
				'idalert' => intval($idAlert),
				'idsite'  => intval($idSite)
			));
		}

		return $idAlert;
	}

    /**
     * Delete alert by id.
     *
     * @param int $idAlert
     *
     * @throws \Exception In case alert does not exist or not enough permission
     */
	public function deleteAlert($idAlert)
	{
		$alert = $this->getAlert($idAlert);

		if (empty($alert)) {
			throw new Exception(Piwik::translate('CustomAlerts_AlertDoesNotExist', $idAlert));
		}

		Piwik::checkUserIsSuperUserOrTheUser($alert['login']);

        $db = Db::get();
		$db->update(
				Common::prefixTable('alert'),
				array("deleted" => 1),
				"idalert = " . intval($idAlert)
		);
	}

    public function triggerAlert($idAlert, $idSite)
    {
        $alert = $this->getAlert($idAlert);

        if (empty($alert)) {
            throw new Exception(Piwik::translate('CustomAlerts_AlertDoesNotExist', $idAlert));
        }

        Piwik::checkUserIsSuperUserOrTheUser($alert['login']);

        $db = Db::get();
        $db->insert(
            Common::prefixTable('alert_log'),
            array(
                'idalert' => intval($idAlert),
                'idsite'  => intval($idSite),
                'ts_triggered' => Date::now()->getDatetime()
            )
        );
    }

    public function fetchSiteIdsTheAlertWasDefinedOn($idAlert)
    {
        $sql     = "SELECT idsite FROM ".Common::prefixTable('alert_site')." WHERE idalert = ?";
        $sites   = Db::fetchAll($sql, $idAlert, \PDO::FETCH_COLUMN);

        $idSites = array();
        foreach ($sites as $site) {
            $idSites[] = $site['idsite'];
        }

        return $idSites;
    }

	/**
	 * Checks whether a report + metric exists for
	 * the given idSites and if the a dimension is
	 * given (requires report_condition, report_matched)
	 *
	 * @param array $alert
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
		}

        return true;
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

    private function getNextAlertId()
    {
        $idAlert = Db::fetchOne("SELECT max(idalert) + 1 FROM " . Common::prefixTable('alert'));
        return $idAlert;
    }

}
?>