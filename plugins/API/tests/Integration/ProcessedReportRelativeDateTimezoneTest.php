<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugins\API\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * getProcessedReport resolves a relative date twice: once for the rows, once for
 * prettyDate. Both have to resolve in the site's timezone, or the label names a
 * different day than the report counts. A relative range resolves an anchor day of
 * its own, which has to land in the site's timezone for the same reason.
 *
 * @group API
 * @group Plugins
 */
class ProcessedReportRelativeDateTimezoneTest extends IntegrationTestCase
{
    /** UTC+12 with no DST transition near NOW. */
    private const SITE_TIMEZONE = 'Pacific/Auckland';

    /** 01:00 the next day in Auckland, so the two zones disagree on "yesterday". */
    private const NOW = '2026-09-08 13:00:00';

    private const AUCKLAND_YESTERDAY = '2026-09-08';
    private const UTC_YESTERDAY = '2026-09-07';

    /** The seven days ending on Auckland's yesterday. */
    private const AUCKLAND_PREVIOUS7 = '2026-09-02,2026-09-08';

    private int $idSite;

    private ?int $originalNow = null;

    public function setUp(): void
    {
        parent::setUp();

        // Otherwise getLocalizedLongString() compares raw Intl_ keys.
        Fixture::loadAllTranslations();

        Fixture::createSuperUser(true);
        $this->idSite = Fixture::createWebsite(
            '2026-09-01 00:00:00',
            0,
            'Timezone probe',
            false,
            1,
            null,
            null,
            self::SITE_TIMEZONE
        );

        // Visit times are UTC: Auckland's 2026-09-08 starts at 2026-09-07 12:00 UTC,
        // so both of these fall on Auckland's yesterday and UTC's 2026-09-07.
        foreach (['2026-09-07 13:00:00', '2026-09-07 14:00:00'] as $when) {
            $tracker = Fixture::getTracker($this->idSite, $when);
            Fixture::checkResponse($tracker->doTrackPageView('/auckland-yesterday'));
        }

        // One on the Auckland day before, so the candidate days differ by count too.
        $tracker = Fixture::getTracker($this->idSite, '2026-09-06 13:00:00');
        Fixture::checkResponse($tracker->doTrackPageView('/auckland-day-before'));

        $this->originalNow = Date::$now;
        Date::$now = (int) strtotime(self::NOW . ' UTC');
    }

    public function tearDown(): void
    {
        Date::$now = $this->originalNow;

        parent::tearDown();
    }

    public function testTheTwoCandidateDaysHoldDifferentVisitCounts(): void
    {
        self::assertSame(2, $this->visitsOn(self::AUCKLAND_YESTERDAY));
        self::assertSame(1, $this->visitsOn(self::UTC_YESTERDAY));
    }

    public function testRelativeDateSelectsRowsInTheSitesTimezone(): void
    {
        self::assertSame(
            2,
            $this->visitsOn('yesterday'),
            'The archive resolved "yesterday" somewhere other than the site timezone.'
        );
    }

    public function testPrettyDateNamesTheDayTheRowsActuallyCoverOnARelativeDate(): void
    {
        $report = API::getInstance()->getProcessedReport(
            $this->idSite,
            'day',
            'yesterday',
            'VisitsSummary',
            'get'
        );

        self::assertSame(2, $this->visitsInReport($report['reportData']));
        self::assertSame(
            $this->prettyDateFor(self::AUCKLAND_YESTERDAY),
            $report['prettyDate'],
            'prettyDate names a different day than the rows it is attached to.'
        );
    }

    public function testAnAbsoluteDateIsLabelledConsistently(): void
    {
        $report = API::getInstance()->getProcessedReport(
            $this->idSite,
            'day',
            self::AUCKLAND_YESTERDAY,
            'VisitsSummary',
            'get'
        );

        self::assertSame(2, $this->visitsInReport($report['reportData']));
        self::assertSame($this->prettyDateFor(self::AUCKLAND_YESTERDAY), $report['prettyDate']);
    }

    /**
     * A relative range is measured back from a "today" of its own. The fixture holds 2 visits on
     * the site's yesterday and 1 the day before, so a window anchored a day early counts 1, not 3.
     */
    public function testARelativeRangeIsCountedAndLabelledOnTheSitesOwnDays(): void
    {
        $report = API::getInstance()->getProcessedReport(
            $this->idSite,
            'range',
            'previous7',
            'VisitsSummary',
            'get'
        );

        self::assertSame(3, $this->visitsInReport($report['reportData']));
        self::assertSame(
            PeriodFactory::build('range', self::AUCKLAND_PREVIOUS7)->getLocalizedLongString(),
            $report['prettyDate'],
            'previous7 did not end on the site\'s yesterday.'
        );
    }

