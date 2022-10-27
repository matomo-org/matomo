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

use function Piwik\Plugins\Referrers\getReferrerTypeFromShortName;

class VisitorDetails extends VisitorDetailsAbstract
{
    const EVENT_VALUE_PRECISION = 3;

    protected $lastGoalResults = [];
    protected $lastVisitIds    = [];

    public function extendVisitorDetails(&$visitor)
    {
        $idVisit = $visitor['idVisit'];

        if (in_array($idVisit, $this->lastVisitIds)) {
            $goalConversionDetails = isset($this->lastGoalResults[$idVisit]) ? $this->lastGoalResults[$idVisit] : [];
        } else {
            $goalConversionDetails = $this->queryGoalConversionsForVisits([$idVisit]);
        }

        $visitor['goalConversions'] = count($goalConversionDetails);
    }

    public function provideActionsForVisitIds(&$actions, $idVisits)
    {
        $this->lastVisitIds    = $idVisits;
        $this->lastGoalResults = [];
        $goalConversionDetails = $this->queryGoalConversionsForVisits($idVisits);

        // use while / array_shift combination instead of foreach to save memory
        while (is_array($goalConversionDetails) && count($goalConversionDetails)) {
            $goalConversionDetail = array_shift($goalConversionDetails);
            $idVisit              = $goalConversionDetail['idvisit'];

            unset($goalConversionDetail['idvisit']);
            $goalConversionDetail['referrerType'] = $this->getReferrerType($goalConversionDetail['referrerType']);

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
        if (empty($idVisits)) {
            return [];
        }
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
						log_conversion.url as url,
						log_conversion.referer_type as referrerType,
						log_conversion.referer_name as referrerName,
						log_conversion.referer_keyword as referrerKeyword
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
        $conversions = $this->getDb()->fetchAll($sql);

        foreach ($conversions as &$conversion) {
            $conversion['goalName'] = Common::unsanitizeInputValue($conversion['goalName']);
        }

        return $conversions;
    }


    public function initProfile($visits, &$profile)
    {
        $profile['totalGoalConversions']   = 0;
        $profile['totalConversionsByGoal'] = [];
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

    protected function getReferrerType($referrerTypeId)
    {
        try {
            $referrerType = getReferrerTypeFromShortName($referrerTypeId);
        } catch (\Exception $e) {
            $referrerType = '';
        }

        return $referrerType;
    }
}
