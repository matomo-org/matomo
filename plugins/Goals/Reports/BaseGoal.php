<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Reports;

use Piwik\Common;
use Piwik\Plugins\Goals\API;
use Piwik\Plugins\Goals\Goals;

abstract class BaseGoal extends \Piwik\Plugin\Report
{
    protected $orderGoal = 50;

    protected function init()
    {
        $this->category = 'Goals_Goals';
    }

    protected function addReportMetadataForEachGoal(&$availableReports, $infos, $goalNameFormatter)
    {
        $idSite = $this->getIdSiteFromInfos($infos);
        $goals  = $this->getGoalsForIdSite($idSite);

        foreach ($goals as $goal) {
            $goal['name'] = Common::sanitizeInputValue($goal['name']);

            $this->name       = $goalNameFormatter($goal);
            $this->parameters = array('idGoal' => $goal['idgoal']);
            $this->order      = $this->orderGoal + $goal['idgoal'] * 3;

            $availableReports[] = $this->buildReportMetadata($availableReports, $infos);
        }

        $this->init();
    }

    protected function getIdSiteFromInfos($infos)
    {
        $idSites = $infos['idSites'];

        if (count($idSites) != 1) {
            return null;
        }

        $idSite = reset($idSites);

        return $idSite;
    }

    private function getGoalsForIdSite($idSite)
    {
        if (empty($idSite)) {
            return array();
        }

        return API::getInstance()->getGoals($idSite);
    }
}
