<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs\Impl;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Jobs\Job;
use Piwik\Jobs\Queue;
use Piwik\Sequence;

/**
 * MySQL based distributed queue implementation.
 *
 * Uses an option value.
 *
 * TODO: change to using table causes random failure in ArchiveCronTests. likely due to pull method.
 */
class DistributedJobsQueue implements Queue
{
    const TABLE_NAME = 'jobs';

    private $distributedSequence;

    /**
     * Constructor.
     */
    public function __construct()
    {
        self::createTableIfNotExists();

        $this->distributedSequence = new Sequence("DistributedJobsQueue.lockid");
        if (!$this->distributedSequence->exists()) {
            $this->distributedSequence->create(0);
        }
    }

    /**
     * Atomically adds a list of jobs to the Piwik Option that holds the queue.
     *
     * This operation uses a named lock to ensure atomicity.
     *
     * @param Job[] $jobs The jobs to add.
     */
    public function enqueue($jobs)
    {
        $sql = "INSERT INTO `" . self::getTableName() . "` (data) VALUES ";

        reset($jobs);
        $firstKey = key($jobs);

        $bind = array();
        foreach ($jobs as $key => $job) {
            if ($firstKey != $key) {
                $sql .= ", ";
            }

            $sql .= "(?)";
            $bind[] = $this->getSerializedJob($job);
        }

        Db::query($sql, $bind);
    }

    /**
     * Atomically pops N jobs from the queue in the Piwik Option and returns them.
     *
     * This operation uses a named lock to ensure atomicity.
     *
     * @param int $count The maximum number of jobs to get.
     * @return Job[] The jobs at the front of the queue.
     */
    public function pull($count)
    {
        $lockId = $this->generateLockId();

        Db::query("UPDATE `" . self::getTableName() . "`
                      SET lockid = ?
                    WHERE lockid IS NULL
                 ORDER BY idjob ASC
                    LIMIT " . (int) $count, array($lockId));

        $serializedJobs = Db::fetchAll("SELECT data FROM `" . self::getTableName() . "` WHERE lockid = ?", array($lockId));

        Db::query("DELETE FROM `" . self::getTableName() . "` WHERE lockid = ?", array($lockId));

        $jobs = array();
        foreach ($serializedJobs as $row) {
            $jobs[] = $this->unserializeJob($row['data']);
        }
        return $jobs;
    }

    /**
     * Returns the number of URLs in the queue in the Piwik Option.
     *
     * This operation uses a named lock to ensure atomicity.
     *
     * @return int
     */
    public function peek()
    {
        return Db::fetchOne("SELECT COUNT(*) FROM `" . self::getTableName() . "` WHERE lockid IS NULL");
    }

    private function getSerializedJob(Job $job)
    {
        return serialize($job);
    }

    private static function getTableName()
    {
        return Common::prefixTable(self::TABLE_NAME);
    }

    private static function createTableIfNotExists()
    {
        $createTableSql = DbHelper::getTableCreateSql(self::getTableName());
        Db::query($createTableSql);
    }

    /**
     * Generates a unique lock ID for job rows. A lock ID is used so we can issue multiple queries
     * w/o using explicit locks when pulling jobs. The lock ID must be unique across different
     * processes and machines.
     *
     * @return int
     */
    private function generateLockId()
    {
        return $this->distributedSequence->getNextId();
    }

    private function unserializeJob($serializedJob)
    {
        $job = unserialize($serializedJob);
        if (!($job instanceof Job)) {
            $job = null;
        }
        return $job;
    }
}