<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Integration\PluginPromotions;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ActiveSitesCount;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ArchivedReportReader;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The counting behind the Crash Analytics and Roll-Up promotions: which websites count,
 * how many are looked at, and that a second promotion asking the same question costs
 * nothing.
 *
 * @group ProfessionalServices
 * @group PluginPromotions
 * @group Plugins
 */
class ActiveSitesCountTest extends IntegrationTestCase
{
    /** @var int[] the site ids the reader was asked about */
    private array $requestedSites = [];

    /** @var int number of times the reader was asked anything at all */
    private int $reads = 0;

    /** @var array<int, int> visits to report per site id */
    private array $visitsPerSite = [];

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'alice';
        $this->requestedSites = [];
        $this->reads = 0;
        $this->visitsPerSite = [];
    }

    public function testItDoesNotCountWhenTheUserCannotSeeEnoughWebsites(): void
    {
        FakeAccess::$idSitesView = [1, 2, 3, 4];
        $this->visitsPerSite = [1 => 900, 2 => 900, 3 => 900, 4 => 900];

        $this->assertSame(0, $this->makeCounter()->countQualifyingSites());
        $this->assertSame(0, $this->reads, 'below the floor there is nothing worth reading an archive for');
    }

    public function testOnlyWebsitesOverTheVisitFloorCount(): void
    {
        FakeAccess::$idSitesView = [1, 2, 3, 4, 5, 6];
        $this->visitsPerSite = [
            1 => 100,   // exactly the floor, counts
            2 => 99,    // just under, does not
            3 => 5000,
            4 => 0,
            5 => 250,
            6 => 100,
        ];

        $this->assertSame(4, $this->makeCounter()->countQualifyingSites());
    }

    /**
     * The old implementation stopped as soon as five websites qualified and reported five,
     * whatever the real number was. The copy names that number, so it has to be the real
     * one.
     */
    public function testItReportsEveryQualifyingWebsiteRatherThanStoppingAtTheThreshold(): void
    {
        FakeAccess::$idSitesView = range(1, 9);
        $this->visitsPerSite = array_fill_keys(range(1, 9), 500);

        $this->assertSame(9, $this->makeCounter()->countQualifyingSites());
    }

    public function testItNeverLooksAtMoreThanTheInspectionCap(): void
    {
        FakeAccess::$idSitesView = range(1, ActiveSitesCount::MAXIMUM_SITES_INSPECTED + 25);
        $this->visitsPerSite = array_fill_keys(FakeAccess::$idSitesView, 500);

        $counter = $this->makeCounter();

        $this->assertSame(ActiveSitesCount::MAXIMUM_SITES_INSPECTED, $counter->countQualifyingSites());
        $this->assertCount(ActiveSitesCount::MAXIMUM_SITES_INSPECTED, $this->requestedSites);
    }

    /**
     * Two promotions ask this question on every dashboard. The second must be free, which
     * is the whole reason the counting moved out of the triggers.
     */
    public function testAskingTwiceReadsTheArchivesOnce(): void
    {
        FakeAccess::$idSitesView = [1, 2, 3, 4, 5, 6];
        $this->visitsPerSite = array_fill_keys([1, 2, 3, 4, 5, 6], 500);

        $counter = $this->makeCounter();

        $this->assertSame(6, $counter->countQualifyingSites());
        $this->assertSame(6, $counter->countQualifyingSites());
        $this->assertSame(1, $this->reads);
    }

    /**
     * The answer depends on which websites the user may see, so it must not be handed to
     * a user with different access.
     */
    public function testADifferentSetOfWebsitesIsCountedAgain(): void
    {
        $this->visitsPerSite = array_fill_keys(range(1, 12), 500);
        $counter = $this->makeCounter();

        FakeAccess::$idSitesView = [1, 2, 3, 4, 5, 6];
        $this->assertSame(6, $counter->countQualifyingSites());

        FakeAccess::$idSitesView = [7, 8, 9, 10, 11, 12];
        $this->assertSame(6, $counter->countQualifyingSites());

        $this->assertSame(2, $this->reads);
    }

    private function makeCounter(): ActiveSitesCount
    {
        $reader = $this->createMock(ArchivedReportReader::class);

        $reader->method('buildArchiveForSites')->willReturnCallback(
            function (array $idSites) {
                $this->reads++;
                $this->requestedSites = $idSites;

                return $this->makeArchiveReturning($idSites);
            }
        );

        return new ActiveSitesCount($reader);
    }

    /**
     * @param int[] $idSites
     */
    private function makeArchiveReturning(array $idSites): Archive
    {
        $map = new DataTable\Map();
        $map->setKeyName('idSite');

        foreach ($idSites as $idSite) {
            $table = new DataTable();
            $table->addRowFromSimpleArray(['nb_visits' => $this->visitsPerSite[$idSite] ?? 0]);
            $map->addTable($table, (string) $idSite);
        }

        $archive = $this->createMock(Archive::class);
        $archive->method('getDataTableFromNumeric')->willReturn($map);

        return $archive;
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
