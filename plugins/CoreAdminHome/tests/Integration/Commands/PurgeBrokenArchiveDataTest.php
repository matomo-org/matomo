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
use Piwik\Plugins\CoreAdminHome\Commands\PurgeBrokenArchiveData;
use Piwik\Tests\Fixtures\RawArchiveDataWithTempAndInvalidated;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @group Core
 */
class PurgeBrokenArchiveDataTest extends IntegrationTestCase
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

        Date::$now = Date::factory('2015-02-27')->getTimestamp();

        $archivePurger = new ArchivePurger();
        $archivePurger->setTodayDate(Date::factory('2015-02-27'));
        $archivePurger->setYesterdayDate(Date::factory('2015-02-26'));
        $archivePurger->setNow(Date::factory('2015-02-27 08:00:00')->getTimestamp());

        $this->application = new Console();
        $this->application->setAutoExit(false);
        $this->application->add(new PurgeBrokenArchiveData($archivePurger));

        $this->applicationTester = new ApplicationTester($this->application);

        // assert the test data was setup correctly
        self::$fixture->assertBrokenArchivesNotPurged(self::$fixture->january);
        self::$fixture->assertBrokenArchivesNotPurged(self::$fixture->february);
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testExecutingCommandDefaultDatesPurgesFromExistingMonth()
    {
        $result = $this->applicationTester->run([
            'command' => 'core:purge-broken-archive-data',
        ]);

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        self::$fixture->assertBrokenArchivesWithoutDoneFlagPurged(self::$fixture->february);
        self::$fixture->assertBrokenArchivesNotPurged(self::$fixture->january);
    }

    public function testExecutingCommandSpecificDatesPurgesOnlyInRange()
    {
        $result = $this->applicationTester->run([
            'command' => 'core:purge-broken-archive-data',
            'startMonth' => '2015-01',
            'endMonth' => '2015-02'
        ]);

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
        self::$fixture->assertBrokenArchivesWithoutDoneFlagPurged(self::$fixture->january);
        self::$fixture->assertBrokenArchivesWithoutDoneFlagPurged(self::$fixture->february);
    }

    public function testExecutingCommandInvalidDates()
    {
        $result = $this->applicationTester->run([
            'command' => 'core:purge-broken-archive-data',
            'startMonth' => '201-01',
        ]);

        $this->assertEquals(2, $result, $this->getCommandDisplayOutputErrorMessage());

        $result = $this->applicationTester->run([
            'command' => 'core:purge-broken-archive-data',
            'startMonth' => '2015-01',
            'endMonth' => '201-001'
        ]);

        $this->assertEquals(2, $result, $this->getCommandDisplayOutputErrorMessage());
    }

    protected function getCommandDisplayOutputErrorMessage()
    {
        return "Command did not behave as expected. Command output: " . $this->applicationTester->getDisplay();
    }
}

PurgeBrokenArchiveDataTest::$fixture = new RawArchiveDataWithTempAndInvalidated();
