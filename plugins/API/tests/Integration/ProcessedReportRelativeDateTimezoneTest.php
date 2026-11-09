<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\Archive\DataTableFactory;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\EventDispatcher;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugins\API\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
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

    /** Three days with visits, compared below with an eight-day range. */
    private const COMPARED_DATE = '2026-09-06,2026-09-08';

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

        // Both fall on Auckland's 2026-09-08 and on UTC's 2026-09-07.
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

    /**
     * @dataProvider getRelativeDateCombinations
     */
    public function testPrettyDateNamesTheDayTheRowsActuallyCoverOnARelativeDate(
        string $timezone,
        string $nowUtc,
        string $expectedDay,
        int $expectedVisits
    ): void {
        $idSite = $timezone === self::SITE_TIMEZONE ? $this->idSite : $this->createLosAngelesSite();
        Date::$now = (int) strtotime($nowUtc . ' UTC');

        $report = API::getInstance()->getProcessedReport(
            $idSite,
            'day',
            'yesterday',
            'VisitsSummary',
            'get'
        );

        self::assertSame($expectedVisits, $this->visitsInReport($report['reportData']));
        self::assertSame(
            $this->prettyDateFor($expectedDay),
            $report['prettyDate'],
            'prettyDate names a different day than the rows it is attached to.'
        );
    }

    public function getRelativeDateCombinations(): array
    {
        return [
            'Auckland Sep 9 01:00, a day ahead of UTC' => [self::SITE_TIMEZONE, self::NOW, self::AUCKLAND_YESTERDAY, 2],
            'Auckland Sep 8 21:00, same day as UTC' => [self::SITE_TIMEZONE, '2026-09-08 09:00:00', '2026-09-07', 1],
            'Los Angeles Sep 7 20:00, a day behind UTC' => ['America/Los_Angeles', '2026-09-08 03:00:00', '2026-09-06', 2],
            'Los Angeles Sep 8 13:00, same day as UTC' => ['America/Los_Angeles', '2026-09-08 20:00:00', '2026-09-07', 1],
        ];
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

    /** Sunday 13:00 in UTC is already Monday in Auckland, so the two zones pick different weeks. */
    public function testAWeekLastNIsLabelledOnTheSitesWeeks(): void
    {
        Date::$now = (int) strtotime('2026-09-13 13:00:00 UTC');

        $report = API::getInstance()->getProcessedReport(
            $this->idSite,
            'week',
            'last2',
            'VisitsSummary',
            'get'
        );

        self::assertSame(
            PeriodFactory::build('range', '2026-09-07,2026-09-20')->getLocalizedLongString(),
            $report['prettyDate']
        );
    }

    /** A relative range does not resolve "today" on the site's day, so the label follows the rows instead. */
    public function testARelativeRangeIsLabelledOnTheDaysItIsArchivedFor(): void
    {
        $report = API::getInstance()->getProcessedReport(
            $this->idSite,
            'range',
            'previous2',
            'VisitsSummary',
            'get'
        );

        self::assertSame(
            PeriodFactory::build('range', '2026-09-06,' . self::UTC_YESTERDAY)->getLocalizedLongString(),
            $report['prettyDate']
        );
    }

    public function testATableWithoutPeriodMetadataIsLabelledInTheSitesTimezone(): void
    {
        EventDispatcher::getInstance()->addObserver(
            'API.getProcessedReport.inner.after',
            function ($parameters, $dataTable) {
                $dataTable->deleteMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
            }
        );

        $report = API::getInstance()->getProcessedReport(
            $this->idSite,
            'day',
            'yesterday',
            'VisitsSummary',
            'get'
        );

        self::assertSame($this->prettyDateFor(self::AUCKLAND_YESTERDAY), $report['prettyDate']);
    }

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

    /** The archive selects rows in UTC for more than one site, even when they share a timezone. */
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

        // Keyed on the period label, only the last site's table survives, so site two needs a visit.
        $tracker = Fixture::getTracker($idSite2, '2026-09-07 02:00:00');
        Fixture::checkResponse($tracker->doTrackPageView('/site-two-utc-yesterday'));

        $report = API::getInstance()->getProcessedReport(
            $this->idSite . ',' . $idSite2,
            'day',
            'yesterday',
            'VisitsSummary',
            'get'
        );

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
     * MultiSites.getAll selects every viewable site, so a one-site request resolves relative dates
     * in UTC while each site's rows still cover its own days.
     *
     * @param string[] $expectedDates
     * @dataProvider getMultiSitesDates
     */
    public function testAModuleThatSelectsItsOwnSiteSetIsLabelledOnTheTimezoneTheRowsUse(
        string $date,
        string $expectedRange,
        array $expectedDates
    ): void {
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

        // On UTC's yesterday, so the UTC-resolved day has data.
        $tracker = Fixture::getTracker($idSite2, '2026-09-07 02:00:00');
        Fixture::checkResponse($tracker->doTrackPageView('/site-two-utc-yesterday'));

        $report = API::getInstance()->getProcessedReport(
            $this->idSite,
            'day',
            $date,
            'MultiSites',
            'getAll'
        );

        if (count($expectedDates) > 1) {
            self::assertInstanceOf(DataTable\Map::class, $report['reportData']);
            self::assertEqualsCanonicalizing(
                array_map([$this, 'prettyDateFor'], $expectedDates),
                array_keys($report['reportData']->getDataTables())
            );
        }

        self::assertSame(
            PeriodFactory::build('range', $expectedRange)->getLocalizedLongString(),
            $report['prettyDate'],
            'prettyDate took the named site\'s timezone for rows the module selected in UTC.'
        );
    }

    public function getMultiSitesDates(): array
    {
        return [
            ['yesterday', '2026-09-07,2026-09-07', ['2026-09-07']],
            ['last2', '2026-09-07,2026-09-08', ['2026-09-07', '2026-09-08']],
        ];
    }

    public function testALongerComparisonRangeDoesNotWidenTheLabel(): void
    {
        $report = $this->getProcessedReportComparedWithALongerRange();

        self::assertSame(
            PeriodFactory::build('range', self::COMPARED_DATE)->getLocalizedLongString(),
            $report['prettyDate']
        );
    }

    /** @return array<string, mixed> */
    private function getProcessedReportComparedWithALongerRange(): array
    {
        $_GET['compare'] = '1';
        $_GET['compareDates'] = ['2026-09-01,2026-09-08'];
        $_GET['comparePeriods'] = ['day'];

        try {
            $report = API::getInstance()->getProcessedReport(
                $this->idSite,
                'day',
                self::COMPARED_DATE,
                'VisitsSummary',
                'get'
            );
        } finally {
            unset($_GET['compare'], $_GET['compareDates'], $_GET['comparePeriods']);
        }

        self::assertInstanceOf(DataTable\Map::class, $report['reportData']);
        self::assertCount(
            8,
            $report['reportData']->getDataTables(),
            'The comparison did not append tables for the days after the requested ones.'
        );

        return $report;
    }

    /** @param DataTable|DataTable\Map $reportData */
    private function visitsInReport($reportData): int
    {
        self::assertInstanceOf(DataTable::class, $reportData);

        $row = $reportData->getFirstRow();
        self::assertNotFalse($row, 'The report holds no rows, so prettyDate describes no data.');

        return (int) $row->getColumn('nb_visits');
    }

    /**
     * @param DataTable|DataTable\Map $reportData
     * @return array<string, int>
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

    /** UTC-7 in September: two visits on its 2026-09-06 and one on its 2026-09-07. */
    private function createLosAngelesSite(): int
    {
        $idSite = Fixture::createWebsite(
            '2026-09-01 00:00:00',
            0,
            'Timezone probe behind UTC',
            false,
            1,
            null,
            null,
            'America/Los_Angeles'
        );

        foreach (['2026-09-06 17:00:00', '2026-09-06 18:00:00', '2026-09-07 17:00:00'] as $when) {
            $tracker = Fixture::getTracker($idSite, $when);
            Fixture::checkResponse($tracker->doTrackPageView('/los-angeles'));
        }

        return $idSite;
    }

    private function prettyDateFor(string $date): string
    {
        return PeriodFactory::build('day', $date)->getLocalizedLongString();
    }
}
