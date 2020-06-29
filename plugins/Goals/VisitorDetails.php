<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals;

use Piwik\Common;
use Piwik\Plugins\Live\VisitorDetailsAbstract;

class VisitorDetails extends VisitorDetailsAbstract
{
    const EVENT_VALUE_PRECISION = 3;

    protected $lastGoalResults = array();
    protected $lastVisitIds    = array();

    public function extendVisitorDetails(&$visitor)
    {
        $idVisit = $visitor['idVisit'];

        if (in_array($idVisit, $this->lastVisitIds)) {
            $goalConversionDetails = isset($this->lastGoalResults[$idVisit]) ? $this->lastGoalResults[$idVisit] : array();
        } else {
            $goalConversionDetails = $this->queryGoalConversionsForVisits(array($idVisit));
        }

        $visitor['goalConversions'] = count($goalConversionDetails);
    }

    public function provideActionsForVisitIds(&$actions, $idVisits)
    {
        $this->lastVisitIds    = $idVisits;
        $this->lastGoalResults = array();
        $goalConversionDetails = $this->queryGoalConversionsForVisits($idVisits);

        // use while / array_shift combination instead of foreach to save memory
        while (is_array($goalConversionDetails) && count($goalConversionDetails)) {
            $goalConversionDetail = array_shift($goalConversionDetails);
            $idVisit              = $goalConversionDetail['idvisit'];

            unset($goalConversionDetail['idvisit']);

            $this->lastGoalResults[$idVisit][] = $actions[$idVisit][] = $goalConversionDetail;
        }
    }

    /**
     * @param $idVisit
     * @return array
     * @throws \Exception
     */
    protected function queryGoalConversionsForVisits($idVisits)
    {
        $sql = "
				SELECT
						log_conversion.idvisit,
						'goal' as type,
						goal.name as goalName,
						goal.idgoal as goalId,
						log_link_visit_action.idpageview,
						log_conversion.revenue as revenue,
						log_conversion.idlink_va,
						log_conversion.idlink_va as goalPageId,
						log_conversion.server_time as serverTimePretty,
						log_conversion.url as url
				FROM " . Common::prefixTable('log_conversion') . " AS log_conversion
				LEFT JOIN " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
				    ON log_link_visit_action.idlink_va = log_conversion.idlink_va
				LEFT JOIN " . Common::prefixTable('goal') . " AS goal
					ON (goal.idsite = log_conversion.idsite
						AND
						goal.idgoal = log_conversion.idgoal)
					AND goal.deleted = 0
				WHERE log_conversion.idvisit IN ('" . implode("','", $idVisits) . "')
					AND log_conversion.idgoal > 0
                ORDER BY log_conversion.idvisit, log_conversion.server_time ASC
			";
        return $this->getDb()->fetchAll($sql);
    }


    public function initProfile($visits, &$profile)
    {
        $profile['totalGoalConversions']   = 0;
        $profile['totalConversionsByGoal'] = array();
    }

    public function handleProfileVisit($visit, &$profile)
    {
        $profile['totalGoalConversions'] += $visit->getColumn('goalConversions');
    }

    public function handleProfileAction($action, &$profile)
    {
        if ($action['type'] != 'goal') {
            return;
        }

        $idGoal    = $action['goalId'];

        if (empty($idGoal)) {
            return;
        }

        $idGoalKey = 'idgoal=' . $idGoal;

        if (!isset($profile['totalConversionsByGoal'][$idGoalKey])) {
            $profile['totalConversionsByGoal'][$idGoalKey] = 0;
        }
        ++$profile['totalConversionsByGoal'][$idGoalKey];

        if (!empty($action['revenue'])) {
            if (!isset($profile['totalRevenueByGoal'][$idGoalKey])) {
                $profile['totalRevenueByGoal'][$idGoalKey] = 0;
            }
            $profile['totalRevenueByGoal'][$idGoalKey] += $action['revenue'];
        }
    }
}