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

use Piwik\View;
use Piwik\Common;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;
use Piwik\Plugins\API\API as MetadataApi;
use Piwik\Period;
use Piwik\Db;

/**
  *
 * @package Piwik_Alerts
 */
class Controller extends \Piwik\Plugin\Controller
{

	private $alertGroupConditions = array(
	    'CustomAlerts_MatchesExactly' => 'matches_exactly',
	    'CustomAlerts_DoesNotMatchExactly' => 'does_not_match_exactly',
	    'CustomAlerts_MatchesRegularExpression' => 'matches_regex',
	    'CustomAlerts_DoesNotMatchRegularExpression' => 'does_not_match_regex',
	    'CustomAlerts_Contains' => 'contains',
	    'CustomAlerts_DoesNotContain' => 'does_not_contain',
	    'CustomAlerts_StartsWith' => 'starts_with',
	    'CustomAlerts_DoesNotStartWith' => 'does_not_start_with',
	    'CustomAlerts_EndsWith' => 'ends_with',
	    'CustomAlerts_DoesNotEndWith' => 'does_not_end_with',
	);
	private $alertMetricConditions = array(
	    'CustomAlerts_IsLessThan' => 'less_than',
	    'CustomAlerts_IsGreaterThan' => 'greater_than',
	    'CustomAlerts_DecreasesMoreThan' => 'decrease_more_than',
	    'CustomAlerts_IncreasesMoreThan' => 'increase_more_than',
	    'CustomAlerts_PercentageDecreasesMoreThan' => 'percentage_decrease_more_than',
	    'CustomAlerts_PercentageIncreasesMoreThan' => 'percentage_increase_more_than',
	);

	/**
	 * Shows all Alerts of the current selected idSite.
	 */
	public function index()
	{
        $view = new View('@CustomAlerts/index');
		$this->setGeneralVariablesView($view);

		$idSite = Common::getRequestVar('idSite');

		$alertList = API::getInstance()->getAlerts(array($idSite));

		$view->alertList = $alertList;

		return $view->render();
	}

	public function addNewAlert()
	{
        $view = new View('@CustomAlerts/addNewAlert');
		$this->setGeneralVariablesView($view);

		$sitesList = SitesManagerApi::getInstance()->getSitesWithAtLeastViewAccess();
		$view->sitesList = $sitesList;

		$availableReports = MetadataApi::getInstance()->getReportMetadata();

		// ToDo need to collect metrics,processedMetrics,goalMetrics, goalProcessedMetric

        $view->alertGroups = array();
		$view->alerts = $availableReports;
		$view->alertGroupConditions  = $this->alertGroupConditions;
		$view->alertMetricConditions = $this->alertMetricConditions;

		return $view->render();
	}

	public function editAlert()
	{
		$idAlert = Common::getRequestVar('idalert');

        $view = new View('@CustomAlerts/editAlert');
		$this->setGeneralVariablesView($view);

		$alert = API::getInstance()->getAlert($idAlert);
		$view->alert = $alert;

		$sitesList = SitesManagerApi::getInstance()->getSitesWithAtLeastViewAccess();
		$view->sitesList = $sitesList;

        $model = new Model();
		$view->sitesDefined = $model->fetchSiteIdsTheAlertWasDefinedOn($idAlert);

		$availableReports = MetadataApi::getInstance()->getReportMetadata();

		$view->alerts = $availableReports;
		$view->alertGroupConditions  = $this->alertGroupConditions;
		$view->alertMetricConditions = $this->alertMetricConditions;

		return $view->render();
	}
}
?>
