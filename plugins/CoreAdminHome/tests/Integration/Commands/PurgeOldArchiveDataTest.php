<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Archive\ArchivePurger;
use Piwik\Console;
use Piwik\Date;
use Piwik\Plugins\CoreAdminHome\Commands\PurgeOldArchiveData;
use Piwik\Tests\Fixtures\RawArchiveDataWithTempAndInvalidated;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @group Core
 */
class PurgeOldArchiveDataTest extends IntegrationTestCase
{
    /**
     * @var RawArchiveDataWithTempAndInvalidated
     */
    public static $fixture = null;

    /**
     * @var ApplicationTester
     */
    protected $applicationTester = null;

    /**
     * @var Console
     */
    protected $application;

    public function setUp(): void
    {
        parent::setUp();

        PurgeOldArchiveData::$todayOverride = Date::factory('2015-02-27');

        $archivePurger = new ArchivePurger();
        $archivePurger->setTodayDate(Date::factory('2015-02-27'));
        $archivePurger->setYesterdayDate(Date::factory('2015-02-26'));
        $archivePurger->setNow(Date::factory('2015-02-27 08:00:00')->getTimestamp());

        $this->application = new Console();
        $this->application->setAutoExit(false);
        $this->application->add(new PurgeOldArchiveData($archivePurger));

        $this->applicationTester = new ApplicationTester($this->application);

        // assert the test data was setup correctly
        self::$fixture->assertInvalidatedArchivesNotPurged(self::$fixture->january);
        self::$fixture->assertInvalidatedArchivesNotPurged(self::$fixture->february);
    }

    public function tearDown(): void
    {
        PurgeOldArchiveData::$todayOverride = null;

        parent::tearDown();
    }

    public function testExecutingCommandWithAllDatesPurgesAllExistingArchiveTables()
    {
        $result = $this->applicationTester->run(array(
            'command' => 'core:purge-old-archive-data',
            'dates' => array('all'),
            '-vvv' => true
        ));

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        self::$fixture->assertInvalidatedArchivesPurged(self::$fixture->february);
        self::$fixture->assertTemporaryArchivesPurged($isBrowserTriggeredArchivingEnabled = true, self::$fixture->february);
        self::$fixture->assertCustomRangesPurged(self::$fixture->february);

        self::$fixture->assertInvalidatedArchivesPurged(self::$fixture->january);
        self::$fixture->assertTemporaryArchivesPurged($isBrowserTriggeredArchivingEnabled = true, self::$fixture->january);
        self::$fixture->assertCustomRangesPurged(self::$fixture->january);
    }

    public function testExecutingCommandWithNoDatePurgesArchiveTableForToday()
    {
        $result = $this->applicationTester->run(array(
            'command' => 'core:purge-old-archive-data',
            '-vvv' => true
        ));

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        self::$fixture->assertInvalidatedArchivesPurged(self::$fixture->february);
        self::$fixture->assertTemporaryArchivesPurged($isBrowserTriggeredArchivingEnabled = true, self::$fixture->february);
        self::$fixture->assertCustomRangesPurged(self::$fixture->february);

        self::$fixture->assertInvalidatedArchivesNotPurged(self::$fixture->january);
        self::$fixture->assertTemporaryArchivesNotPurged(self::$fixture->january);
        self::$fixture->assertCustomRangesNotPurged(self::$fixture->january);
    }

    public function testExecutingCommandWithSpecificDatePurgesArchiveTableForDate()
    {
        $result = $this->applicationTester->run(array(
            'command' => 'core:purge-old-archive-data',
            'dates' => array('2015-01-14'),
            '-vvv' => true
        ));

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        self::$fixture->assertInvalidatedArchivesPurged(self::$fixture->january);
        self::$fixture->assertTemporaryArchivesPurged($isBrowserTriggeredArchivingEnabled = true, self::$fixture->january);
        self::$fixture->assertCustomRangesPurged(self::$fixture->january);

        self::$fixture->assertInvalidatedArchivesNotPurged(self::$fixture->february);
        self::$fixture->assertTemporaryArchivesNotPurged(self::$fixture->february);
        self::$fixture->assertCustomRangesNotPurged(self::$fixture->february);
    }

    public function testExecutingCommandWithExcludeOptionsSkipsAppropriatePurging()
    {
        $result = $this->applicationTester->run(array(
            'command' => 'core:purge-old-archive-data',
            'dates' => array('2015-01-14'),
            '--exclude-outdated' => true,
            '--exclude-invalidated' => true,
            '--exclude-ranges' => true,
            '--skip-optimize-tables' => true,
            '-vvv' => true
        ));

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        self::$fixture->assertInvalidatedArchivesNotPurged(self::$fixture->january);
        self::$fixture->assertTemporaryArchivesNotPurged(self::$fixture->january);
        self::$fixture->assertCustomRangesNotPurged(self::$fixture->january);

        self::assertStringContainsString("Skipping purge outdated archive data.", $this->applicationTester->getDisplay());
        self::assertStringContainsString("Skipping purge invalidated archive data.", $this->applicationTester->getDisplay());
        self::assertStringContainsString("Skipping OPTIMIZE TABLES.", $this->applicationTester->getDisplay());
    }

    protected function getCommandDisplayOutputErrorMessage()
    {
        return "Command did not behave as expected. Command output: " . $this->applicationTester->getDisplay();
    }
}

PurgeOldArchiveDataTest::$fixture = new RawArchiveDataWithTempAndInvalidated();
