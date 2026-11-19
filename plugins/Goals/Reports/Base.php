<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Reports;

use Piwik\API\Request;
use Piwik\Plugins\Goals\GoalsSorter;
use Piwik\Url;

abstract class Base extends \Piwik\Plugin\Report
{
    protected $orderGoal = 50;

    protected function init()
    {
        $this->categoryId = 'Goals_Goals';
        $this->onlineGuideUrl = Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/tracking-goals-web-analytics/');
    }

    protected function addReportMetadataForEachGoal(&$availableReports, $infos, $goalNameFormatter)
    {
        $idSite = $this->getIdSiteFromInfos($infos);
        $goals  = GoalsSorter::sortStarredFirst($this->getGoalsForIdSite($idSite));

        // the reports of a goal are ordered like the goals are listed: starred goals first, then by name
        $position = 0;
        foreach ($goals as $goal) {
            $position++;

            $this->name       = $goalNameFormatter($goal);
            $this->parameters = array('idGoal' => $goal['idgoal']);
            $this->order      = $this->orderGoal + $position * 3;

            $report = $this->buildReportMetadata();
            if (GoalsSorter::isStarred($goal)) {
                // only set for the reports of a starred goal: API.getReportMetadata removes every
                // attribute of a report that is false
                $report['isStarred'] = true;
            }

            $availableReports[] = $report;
        }

        $this->init();
    }

    protected function getIdSiteFromInfos($infos)
    {
        $idSite = $infos['idSite'];

        if (empty($idSite)) {
            return null;
        }

        return $idSite;
    }

    private function getGoalsForIdSite($idSite)
    {
        if (empty($idSite)) {
            return array();
        }

        return Request::processRequest('Goals.getGoals', ['idSite' => $idSite, 'filter_limit' => '-1'], $default = []);
    }
}
