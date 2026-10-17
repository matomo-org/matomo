<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updates;

use Piwik\Config;
use Piwik\CronArchive\ReArchiveList;
use Piwik\Piwik;
use Piwik\Plugins\SegmentEditor\Model as SegmentEditorModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;
use Piwik\Updates\Updates_6_0_0_b7;

require_once __DIR__ . '/../../../../core/Updates/6.0.0-b7.php';

/**
 * @group Updates
 */
class Updates600b7Test extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2015-01-01 00:00:00');
        Fixture::createWebsite('2015-01-01 00:00:00');
    }

    public function testOnlySavedSegmentsExcludingAContinentAreRearchived(): void
    {
        $notEurope = $this->createSegment('continentCode!=eur', 0);
        $notSouthAmerica = $this->createSegment('continentCode%21%3Dams', 2);
        $this->createSegment('continentCode==eur', 0);
        $this->createSegment('countryCode!=fr', 0);
        $this->createSegment('continentCode!=asi', 0, $deleted = 1);

        $startDate = Piwik::getEarliestDateToRearchive()->toString();
        $update = new Updates_6_0_0_b7();
        $hints = array_map('strval', $update->getMigrations(new Updater()));

        // the migration does not order the segments it reads
        self::assertEqualsCanonicalizing([
            "./console core:invalidate-report-data --sites=all --dates=$startDate,today --segment=$notEurope",
            "./console core:invalidate-report-data --sites=2 --dates=$startDate,today --segment=$notSouthAmerica",
        ], $hints);

        $update->doUpdate(new Updater());

        $reArchives = array_map(function ($item) {
            return json_decode($item, true);
        }, (new ReArchiveList())->getAll());
        usort($reArchives, function ($a, $b) {
            return strcmp($a['segment'], $b['segment']);
        });

        self::assertSame([
            ['idSites' => 'all', 'pluginName' => null, 'report' => null, 'startDate' => null, 'segment' => 'continentCode!=eur'],
            ['idSites' => [2], 'pluginName' => null, 'report' => null, 'startDate' => null, 'segment' => 'continentCode%21%3Dams'],
        ], $reArchives);
    }

    public function testNothingIsRearchivedWhenRearchivingIsTurnedOff(): void
    {
        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = 0;
        $this->createSegment('continentCode!=eur', 0);

        self::assertSame([], (new Updates_6_0_0_b7())->getMigrations(new Updater()));
    }

    private function createSegment(string $definition, int $idSite, int $deleted = 0): int
    {
        return (int) (new SegmentEditorModel())->createSegment([
            'name' => $definition,
            'definition' => $definition,
            'login' => 'superUserLogin',
            'enable_all_users' => 1,
            'enable_only_idsite' => $idSite,
            'auto_archive' => 1,
            'ts_created' => '2015-01-01 00:00:00',
            'deleted' => $deleted,
        ]);
    }
}
