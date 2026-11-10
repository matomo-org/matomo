<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updates;

use Piwik\Common;
use Piwik\Config;
use Piwik\CronArchive\ReArchiveList;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\Model as ArchiveModel;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsAPI;
use Piwik\Plugins\SegmentEditor\Model as SegmentEditorModel;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;
use Piwik\Updates\Updates_6_1_0_b1;

require_once __DIR__ . '/../../../../core/Updates/6.1.0-b1.php';

/**
 * @group Updates
 */
class Updates610b1Test extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Date::$now = strtotime('2026-03-20 12:00:00');

        Fixture::createWebsite('2015-01-01 00:00:00');
        Fixture::createWebsite('2015-01-01 00:00:00');
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testOnlySavedSegmentsWithAChangedContinentConditionAreRearchived(): void
    {
        $notEurope = $this->createSegment('continentCode!=eur', 0);
        $notSouthAmerica = $this->createSegment('continentCode%21%3Dams', 2);
        $notContainingAsia = $this->createSegment('continentCode!@asi', 0);
        $frenchNotInEurope = $this->createSegment('countryCode%3D%3Dfr%3BcontinentCode%21%3Deur', 0);
        $containingAm = $this->createSegment('continentCode=@am', 0);
        $unknown = $this->createSegment('continentCode==unk', 0);
        $europeOrNotAsia = $this->createSegment('continentCode==eur,continentCode!=asi', 0);
        $this->createSegment('continentCode==eur', 0);
        $this->createSegment('continentCode==EUR;continentCode==asi', 0);
        // an empty value selects the same visits as before
        $this->createSegment('continentCode!=', 0);
        $this->createSegment('continentCode==', 0);
        // saved again by other users, encoded or not, rearchived once
        $this->createSegment('continentCode!=eur', 0);
        $this->createSegment('continentCode%21%3Deur', 0);
        // not a valid segment, skipped
        $this->createSegment('continentCode', 0);
        $this->createSegment('eventAction==continentCode!=eur', 0);
        $this->createSegment('countryCode!=fr', 0);
        $this->createSegment('continentCode!=asi', 0, $deleted = 1);

        $update = new Updates_6_1_0_b1();
        $hints = array_map('strval', $update->getMigrations(new Updater()));

        self::assertSame([
            "./console core:invalidate-report-data --sites=all --dates=2026-03-01,today --segment=$notEurope",
            "./console core:invalidate-report-data --sites=2 --dates=2026-03-01,today --segment=$notSouthAmerica",
            "./console core:invalidate-report-data --sites=all --dates=2026-03-01,today --segment=$notContainingAsia",
            "./console core:invalidate-report-data --sites=all --dates=2026-03-01,today --segment=$frenchNotInEurope",
            "./console core:invalidate-report-data --sites=all --dates=2026-03-01,today --segment=$containingAm",
            "./console core:invalidate-report-data --sites=all --dates=2026-03-01,today --segment=$unknown",
            "./console core:invalidate-report-data --sites=all --dates=2026-03-01,today --segment=$europeOrNotAsia",
        ], $hints);

        $update->doUpdate(new Updater());

        $startDate = Date::factory('2026-03-01')->getTimestamp();
        self::assertSame([
            ['idSites' => 'all', 'pluginName' => null, 'report' => null, 'startDate' => $startDate, 'segment' => 'continentCode!=eur'],
            ['idSites' => [2], 'pluginName' => null, 'report' => null, 'startDate' => $startDate, 'segment' => 'continentCode%21%3Dams'],
            ['idSites' => 'all', 'pluginName' => null, 'report' => null, 'startDate' => $startDate, 'segment' => 'continentCode!@asi'],
            ['idSites' => 'all', 'pluginName' => null, 'report' => null, 'startDate' => $startDate, 'segment' => 'countryCode%3D%3Dfr%3BcontinentCode%21%3Deur'],
            ['idSites' => 'all', 'pluginName' => null, 'report' => null, 'startDate' => $startDate, 'segment' => 'continentCode=@am'],
            ['idSites' => 'all', 'pluginName' => null, 'report' => null, 'startDate' => $startDate, 'segment' => 'continentCode==unk'],
            ['idSites' => 'all', 'pluginName' => null, 'report' => null, 'startDate' => $startDate, 'segment' => 'continentCode==eur,continentCode!=asi'],
        ], $this->getReArchiveEntries());
    }

    public function testASegmentIsRearchivedFromWhereItsBackfillStarts(): void
    {
        Config::getInstance()->General['process_new_segments_from'] = 'segment_creation_time';
        $notEurope = $this->createSegment('continentCode!=eur', 0, 0, 1, '2026-03-10 08:00:00');
        // saved again earlier by another user, both are rearchived from the earliest date
        $this->createSegment('continentCode%21%3Deur', 0, 0, 1, '2026-03-05 08:00:00');
        // created before the current month, rearchived from its start
        $notAsia = $this->createSegment('continentCode!=asi', 0, 0, 1, '2026-02-01 08:00:00');
        // created today, nothing to rearchive
        $this->createSegment('continentCode!=ams', 0, 0, 1, '2026-03-20 08:00:00');

        $update = new Updates_6_1_0_b1();
        self::assertSame([
            "./console core:invalidate-report-data --sites=all --dates=2026-03-05,today --segment=$notEurope",
            "./console core:invalidate-report-data --sites=all --dates=2026-03-01,today --segment=$notAsia",
        ], array_map('strval', $update->getMigrations(new Updater())));

        $update->doUpdate(new Updater());

        self::assertSame([
            Date::factory('2026-03-05')->getTimestamp(),
            Date::factory('2026-03-01')->getTimestamp(),
        ], array_column($this->getReArchiveEntries(), 'startDate'));
    }

    public function testNothingIsRearchivedOnTheFirstDayOfTheMonth(): void
    {
        Date::$now = strtotime('2026-03-01 12:00:00');
        $this->createSegment('continentCode!=eur', 0);

        self::assertSame([], (new Updates_6_1_0_b1())->getMigrations(new Updater()));
    }

    public function testTheArchivesOfASegmentNotAutoArchivedAreOnlyInvalidated(): void
    {
        $this->createSegment('continentCode!=eur', 0, 0, $autoArchive = 0);
        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2026-03-10'), true);
        $idArchive = $this->insertDoneFlag($archiveTable, 'continentCode!=eur', '2026-03-10');

        $update = new Updates_6_1_0_b1();
        self::assertSame([''], array_map('strval', $update->getMigrations(new Updater())));

        $update->doUpdate(new Updater());

        self::assertSame([], (new ReArchiveList())->getAll());
        self::assertEquals(
            ArchiveWriter::DONE_INVALIDATED,
            Db::fetchOne("SELECT `value` FROM `$archiveTable` WHERE idarchive = ?", [$idArchive])
        );
        self::assertEquals(0, Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations')));
    }

    public function testASegmentNoLongerValidIsSkipped(): void
    {
        $this->createSegment('continentCode!=eur;segmentOfARemovedPlugin==x', 0);
        $this->createSegment('continentCode!=ams', 0);

        (new Updates_6_1_0_b1())->doUpdate(new Updater());

        self::assertSame(['continentCode!=ams'], array_column($this->getReArchiveEntries(), 'segment'));
    }

    public function testASegmentOfADeletedSiteIsSkipped(): void
    {
        // segments of a site deleted before Matomo 4 were left in place
        $this->createSegment('continentCode!=eur', 99);
        $notAsia = $this->createSegment('continentCode!=asi', 2);

        self::assertSame(
            ["./console core:invalidate-report-data --sites=2 --dates=2026-03-01,today --segment=$notAsia"],
            array_map('strval', (new Updates_6_1_0_b1())->getMigrations(new Updater()))
        );
    }

    public function testASegmentOfEverySiteValidOnlyOnSomeSitesIsRearchivedOnEachOfThem(): void
    {
        Fixture::createWebsite('2015-01-01 00:00:00');
        CustomDimensionsAPI::getInstance()->configureNewCustomDimension(1, 'Plan', 'visit', true);
        CustomDimensionsAPI::getInstance()->configureNewCustomDimension(3, 'Plan', 'visit', true);
        $this->createSegment('continentCode!=eur;dimension1==premium', 0);

        (new Updates_6_1_0_b1())->doUpdate(new Updater());

        self::assertSame([[1], [3]], array_column($this->getReArchiveEntries(), 'idSites'));
    }

    public function testNothingIsRearchivedWhenRearchivingIsTurnedOff(): void
    {
        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = 0;
        $this->createSegment('continentCode!=eur', 0);

        self::assertSame([], (new Updates_6_1_0_b1())->getMigrations(new Updater()));
    }

    private function createSegment(
        string $definition,
        int $idSite,
        int $deleted = 0,
        int $autoArchive = 1,
        string $tsCreated = '2015-01-01 00:00:00'
    ): int {
        return (int) (new SegmentEditorModel())->createSegment([
            'name' => $definition,
            'definition' => $definition,
            'login' => 'superUserLogin',
            'enable_all_users' => 1,
            'enable_only_idsite' => $idSite,
            'auto_archive' => $autoArchive,
            'ts_created' => $tsCreated,
            'deleted' => $deleted,
        ]);
    }

    private function getReArchiveEntries(): array
    {
        return array_map(function ($item) {
            return json_decode($item, true);
        }, (new ReArchiveList())->getAll());
    }

    private function insertDoneFlag(string $archiveTable, string $definition, string $date): int
    {
        $idArchive = (int) (new ArchiveModel())->allocateNewArchiveId($archiveTable);
        Db::query(
            "INSERT INTO `$archiveTable` (idarchive, idsite, date1, date2, period, ts_archived, name, value) VALUES (?, 1, ?, ?, 1, NOW(), ?, ?)",
            [$idArchive, $date, $date, 'done' . (new Segment($definition, []))->getHash(), ArchiveWriter::DONE_OK]
        );

        return $idArchive;
    }
}
