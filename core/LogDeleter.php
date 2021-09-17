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
        $visitIds = array_map(function($visitid) {
            return ['idvisit' => $visitid];
        }, $visitIds);
        $dataSubjects = StaticContainer::get(DataSubjects::class);
        $deleteCounts = $dataSubjects->deleteDataSubjectsWithoutInvalidatingArchives($visitIds);
        return $deleteCounts['log_visit'];
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
