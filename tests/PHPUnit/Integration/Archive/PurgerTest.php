<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration\Archive;

use Piwik\Archive\Purger;
use Piwik\Config;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Tests\Fixtures\RawArchiveDataWithTempAndInvalidated;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class PurgerTest extends IntegrationTestCase
{
    /* TODO: things to test
     * - that archive purging is done daily (when called through cron archiving and through API request for scheduled task running) [SYSTEM TESTS]
     */

    /**
     * @var RawArchiveDataWithTempAndInvalidated
     */
    public static $fixture;

    /**
     * @var Purger
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

        $this->archivePurger = new Purger();
        $this->archivePurger->setTodayDate(Date::factory('2015-02-27'));
        $this->archivePurger->setYesterdayDate(Date::factory('2015-02-26'));
        $this->archivePurger->setNow(Date::factory('2015-02-27 08:00:00')->getTimestamp());

        $this->configureCustomRangePurging();
    }

    public function test_purgeOutdatedArchives_PurgesCorrectTemporaryArchives_AndRangeArchives_WhileKeepingNewerTemporaryArchives_WithBrowserTriggeringEnabled()
    {
        $this->enableBrowserTriggeredArchiving();

        $this->archivePurger->purgeOutdatedArchives($this->february);

        self::$fixture->assertFebruaryTemporaryArchivesPurged($browserTriggeringEnabled = true);
        self::$fixture->assertFebruaryCustomRangesPurged();

        self::$fixture->assertJanuaryTemporaryArchivesNotPurged();
    }

    public function test_purgeOutdatedArchives_PurgesCorrectTemporaryArchives_AndRangeArchives_WhileKeepingNewerTemporaryArchives_WithBrowserTriggeringDisabled()
    {
        $this->disableBrowserTriggeredArchiving();

        $this->archivePurger->purgeOutdatedArchives($this->february);

        self::$fixture->assertFebruaryTemporaryArchivesPurged($browserTriggeringEnabled = false);
        self::$fixture->assertFebruaryCustomRangesPurged();

        self::$fixture->assertJanuaryTemporaryArchivesNotPurged();
    }

    public function test_purgeInvalidatedArchivesFrom_PurgesAllInvalidatedArchives_AndMarksDatesAndSitesAsInvalidated()
    {
        $this->archivePurger->purgeInvalidatedArchivesFrom($this->february);

        self::$fixture->assertFebruaryInvalidatedArchivesPurged();
        self::$fixture->assertJanuaryInvalidatedArchivesNotPurged();
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

PurgerTest::$fixture = new RawArchiveDataWithTempAndInvalidated();