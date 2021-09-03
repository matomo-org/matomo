<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Container\StaticContainer;
use Piwik\DataAccess\RawLogDao;
use Piwik\Plugin\LogTablesProvider;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Tracker\LogTable;

/**
 * Service that deletes log entries. Methods in this class cascade, so deleting visits will delete visit actions,
 * conversions and conversion items.
 */
class LogDeleter
{
    /**
     * @var RawLogDao
     */
    private $rawLogDao;

    /**
     * @var LogTablesProvider
     */
    private $logTablesProvider;

    public function __construct(RawLogDao $rawLogDao, LogTablesProvider $logTablesProvider)
    {
        $this->rawLogDao = $rawLogDao;
        $this->logTablesProvider = $logTablesProvider;
    }

    /**
     * Deletes visits by ID. This method cascades, so conversions, conversion items and visit actions for
     * the visits are also deleted.
     *
     * @param int[] $visitIds
     * @return int The number of deleted visits.
     */
    public function deleteVisits($visitIds)
    {
        $numDeletedVisits = 0;

        foreach ($this->logTablesProvider->getAllLogTables() as $logTable) {
            if ($logTable->getColumnToJoinOnIdVisit()) {
                $numVisits = $this->rawLogDao->deleteFromLogTable($logTable->getName(), $visitIds);
                if ($logTable->getName() === 'log_visit') {
                    $numDeletedVisits = $numVisits;
                }
            } elseif ($ways = $logTable->getWaysToJoinToOtherLogTables()) {
                // ...
                $a = $ways;
            }
        }
        $tables = StaticContainer::get(DataSubjects::class)->getLogTablesToDeleteFrom();

        return $numDeletedVisits;
    }

    /**
     * Deletes visits within the specified date range and belonging to the specified site (if any). Visits are
     * deleted in chunks, so only `$iterationStep` visits are deleted at a time.
     *
     * @param string|null $startDatetime A datetime string. Visits that occur at this time or after are deleted. If not supplied,
     *                                   visits from the beginning of time are deleted.
     * @param string|null $endDatetime A datetime string. Visits that occur before this time are deleted. If not supplied,
     *                                 visits from the end of time are deleted.
     * @param int|null $idSite The site to delete visits from.
     * @param int $iterationStep The number of visits to delete at a single time.
     * @param callable $afterChunkDeleted Callback executed after every chunk of visits are deleted.
     * @return int The number of visits deleted.
     */
    public function deleteVisitsFor($startDatetime, $endDatetime, $idSite = null, $iterationStep = 2000, $afterChunkDeleted = null)
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
        } elseif (!empty($startDatetime) || !empty($endDatetime)) {
            // make sure to use index!
            $sitesModel = new Model();
            $allIdSites = $sitesModel->getSitesId();
            $allIdSites = array_map('intval', $allIdSites);
            $conditions[] = array('idsite', '', $allIdSites);
        }

        $logsDeleted = 0;
        $logPurger = $this;
        $logTables = StaticContainer::get(DataSubjects::class)->getLogTablesToDeleteFrom();
        // @var LogTable[]
        $logTablesMinusLogVisit = array_filter($logTables, function (LogTable $logTable) {
            return $logTable->getName() !== 'log_visit';
        });
//        foreach ($logTablesMinusLogVisit as $logTable) {
//            $table = $logTable->getName();
//            $idColumn = $logTable->getIdColumn();
//            $waysToJoin = $logTable->getWaysToJoinToOtherLogTables();
//            $joinTable = key($waysToJoin);
//            $joinColumn = $waysToJoin[$joinTable];
//            $idVisitColumn = $logTable->getColumnToJoinOnIdVisit();
//            $sql = "DELETE FROM `$table` WHERE `$idColumn` IN ?";
//            $sql = "SELECT * " .
//                "FROM matomo_log_form_field AS log_form_field JOIN matomo_log_form AS log_form " .
//                "ON (log_form_field.idlogform=log_form.idlogform) JOIN matomo_log_visit AS log_visit " .
//                "ON (log_form.idvisit=log_visit.idvisit) " .
//                "WHERE log_visit.visit_last_action_time<='2021-12-12'";
//            $logVisitTable = Common::prefixTable('log_visit');
//            $sql = "DELETE FROM `$table` AS `table` JOIN `$joinTable` AS `join_table` " .
//                "ON (`$table`.`$joinColumn`=`$joinTable`.`$joinColumn`) JOIN `$logVisitTable` AS `log_visit` " .
//                "ON (`$joinTable`.`$idVisitColumn`=`log_visit`.`idvisit`) " .
//                "WHERE `log_visit`.`visit_last_action_time` BETWEEN ? AND ?";
////            Db::query($sql, [$startDatetime, $endDatetime]);
////            getColumnToJoinOnIdVisit
//
//        }
        $this->rawLogDao->forAllLogs('log_visit', $fields, $conditions, $iterationStep, function ($logs) use ($logPurger, &$logsDeleted, $afterChunkDeleted) {
            $ids = array_map(function ($row) { return (int) (reset($row)); }, $logs);
            sort($ids);
            $logsDeleted += $logPurger->deleteVisits($ids);

            if (!empty($afterChunkDeleted)) {
                $afterChunkDeleted($logsDeleted);
            }
        }, $willDelete = true);

        return $logsDeleted;
    }
}
