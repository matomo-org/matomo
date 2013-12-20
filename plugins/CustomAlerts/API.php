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

use Piwik\Period;
use Piwik\Db;

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
     *
     * @return array
     */
	public function getAlert($idAlert)
	{
        return $this->getModel()->getAlert($idAlert);
	}

    /**
     * Returns the Alerts that are defined on the idSites given.
     * If no value is given, all Alerts for the current user will
     * be returned.
     *
     * @param array $idSites
     * @return array
     */
	public function getAlerts($idSites = array())
	{
        return $this->getModel()->getAlerts($idSites);
	}

	public function getTriggeredAlerts($period, $date, $login = false)
	{
        return $this->getModel()->getTriggeredAlerts($period, $date, $login);
	}

	public function getAllAlerts($period)
	{
        return $this->getModel()->getAllAlerts($period);
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
        return $this->getModel()->addAlert($name, $idSites, $period, $email, $metric, $metricCondition, $metricValue, $report, $reportCondition, $reportValue);
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
     * @return boolean
     */
	public function editAlert($idAlert, $name, $idSites, $period, $email, $metric, $metricCondition, $metricValue, $report, $reportCondition = '', $reportValue = '')
	{
        return $this->getModel()->editAlert($idAlert, $name, $idSites, $period, $email, $metric, $metricCondition, $metricValue, $report, $reportCondition, $reportValue);
	}

	/**
	 * Delete alert by id.
	 *
	 * @param int $idAlert
	 */
	public function deleteAlert($idAlert)
	{
        $this->getModel()->deleteAlert($idAlert);
	}

    private function getModel()
    {
        return new Model();
    }

}
?>