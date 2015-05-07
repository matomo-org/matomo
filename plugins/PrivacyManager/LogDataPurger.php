<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\RawLogDao;
use Piwik\Date;
use Piwik\Db;
use Piwik\Log;
use Piwik\LogPurger;
use Piwik\Piwik;

/**
 * Purges the log_visit, log_conversion and related tables of old visit data.
 */
class LogDataPurger
{
    /**
     * The max set of rows each table scan select should query at one time.
     */
    public static $selectSegmentSize = 100000;

    /**
     * TODO
     *
     * @var LogPurger
     */
    private $logPurger;

    /**
     * TODO
     *
     * @var RawLogDao
     */
    private $rawLogDao;

    /**
     * TODO
     *
     * @var int
     */
    private $logIterationStepSize = 1000; // TODO: make configurable via constructor

    /**
     * Constructor.
     */
    public function __construct(LogPurger $logPurger, RawLogDao $rawLogDao)
    {
        $this->logPurger = $logPurger;
        $this->rawLogDao = $rawLogDao;
    }

    /**
     * Purges old data from the following tables:
     * - log_visit
     * - log_link_visit_action
     * - log_conversion
     * - log_conversion_item
     * - log_action
     *
     * @param int $deleteLogsOlderThan The number of days after which log entires are considered old.
     *                                 Visits and related data whose age is greater than this number
     *                                 will be purged.
     * @param int $maxRowsToDeletePerQuery The maximum number of rows to delete in one query. Used to
     *                                     make sure log tables aren't locked for too long.
     */
    public function purgeData($deleteLogsOlderThan, $maxRowsToDeletePerQuery)
    {
        $dateStart = Date::factory("today")->subDay($deleteLogsOlderThan); // TODO: move logic to constructor
        $conditions = array(
            array('visit_last_action_time', '<', $dateStart->getDatetime())
        );

        $logPurger = $this->logPurger;
        $this->rawLogDao->forAllLogs('log_visit', array('idvisit'), $conditions, $this->logIterationStepSize, function ($rows) use ($logPurger) {
            $ids = array_map('reset', $rows);
            $logPurger->deleteVisits($ids);
        });

        $logTables = self::getDeleteTableLogTables();

        // delete unused actions from the log_action table (but only if we can lock tables)
        if (Db::isLockPrivilegeGranted()) {
            $this->rawLogDao->deleteUnusedLogActions();
        } else {
            $logMessage = get_class($this) . ": LOCK TABLES privilege not granted; skipping unused actions purge";
            Log::warning($logMessage);
        }

        // optimize table overhead after deletion // TODO: logs:delete command should allow optimization
        Db::optimizeTables($logTables);
    }

    /**
     * Returns an array describing what data would be purged if purging were invoked.
     *
     * This function returns an array that maps table names with the number of rows
     * that will be deleted.
     *
     * @param int $deleteLogsOlderThan The number of days after which log entires are considered old.
     *                                 Visits and related data whose age is greater than this number
     *                                 will be purged.
     * @return array
     *
     * TODO: purge estimate should ideally not use idvisit, but we have to wait until performance tests are done to
     *       really test this.
     * TODO: let's move PrivacyManagerTest to PrivacyManager plugin
     */
    public function getPurgeEstimate($deleteLogsOlderThan)
    {
        $result = array();

        // deal w/ log tables that will be purged
        $maxIdVisit = $this->getDeleteIdVisitOffset($deleteLogsOlderThan);
        if (!empty($maxIdVisit)) {
            foreach ($this->getDeleteTableLogTables() as $table) {
                // getting an estimate for log_action is not supported since it can take too long
                if ($table != Common::prefixTable('log_action')) {
                    $rowCount = $this->getLogTableDeleteCount($table, $maxIdVisit);
                    if ($rowCount > 0) {
                        $result[$table] = $rowCount;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * get highest idVisit to delete rows from
     * @return string
     */
    private function getDeleteIdVisitOffset($deleteLogsOlderThan)
    {
        $logVisit = Common::prefixTable("log_visit");

        // get max idvisit
        $maxIdVisit = Db::fetchOne("SELECT MAX(idvisit) FROM $logVisit");
        if (empty($maxIdVisit)) {
            return false;
        }

        // select highest idvisit to delete from
        $dateStart = Date::factory("today")->subDay($deleteLogsOlderThan);
        $sql = "SELECT idvisit
		          FROM $logVisit
		         WHERE '" . $dateStart->toString('Y-m-d H:i:s') . "' > visit_last_action_time
		           AND idvisit <= ?
		           AND idvisit > ?
		      ORDER BY idvisit DESC
		         LIMIT 1";

        return Db::segmentedFetchFirst($sql, $maxIdVisit, 0, -self::$selectSegmentSize);
    }

    private function getLogTableDeleteCount($table, $maxIdVisit)
    {
        $sql = "SELECT COUNT(*) FROM $table WHERE idvisit <= ?";
        return (int) Db::fetchOne($sql, array($maxIdVisit));
    }

    // let's hardcode, since these are not dynamically created tables
    public static function getDeleteTableLogTables()
    {
        $result = Common::prefixTables('log_conversion',
            'log_link_visit_action',
            'log_visit',
            'log_conversion_item');
        if (Db::isLockPrivilegeGranted()) {
            $result[] = Common::prefixTable('log_action');
        }
        return $result;
    }
}
