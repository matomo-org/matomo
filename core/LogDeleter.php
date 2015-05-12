<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\DataAccess\RawLogDao;

/**
 * TODO
 *
 * TODO: class + method docs
 */
class LogDeleter
{
    /**
     * @var RawLogDao
     */
    private $rawLogDao;

    public function __construct(RawLogDao $rawLogDao)
    {
        $this->rawLogDao = $rawLogDao;
    }

    public function deleteVisits($visitIds)
    {
        $this->deleteConversions($visitIds);
        $this->rawLogDao->deleteVisitActionsForVisits($visitIds);

        return $this->rawLogDao->deleteVisits($visitIds);
    }

    public function deleteVisitActions($visitActionIds)
    {
        return $this->rawLogDao->deleteVisitActions($visitActionIds);
    }

    public function deleteConversions($visitIds)
    {
        $this->deleteConversionItems($visitIds);
        return $this->rawLogDao->deleteConversions($visitIds);
    }

    public function deleteConversionItems($visitIds)
    {
        return $this->rawLogDao->deleteConversionItems($visitIds);
    }

    /**
     * @param $startDatetime
     * @param $endDatetime
     * @param null $idSite
     * @param int $iterationStep
     * @param callable $afterChunkDeleted
     * @return int
     */
    public function deleteVisitsFor($startDatetime, $endDatetime, $idSite = null, $iterationStep = 1000, $afterChunkDeleted = null)
    {
        $fields = array('idvisit');
        $conditions = array();

        if (!empty($startDatetime)) {
            $conditions[] = array('visit_last_action_time', '>=', $startDatetime);
        }

        if (!empty($endDatetime)) {
            $conditions[] = array('visit_last_action_time', '<', $endDatetime);
        }

        if (!empty($idSite)) {
            $conditions[] = array('idsite', '=', $idSite);
        }

        $logsDeleted = 0;
        $logPurger = $this;
        $this->rawLogDao->forAllLogs('log_visit', $fields, $conditions, $iterationStep, function ($logs) use ($logPurger, &$logsDeleted, $afterChunkDeleted) {
            $ids = array_map(function ($row) { return reset($row); }, $logs);
            $logsDeleted += $logPurger->deleteVisits($ids);

            if (!empty($afterChunkDeleted)) {
                $afterChunkDeleted($logsDeleted);
            }
        });

        return $logsDeleted;
    }
}