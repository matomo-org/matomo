<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Integration\PluginPromotions;

use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\BounceRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ReturningVisitorsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionRegistry;
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyApi;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Opening a dashboard must never be the reason an archive gets built. These tests pin that
 * down for the report based triggers, and include a control showing the very same report
 * request does build an archive when it is not made through the trigger.
 *
 * @group ProfessionalServices
 * @group PluginPromotions
 * @group Plugins
 */
class ArchiveIsNeverBuiltTest extends IntegrationTestCase
{
    private const IDSITE = 1;

    public function setUp(): void
    {
        parent::setUp();

        Date::$now = strtotime('2026-08-27 10:00:00 UTC');

        Fixture::createSuperUser();
        Fixture::createWebsite('2026-01-01 00:00:00');
        $this->trackVisitsInTheLastCompletedWeek();

        FakeAccess::$superUser = true;

        // Without this the assertion would pass for the wrong reason.
        Rules::setBrowserTriggerArchiving(true);
        $this->assertTrue(Rules::isBrowserTriggerEnabled());
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testThePageTitlesTriggerBuildsNoArchive(): void
    {
        $before = $this->getArchiveState();

        $result = StaticContainer::get(BounceRateTrigger::class)->evaluate(self::IDSITE);

        $this->assertFalse($result->isTriggered(), 'no archived data exists, so nothing can qualify');
        $this->assertSame('2026-08-17', $result->getPeriodStart());
        $this->assertSame('2026-08-23', $result->getPeriodEnd());
        $this->assertSame($before, $this->getArchiveState());
    }

    public function testTheGoalTriggersBuildNoArchive(): void
    {
        $before = $this->getArchiveState();

        $this->assertFalse(StaticContainer::get(LowConversionRateTrigger::class)->evaluate(self::IDSITE)->isTriggered());
        $this->assertFalse(StaticContainer::get(HighConversionRateTrigger::class)->evaluate(self::IDSITE)->isTriggered());

        $this->assertSame($before, $this->getArchiveState());
    }

    /**
     * The Cohorts trigger wants the returning half of `VisitFrequency`, which lives in a
     * segment archive with a done flag of its own. Reading it through `VisitFrequency.get`
     * built that archive on a dashboard render, because the gate only ever saw the
     * unsegmented one. It now reads the segment archive directly, and must build nothing
     * whether or not that archive happens to exist.
     */
    public function testTheReturningVisitorsTriggerBuildsNoArchive(): void
    {
        $before = $this->getArchiveState();

        $result = StaticContainer::get(ReturningVisitorsTrigger::class)->evaluate(self::IDSITE);

        $this->assertFalse($result->isTriggered());
        $this->assertSame($before, $this->getArchiveState());
    }

    /**
     * And once the segment archive does exist, the trigger reads that record - the point
     * of reading the archive directly was to stop building it, not to count something
     * else. A known figure is written into the returning segment's own archive, so a
     * trigger reading any other record, or any other metric, reads something else.
     */
    public function testTheReturningVisitorsTriggerReadsTheSegmentArchiveVisitFrequencyBuilds(): void
    {
        // Build the archives a normal VisitFrequency request would, segment included.
        Request::processRequest('VisitFrequency.get', [
            'idSite' => self::IDSITE,
            'period' => ReportPeriod::PERIOD,
            'date' => ReportPeriod::DATE,
            'format_metrics' => 0,
        ], []);

        $this->setReturningUniqueVisitorsInArchive(640);

        $before = $this->getArchiveState();

        $result = StaticContainer::get(ReturningVisitorsTrigger::class)->evaluate(self::IDSITE);

        $this->assertTrue($result->isTriggered());
        $this->assertSame(640, $result->getContext()['count']);
        $this->assertSame($before, $this->getArchiveState(), 'reading the segment must add nothing');
    }

    /**
     * Writes `nb_uniq_visitors` into the archive of the segment VisitFrequency uses for
     * its returning half, which is the record the trigger is supposed to read.
     */
    private function setReturningUniqueVisitorsInArchive(int $value): void
    {
        $segment = new Segment(urldecode(VisitFrequencyApi::RETURNING_VISITOR_SEGMENT), [self::IDSITE]);
        $period = StaticContainer::get(ReportPeriod::class)->forSite(self::IDSITE);

        $table = ArchiveTableCreator::getNumericTable($period->getDateStart(), false);

        $idArchive = Db::fetchOne(
            'SELECT idarchive FROM ' . $table . " WHERE idsite = ? AND period = ? AND date1 = ? AND date2 = ?"
            . " AND name LIKE ? ORDER BY ts_archived DESC LIMIT 1",
            [
                self::IDSITE,
                $period->getId(),
                $period->getDateStart()->toString(),
                $period->getDateEnd()->toString(),
                'done' . $segment->getHash() . '%',
            ]
        );

        $this->assertNotEmpty($idArchive, 'the returning segment archive must exist for this test to mean anything');

        Db::query('DELETE FROM ' . $table . ' WHERE idarchive = ? AND name = ?', [$idArchive, 'nb_uniq_visitors']);
        Db::query(
            'INSERT INTO ' . $table . ' (idarchive, idsite, date1, date2, period, ts_archived, name, value)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $idArchive,
                self::IDSITE,
                $period->getDateStart()->toString(),
                $period->getDateEnd()->toString(),
                $period->getId(),
                Date::now()->getDatetime(),
                'nb_uniq_visitors',
                $value,
            ]
        );
    }