    /** A comma date is a multiple period, so it stays labelled as a range. */
    public function testAMultiPeriodDateIsStillLabelledAsARange(): void
    {
        $report = API::getInstance()->getProcessedReport(
            $this->idSite,
            'day',
            self::UTC_YESTERDAY . ',' . self::AUCKLAND_YESTERDAY,
            'VisitsSummary',
            'get'
        );

        self::assertSame(
            [
                $this->prettyDateFor(self::UTC_YESTERDAY) => 1,
                $this->prettyDateFor(self::AUCKLAND_YESTERDAY) => 2,
            ],
            $this->visitsPerTable($report['reportData'])
        );
        self::assertSame(
            PeriodFactory::build('range', self::UTC_YESTERDAY . ',' . self::AUCKLAND_YESTERDAY)
                ->getLocalizedLongString(),
            $report['prettyDate']
        );
    }

    /**
     * Both sites share the site timezone, so this pins the rule rather than a disagreement
     * between them: the archive selects rows in UTC whenever the request names more than one
     * site, so the label has to stay in UTC too.
     */
    public function testAMultiSiteRequestIsLabelledInTheTimezoneTheRowsUse(): void
    {
        $idSite2 = Fixture::createWebsite(
            '2026-09-01 00:00:00',
            0,
            'Timezone probe two',
            false,
            1,
            null,
            null,
            self::SITE_TIMEZONE
        );

        // getProcessedReport re-keys the result on the period label, so every site's table
        // collides on one key and all but the last are dropped. Pre-existing, not fixed here.
        // Give site two a visit on the UTC-resolved day so the surviving table is non-empty.
        $tracker = Fixture::getTracker($idSite2, '2026-09-07 02:00:00');
        Fixture::checkResponse($tracker->doTrackPageView('/site-two-utc-yesterday'));

        $report = API::getInstance()->getProcessedReport(
            $this->idSite . ',' . $idSite2,
            'day',
            'yesterday',
            'VisitsSummary',
            'get'
        );

        // Assert the per-site counts here once that keying is fixed.
        foreach ($this->visitsPerTable($report['reportData']) as $label => $visits) {
            self::assertGreaterThan(0, $visits, "The report table \"$label\" counts no visits.");
        }

        self::assertSame(
            $this->prettyDateFor(self::UTC_YESTERDAY),
            $report['prettyDate'],
            'prettyDate took a site timezone for a request whose rows were selected in UTC.'
        );
    }

    /**
     * idSite does not decide the archive's scope for every module. MultiSites.getAll selects
     * over every site the user can view, so a request naming one site still aggregates in UTC,
     * and a label derived from that one site's timezone would name a day the rows do not cover.
     */
    public function testAModuleThatSelectsItsOwnSiteSetIsLabelledOnTheTimezoneTheRowsUse(): void
    {
        $idSite2 = Fixture::createWebsite(
            '2026-09-01 00:00:00',
            0,
            'Timezone probe two',
            false,
            1,
            null,
            null,
            self::SITE_TIMEZONE
        );

        // On UTC's yesterday, so the aggregate the UTC-resolved day covers is non-empty.
        $tracker = Fixture::getTracker($idSite2, '2026-09-07 02:00:00');
        Fixture::checkResponse($tracker->doTrackPageView('/site-two-utc-yesterday'));

        // One site named, two sites archived.
        $report = API::getInstance()->getProcessedReport(
            $this->idSite,
            'day',
            'yesterday',
            'MultiSites',
            'getAll'
        );

        self::assertSame(
            $this->prettyDateFor(self::UTC_YESTERDAY),
            $report['prettyDate'],
            'prettyDate took the named site\'s timezone for rows the module selected in UTC.'
        );
    }

    /**
     * A prettyDate assertion only says something when the report it labels holds rows.
     *
     * @param DataTable|DataTable\Map $reportData
     */
    private function visitsInReport($reportData): int
    {
        self::assertInstanceOf(DataTable::class, $reportData);

        $row = $reportData->getFirstRow();
        self::assertNotFalse($row, 'The report holds no rows, so prettyDate describes no data.');

        return (int) $row->getColumn('nb_visits');
    }

    /**
     * @param DataTable|DataTable\Map $reportData
     * @return int[] visits per table, keyed by the label the report data uses
     */
    private function visitsPerTable($reportData): array
    {
        self::assertInstanceOf(DataTable\Map::class, $reportData);

        $visits = [];
        foreach ($reportData->getDataTables() as $label => $table) {
            $row = $table->getFirstRow();
            self::assertNotFalse($row, "The report table \"$label\" holds no rows.");
            $visits[$label] = (int) $row->getColumn('nb_visits');
        }

        self::assertNotEmpty($visits, 'The report came back without a single table.');

        return $visits;
    }

    private function visitsOn(string $date): int
    {
        $table = Request::processRequest('VisitsSummary.getVisits', [
            'idSite' => $this->idSite,
            'period' => 'day',
            'date' => $date,
            'format' => 'original',
        ]);
        $row = $table->getFirstRow();

        return $row === false ? 0 : (int) $row->getColumn('nb_visits');
    }

    /** Built the way getProcessedReport builds it, so only the date is compared. */
    private function prettyDateFor(string $date): string
    {
        return PeriodFactory::build('day', $date)->getLocalizedLongString();
    }
}
