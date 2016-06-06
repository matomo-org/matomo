<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration\Archive;

use Piwik\Archive\ArchivePurger;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Tests\Fixtures\RawArchiveDataWithTempAndInvalidated;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ArchivePurgerTest
 * @group Core
 */
class ArchivePurgerTest extends IntegrationTestCase
{
    /**
     * @var RawArchiveDataWithTempAndInvalidated
     */
    public static $fixture;

    /**
     * @var ArchivePurger
     */
    private $archivePurger;

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

        $this->january = self::$fixture->january;
        $this->february = self::$fixture->february;

        $this->archivePurger = new ArchivePurger();
        $this->archivePurger->setTodayDate(Date::factory('2015-02-27'));
        $this->archivePurger->setYesterdayDate(Date::factory('2015-02-26'));
        $this->archivePurger->setNow(Date::factory('2015-02-27 08:00:00')->getTimestamp());

        $this->configureCustomRangePurging();

        // assert test data was added correctly
        self::$fixture->assertInvalidatedArchivesNotPurged(self::$fixture->january);
        self::$fixture->assertInvalidatedArchivesNotPurged(self::$fixture->february);
    }

    public function test_purgeOutdatedArchives_PurgesCorrectTemporaryArchives_WhileKeepingNewerTemporaryArchives_WithBrowserTriggeringEnabled()
    {
        $this->enableBrowserTriggeredArchiving();

        $deletedRowCount = $this->archivePurger->purgeOutdatedArchives($this->february);

        self::$fixture->assertTemporaryArchivesPurged($browserTriggeringEnabled = true, $this->february);

        self::$fixture->assertCustomRangesNotPurged($this->february, $includeTemporary = false);
        self::$fixture->assertTemporaryArchivesNotPurged($this->january);

        $this->assertEquals(7 * RawArchiveDataWithTempAndInvalidated::ROWS_PER_ARCHIVE, $deletedRowCount);
    }

    public function test_purgeOutdatedArchives_PurgesCorrectTemporaryArchives_WhileKeepingNewerTemporaryArchives_WithBrowserTriggeringDisabled()
    {
        $this->disableBrowserTriggeredArchiving();

        $deletedRowCount = $this->archivePurger->purgeOutdatedArchives($this->february);

        self::$fixture->assertTemporaryArchivesPurged($browserTriggeringEnabled = false, $this->february);

        self::$fixture->assertCustomRangesNotPurged($this->february);
        self::$fixture->assertTemporaryArchivesNotPurged($this->january);

        $this->assertEquals(5 * RawArchiveDataWithTempAndInvalidated::ROWS_PER_ARCHIVE, $deletedRowCount);
    }

    public function test_purgeInvalidatedArchivesFrom_PurgesAllInvalidatedArchives_AndMarksDatesAndSitesAsInvalidated()
    {
        $deletedRowCount = $this->archivePurger->purgeInvalidatedArchivesFrom($this->february);

        self::$fixture->assertInvalidatedArchivesPurged($this->february);
        self::$fixture->assertInvalidatedArchivesNotPurged($this->january);

        $this->assertEquals(4 * RawArchiveDataWithTempAndInvalidated::ROWS_PER_ARCHIVE, $deletedRowCount);
    }

    public function test_purgeArchivesWithPeriodRange_PurgesAllRangeArchives()
    {
        $deletedRowCount = $this->archivePurger->purgeArchivesWithPeriodRange($this->february);

        self::$fixture->assertCustomRangesPurged($this->february);
        self::$fixture->assertCustomRangesNotPurged($this->january);

        $this->assertEquals(3 * RawArchiveDataWithTempAndInvalidated::ROWS_PER_ARCHIVE, $deletedRowCount);
    }

    private function configureCustomRangePurging()
    {
        Config::getInstance()->General['purge_date_range_archives_after_X_days'] = 3;
    }

    private function enableBrowserTriggeredArchiving()
    {
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;
    }

    private function disableBrowserTriggeredArchiving()
    {
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
    }
}

ArchivePurgerTest::$fixture = new RawArchiveDataWithTempAndInvalidated();