    /**
     * No trigger in the registry may build an archive, whatever it reads. The two tests
     * above pin down the ones with a history; this one covers the rest, and covers any
     * trigger added later without a test of its own.
     */
    public function testNoRegisteredTriggerBuildsAnArchive(): void
    {
        $before = $this->getArchiveState();

        foreach (StaticContainer::get(PromotionRegistry::class)->getAllByPriority() as $promotion) {
            $promotion->getTrigger()->evaluate(self::IDSITE);

            $this->assertSame(
                $before,
                $this->getArchiveState(),
                'the ' . $promotion->getTriggerName() . ' trigger built an archive'
            );
        }
    }

    /**
     * Archiving is skipped altogether for a website with no data, so there has to be
     * something worth archiving for these tests to mean anything.
     */
    private function trackVisitsInTheLastCompletedWeek(): void
    {
        $tracker = Fixture::getTracker(self::IDSITE, '2026-08-19 10:00:00', true, true);

        $tracker->setUrl('http://example.org/pricing');
        Fixture::checkResponse($tracker->doTrackPageView('Pricing'));

        $tracker->setForceVisitDateTime('2026-08-20 11:00:00');
        $tracker->setNewVisitorId();
        $tracker->setUrl('http://example.org/download');
        Fixture::checkResponse($tracker->doTrackPageView('Download'));
    }

    /**
     * Every archive row, identified by what would change if it were rebuilt.
     *
     * A plain row count is not enough. Re-archiving an existing period replaces the record
     * in place: the count is identical afterwards and only `idarchive` and `ts_archived`
     * move. A trigger that rebuilt the archives it was supposed to only read would have
     * passed a count-based assertion, which is exactly how that went unnoticed once.
     *
     * @return array<string, string>
     */
    private function getArchiveState(): array
    {
        $state = [];

        foreach (ArchiveTableCreator::getTablesArchivesInstalled(null, true) as $table) {
            $rows = Db::fetchAll(
                'SELECT idarchive, idsite, date1, date2, period, name, ts_archived FROM ' . $table
                . ' ORDER BY idarchive, name'
            );

            foreach ($rows as $row) {
                $key = $table . '|' . $row['idarchive'] . '|' . $row['name'];
                $state[$key] = $row['idsite'] . '|' . $row['date1'] . '|' . $row['date2']
                    . '|' . $row['period'] . '|' . $row['ts_archived'];
            }
        }

        return $state;
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
