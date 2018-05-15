<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\Archive\ArchivePurger;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Date;
use Piwik\Plugins\CoreAdminHome\Tasks;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Tests\Fixtures\RawArchiveDataWithTempAndInvalidated;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Psr\Log\NullLogger;

/**
 * @group Core
 */
class TasksTest extends IntegrationTestCase
{
    /**
     * @var RawArchiveDataWithTempAndInvalidated
     */
    public static $fixture;

    /**
     * @var Tasks
     */
    private $tasks;

    /**
     * @var Date
     */
    private $january;

    /**
     * @var Date
     */
    private $february;

    public function setUp()
    {
        parent::setUp();

        $this->january = Date::factory('2015-01-01');
        $this->february = Date::factory('2015-02-01');

        $archivePurger = new ArchivePurger();
        $archivePurger->setTodayDate(Date::factory('2015-02-27'));
        $archivePurger->setYesterdayDate(Date::factory('2015-02-26'));
        $archivePurger->setNow(Date::factory('2015-02-27 08:00:00')->getTimestamp());

        $this->tasks = new Tasks($archivePurger, new NullLogger());
    }

    public function tearDown()
    {
        unset($_GET['trigger']);

        parent::tearDown();
    }

    public function test_purgeInvalidatedArchives_PurgesCorrectInvalidatedArchives_AndOnlyPurgesDataForDatesAndSites_InInvalidatedReportsDistributedList()
    {
        $this->setUpInvalidatedReportsDistributedList($dates = array($this->february));

        $this->tasks->purgeInvalidatedArchives();

        self::$fixture->assertInvalidatedArchivesPurged($this->february);
        self::$fixture->assertInvalidatedArchivesNotPurged($this->january);

        // assert invalidated reports distributed list has changed
        $archivesToPurgeDistributedList = new ArchivesToPurgeDistributedList();
        $yearMonths = $archivesToPurgeDistributedList->getAll();

        $this->assertEmpty($yearMonths);
    }

    public function test_purgeOutdatedArchives_SkipsPurging_WhenBrowserArchivingDisabled_AndCronArchiveTriggerNotPresent()
    {
        Rules::setBrowserTriggerArchiving(false);
        unset($_GET['trigger']);

        $wasPurged = $this->tasks->purgeOutdatedArchives();
        $this->assertFalse($wasPurged);
    }

    public function test_purgeOutdatedArchives_Purges_WhenBrowserArchivingEnabled_AndCronArchiveTriggerPresent()
    {
        Rules::setBrowserTriggerArchiving(false);
        $_GET['trigger'] = 'archivephp';

        $wasPurged = $this->tasks->purgeOutdatedArchives();
        $this->assertTrue($wasPurged);
    }

    /**
     * @param Date[] $dates
     */
    private function setUpInvalidatedReportsDistributedList($dates)
    {
        $yearMonths = array();
        foreach ($dates as $date) {
            $yearMonths[] = $date->toString('Y_m');
        }

        $archivesToPurgeDistributedList = new ArchivesToPurgeDistributedList();
        $archivesToPurgeDistributedList->add($yearMonths);
    }
}

TasksTest::$fixture = new RawArchiveDataWithTempAndInvalidated();