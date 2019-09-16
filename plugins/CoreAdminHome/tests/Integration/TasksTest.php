<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\Archive\ArchivePurger;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Mail;
use Piwik\Plugins\CoreAdminHome\Emails\JsTrackingCodeMissingEmail;
use Piwik\Plugins\CoreAdminHome\Emails\TrackingFailuresEmail;
use Piwik\Plugins\CoreAdminHome\Tasks;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\SegmentEditor\Model;
use Piwik\Scheduler\Task;
use Piwik\Tests\Fixtures\RawArchiveDataWithTempAndInvalidated;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Failures;
use Piwik\Tracker\Request;
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

    /**
     * @var Mail
     */
    private $mail;

    public function setUp()
    {
        parent::setUp();

        self::$fixture->loginAsSuperUser();

        $this->january = Date::factory('2015-01-01');
        $this->february = Date::factory('2015-02-01');

        $archivePurger = new ArchivePurger();
        $archivePurger->setTodayDate(Date::factory('2015-02-27'));
        $archivePurger->setYesterdayDate(Date::factory('2015-02-26'));
        $archivePurger->setNow(Date::factory('2015-02-27 08:00:00')->getTimestamp());

        $this->tasks = new Tasks($archivePurger, new NullLogger(), new Failures());

        $this->mail = null;
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

    public function test_schedule_addsRightAmountOfTasks()
    {
        Fixture::createWebsite('2012-01-01 00:00:00');
        Fixture::createWebsite(Date::now()->subDay(5)->getDatetime());
        Fixture::createWebsite(Date::now()->subDay(2)->getDatetime());
        Fixture::createWebsite(Date::now()->subDay(4)->getDatetime());
        Fixture::createWebsite(Date::now()->getDatetime());

        $this->tasks->schedule();

        $tasks = $this->tasks->getScheduledTasks();
        $tasks = array_map(function (Task $task) { return $task->getMethodName() . '.' . $task->getMethodParameter(); }, $tasks);

        $expected = [
            'purgeOutdatedArchives.',
            'purgeInvalidatedArchives.',
            'purgeOrphanedArchives.',
            'optimizeArchiveTable.',
            'cleanupTrackingFailures.',
            'notifyTrackingFailures.',
            'updateSpammerBlacklist.',
            'checkSiteHasTrackedVisits.2',
            'checkSiteHasTrackedVisits.3',
            'checkSiteHasTrackedVisits.4',
            'checkSiteHasTrackedVisits.5',
        ];
        $this->assertEquals($expected, $tasks);
    }

    public function test_checkSiteHasTrackedVisits_doesNothingIfTheSiteHasVisits()
    {
        $idSite = Fixture::createWebsite('2012-01-01 00:00:00');

        $tracker = Fixture::getTracker($idSite, '2014-02-02 03:04:05');
        Fixture::checkResponse($tracker->doTrackPageView('alskdjfs'));

        $this->assertEquals(1, Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('log_visit')));

        $this->tasks->checkSiteHasTrackedVisits($idSite);

        $this->assertEmpty($this->mail);
    }

    public function test_checkSiteHasTrackedVisits_doesNothingIfSiteHasNoCreationUser()
    {
        $idSite = Fixture::createWebsite('2012-01-01 00:00:00');
        Db::query("UPDATE " . Common::prefixTable('site') . ' SET creator_login = NULL WHERE idsite = ' . $idSite . ';');

        $this->assertEquals(0, Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('log_visit')));

        $this->tasks->checkSiteHasTrackedVisits($idSite);

        $this->assertEmpty($this->mail);
    }

    public function test_checkSitesHasTrackedVisits_sendsJsCodeMissingEmailIfSiteHasNoVisitsAndCreationUser()
    {
        $idSite = Fixture::createWebsite('2012-01-01 00:00:00');

        $this->assertEquals(0, Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('log_visit')));

        $this->tasks->checkSiteHasTrackedVisits($idSite);

        $this->assertInstanceOf(JsTrackingCodeMissingEmail::class, $this->mail);

        /** @var JsTrackingCodeMissingEmail $mail */
        $mail = $this->mail;
        $this->assertEquals($mail->getLogin(), 'superUserLogin');
        $this->assertEquals($mail->getEmailAddress(), 'hello@example.org');
        $this->assertEquals($mail->getIdSite(), $idSite);
    }

    public function test_cleanupTrackingFailures_doesNotCauseAnyException()
    {
        // it is only calling one method which is already tested... no need to write complex tests for it
        $this->tasks->cleanupTrackingFailures();
        $this->assertTrue(true);
    }

    public function test_notifyTrackingFailures_doesNotSendAnyMailWhenThereAreNoTrackingRequests()
    {
        $this->tasks->notifyTrackingFailures();
        $this->assertNull($this->mail);
    }

    public function test_notifyTrackingFailures_sendsMailWhenThereAreTrackingFailures()
    {
        $failures = new Failures();
        $failures->logFailure(1, new Request(array('idsite' => 9999, 'rec' => 1)));
        $failures->logFailure(1, new Request(array('idsite' => 9998, 'rec' => 1)));
        Fixture::createSuperUser(false);
        $this->tasks->notifyTrackingFailures();

        /** @var TrackingFailuresEmail $mail */
        $mail = $this->mail;
        $this->assertInstanceOf(TrackingFailuresEmail::class, $mail);
        $this->assertEquals('superUserLogin', $mail->getLogin());
        $this->assertEquals('hello@example.org', $mail->getEmailAddress());
        $this->assertEquals(2, $mail->getNumFailures());
    }

    public function test_getSegmentHashesByIdSite_emptyWhenNoSegments()
    {
        $segmentsByIdSite = $this->tasks->getSegmentHashesByIdSite();
        $this->assertEquals(array(), $segmentsByIdSite);
    }

    public function test_getSegmentHashesByIdSite_allWebsiteAndSiteSpecificSegments()
    {
        $model = new Model();
        $model->createSegment(array(
            'name' => 'Test Segment 1',
            'definition' => 'continentCode==eur',
            'enable_only_idsite' => 0,
            'deleted' => 0
        ));
        $model->createSegment(array(
            'name' => 'Test Segment 2',
            'definition' => 'countryCode==nz',
            'enable_only_idsite' => 0,
            'deleted' => 0
        ));
        $model->createSegment(array(
            'name' => 'Test Segment 3',
            'definition' => 'countryCode==au',
            'enable_only_idsite' => 2,
            'deleted' => 0
        ));

        $segmentsByIdSite = $this->tasks->getSegmentHashesByIdSite();
        $expected = array(
            0 => array('be90051048558489e1d62f4245a6dc65', 'b92fbb3009b32cf632965802de2fb760'),
            2 => array('cffd4336c22c6782211f853495076b1a')
        );
        $this->assertEquals($expected, $segmentsByIdSite);
    }

    public function test_getSegmentHashesByIdSite_deletedSegment()
    {
        $model = new Model();
        $model->createSegment(array(
            'name' => 'Test Segment 1',
            'definition' => 'continentCode==eur',
            'enable_only_idsite' => 0,
            'deleted' => 0
        ));
        $model->createSegment(array(
            'name' => 'Test Segment 2',
            'definition' => 'countryCode==nz',
            'enable_only_idsite' => 0,
            'deleted' => 1
        ));
        $model->createSegment(array(
            'name' => 'Test Segment 3',
            'definition' => 'countryCode==au',
            'enable_only_idsite' => 2,
            'deleted' => 0
        ));

        $segmentsByIdSite = $this->tasks->getSegmentHashesByIdSite();
        $expected = array(
            0 => array('be90051048558489e1d62f4245a6dc65'),
            2 => array('cffd4336c22c6782211f853495076b1a')
        );
        $this->assertEquals($expected, $segmentsByIdSite);
    }

    public function test_getSegmentHashesByIdSite_invalidSegment()
    {
        $model = new Model();
        $model->createSegment(array(
            'name' => 'Test Segment 4',
            'definition' => 'countryCode=nz',   //The single "=" is invalid - we should generate a hash anyway
            'enable_only_idsite' => 0,
            'deleted' => 0
        ));
        $model->createSegment(array(
            'name' => 'Test Segment 5',
            'definition' => 'countryCode==au',
            'enable_only_idsite' => 0,
            'deleted' => 0
        ));

        $expected = array(
            0 => array('5ffe7e116fae7576c047b1fb811584a5', 'cffd4336c22c6782211f853495076b1a'),
        );

        $segmentsByIdSite = $this->tasks->getSegmentHashesByIdSite();
        $this->assertEquals($expected, $segmentsByIdSite);
    }

    public function test_getSegmentHashesByIdSite_siteSpecificCustomDimension()
    {
        // Insert a custom dimension for idsite = 1
        $configuration = new Configuration();
        $configuration->configureNewDimension(
            1, 
            'mydimension', 
            CustomDimensions::SCOPE_VISIT, 
            1, 
            1, 
            array(), 
            true
        );

        $model = new Model();
        $model->createSegment(array(
            'name' => 'Test Segment 6',
            'definition' => 'mydimension==red',
            'enable_only_idsite' => 1,
            'deleted' => 0
        ));
        $model->createSegment(array(
            'name' => 'Test Segment 7',
            'definition' => 'countryCode==au',
            'enable_only_idsite' => 2,
            'deleted' => 0
        ));

        $expected = array(
            1 => array('240d2a84a309debd26bdbaa8eb3d363c'),
            2 => array('cffd4336c22c6782211f853495076b1a')
        );

        $segmentsByIdSite = $this->tasks->getSegmentHashesByIdSite();
        $this->assertEquals($expected, $segmentsByIdSite);
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

    public function provideContainerConfig()
    {
        return [
            'observers.global' => \DI\add([
                ['Mail.send', function (Mail $mail) {
                    $this->mail = $mail;
                }],
            ]),
        ];
    }

    /**
     * @param Fixture $fixture
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}

TasksTest::$fixture = new RawArchiveDataWithTempAndInvalidated();