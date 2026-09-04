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

    public function testTheEntryPagesTriggerBuildsNoArchive(): void
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
     * Control: the identical report request, made without going through the trigger, does
     * build an archive. Without this the tests above could pass simply because archiving
     * was never possible in the first place.
     */
    public function testTheSameReportRequestOutsideATriggerDoesBuildAnArchive(): void
    {
        $before = $this->getArchiveState();

        Request::processRequest('Actions.getEntryPageUrls', [
            'idSite' => self::IDSITE,
            'period' => ReportPeriod::PERIOD,
            'date' => ReportPeriod::DATE,
            'flat' => 1,
            'format_metrics' => 0,
        ], []);

        $this->assertNotSame($before, $this->getArchiveState());
    }

    /**
     * The other side of the guard: once the archive exists the trigger reads it, reports
     * the qualifying entry page, and still adds nothing to the archive tables.
     */
    public function testTheEntryPagesTriggerReadsAnArchiveThatAlreadyExists(): void
    {
        $this->trackBouncingVisitsOnAPopularEntryPage();

        // Build the archive the way a normal report request would.
        Request::processRequest('Actions.getEntryPageUrls', [
            'idSite' => self::IDSITE,
            'period' => ReportPeriod::PERIOD,
            'date' => ReportPeriod::DATE,
            'flat' => 1,
        ], []);

        $before = $this->getArchiveState();
        $this->assertNotSame([], $before, 'the archive must exist for this test to mean anything');

        $result = StaticContainer::get(BounceRateTrigger::class)->evaluate(self::IDSITE);

        $this->assertTrue($result->isTriggered());
        $this->assertStringContainsString('/pricing', $result->getContext()['url']);
        // 210 from this test plus the single /pricing visit tracked in setUp().
        $this->assertSame(211, $result->getContext()['entryVisits']);
        $this->assertSame(1.0, $result->getContext()['bounceRate']);

        $this->assertSame($before, $this->getArchiveState(), 'reading the report must add nothing');
    }

    /**
     * 210 single page visits on one entry page: above the visit threshold, and every visit
     * bounces.
     */
    private function trackBouncingVisitsOnAPopularEntryPage(): void
    {
        $tracker = Fixture::getTracker(self::IDSITE, '2026-08-18 08:00:00', true, true);
        $tracker->setUrl('http://example.org/pricing');

        for ($i = 0; $i < 210; $i++) {
            $tracker->setForceVisitDateTime(Date::factory('2026-08-18 08:00:00')->addPeriod($i, 'minute')->getDatetime());
            $tracker->setNewVisitorId();
            $tracker->setIp('10.10.' . (int) ($i / 250) . '.' . ($i % 250 + 1));
            Fixture::checkResponse($tracker->doTrackPageView('Pricing'));
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
     * @return array<string, int>
     */
    private function getArchiveState(): array
    {
        $state = [];

        foreach (ArchiveTableCreator::getTablesArchivesInstalled(null, true) as $table) {
            $state[$table] = (int) Db::fetchOne('SELECT COUNT(*) FROM ' . $table);
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
