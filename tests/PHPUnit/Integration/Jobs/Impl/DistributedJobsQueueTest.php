<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration\Jobs\Impl;

use PHPUnit_Framework_Error;
use Piwik\Common;
use Piwik\Db;
use Piwik\Jobs\Impl\DistributedJobsQueue;
use Piwik\Jobs\Job;
use Piwik\Jobs\UrlJob;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class DerivedJob extends Job
{
    public function getJobData()
    {
        return array();
    }
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
            new UrlJob("?whatever=value"),
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
            new UrlJob(),
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
        $this->assertEquals(array('first' => 'url'), $jobs[0]->url);

        $this->assertInstanceOf("Piwik\\Jobs\\Job", $jobs[1]);
        $this->assertEquals(array('second' => 'url'), $jobs[1]->url);

        $this->assertInstanceOf("Piwik\\Jobs\\Job", $jobs[2]);
        $this->assertEquals(array('third' => 'url'), $jobs[2]->url);
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
            new UrlJob("?first=url"),
            new UrlJob("?second=url"),
            new UrlJob("?third=url")
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
            new UrlJob("?fourth=url"),
            new UrlJob("?fifth=url"),
            new UrlJob("?sixth=url"),
            new UrlJob("?seventh=url")
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