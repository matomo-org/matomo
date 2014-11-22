<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration\Jobs\Impl;

use Exception;
use PHPUnit_Framework_Error;
use Piwik\Common;
use Piwik\Db;
use Piwik\Jobs\Impl\DistributedJobsQueue;
use Piwik\Jobs\Job;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class DerivedJob extends Job
{
}

class DistributedJobsQueueTest extends IntegrationTestCase
{
    /**
     * @var DistributedJobsQueue
     */
    private $distributedQueue;

    public function setUp()
    {
        parent::setUp();

        $this->distributedQueue = new DistributedJobsQueue();
    }

    public function test_enqueue_ShouldInsertsJobs()
    {
        $jobs = array(
            new Job("?whatever=value"),
            new DerivedJob()
        );

        $this->distributedQueue->enqueue($jobs);

        $jobRows = $this->getAllJobData();
        $this->assertEquals(
            array(
                serialize($jobs[0]),
                serialize($jobs[1])
            ),
            $jobRows
        );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessage must be an instance of Piwik\Jobs\Job
     */
    public function test_enqueue_ShouldFailsToInsertNonJobInstances()
    {
        $jobs = array(
            new Job(),
            "non job"
        );

        $this->distributedQueue->enqueue($jobs);
    }

    public function test_pull_ShouldReturnFirstQueuedJobs()
    {
        $this->insertQueuedJobs();

        $jobs = $this->distributedQueue->pull(3);

        $this->assertPullJobsAreCorrect($jobs);
    }

    public function test_pull_ShouldReturnAllQueuedJobs_WhenAskingForMoreThanExistingNumber()
    {
        $this->insertQueuedJobs();

        $jobs = $this->distributedQueue->pull(6);

        $this->assertPullJobsAreCorrect($jobs);
    }

    public function test_pull_ShouldNotPullLockedJobs()
    {
        $this->insertLockedQueuedJobs();
        $this->insertQueuedJobs();

        $jobs = $this->distributedQueue->pull(3);

        $this->assertPullJobsAreCorrect($jobs);
    }

    public function test_peek_ShouldReturnCountOfUnlockedJobs()
    {
        $this->insertLockedQueuedJobs();
        $this->insertQueuedJobs();

        $jobCount = $this->distributedQueue->peek();
        $this->assertEquals(3, $jobCount);
    }

    /**
     * @param Job[] $jobs
     */
    private function assertPullJobsAreCorrect($jobs)
    {
        $this->assertEquals(3, count($jobs));

        $this->assertInstanceOf("Piwik\\Jobs\\Job", $jobs[0]);
        $this->assertEquals("?first=url", $jobs[0]->url);

        $this->assertInstanceOf("Piwik\\Jobs\\Job", $jobs[1]);
        $this->assertEquals("?second=url", $jobs[1]->url);

        $this->assertInstanceOf("Piwik\\Jobs\\Job", $jobs[2]);
        $this->assertEquals("?third=url", $jobs[2]->url);
    }

    private function getAllJobData()
    {
        $rows = Db::fetchAll("SELECT data from " . Common::prefixTable(DistributedJobsQueue::TABLE_NAME));

        $result = array();
        foreach ($rows as $row) {
            $result[] = $row['data'];
        }
        return $result;
    }

    private function insertQueuedJobs()
    {
        $jobs = array(
            new Job("?first=url"),
            new Job("?second=url"),
            new Job("?third=url")
        );
        Db::query(
            "INSERT INTO `" . Common::prefixTable(DistributedJobsQueue::TABLE_NAME) . "` (data) VALUES (?), (?), (?)",
            array(
                serialize($jobs[0]),
                serialize($jobs[1]),
                serialize($jobs[2])
            )
        );
    }

    private function insertLockedQueuedJobs()
    {
        $lockId = 12345;

        $jobs = array(
            new Job("?fourth=url"),
            new Job("?fifth=url"),
            new Job("?sixth=url"),
            new Job("?seventh=url")
        );

        Db::query(
            "INSERT INTO `" . Common::prefixTable(DistributedJobsQueue::TABLE_NAME) . "` (data, lockid)
                VALUES (?, $lockId),(?, $lockId),(?, $lockId),(?, $lockId)",
            array(
                serialize($jobs[0]),
                serialize($jobs[1]),
                serialize($jobs[2]),
                serialize($jobs[3])
            )
        );
    }
}