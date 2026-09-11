<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Integration\PluginPromotions;

use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\WeeklyGoalMetrics;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * End to end check that the goal based triggers read the right archived records for the
 * last completed week.
 *
 * Every goal here converts at most once per visit. Goals that allow several conversions
 * per visit get a *random* cache buster per conversion (see `Tracker\GoalManager`), and
 * colliding busters are silently dropped, so their conversion counts are not reproducible
 * and cannot be asserted on.
 *
 * That makes one of the two thresholds impractical to reach from a tracked fixture: the
 * funnels promotion wants 100 conversions from at most 3% of visits, which with one
 * conversion per visit needs more than 3,300 of them. Its threshold and selection are
 * covered by {@see \Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions\LowConversionRateTriggerTest}
 * instead; what is checked here is that it reads the archive and reaches the right verdict.
 *
 * @group ProfessionalServices
 * @group PluginPromotions
 * @group Plugins
 */
class GoalTriggersTest extends IntegrationTestCase
{
    private const IDSITE = 1;

    private const VISITS = 505;

    /**
     * Enough to clear the A/B testing promotion's 500 conversion floor, and converting on
     * nearly every visit puts the rate far above its 3% floor too.
     */
    private const SIGNUP_CONVERSIONS = 500;

    private const PURCHASE_CONVERSIONS = 5;

    private int $idSignupGoal;

    private int $idPurchaseGoal;

    public function setUp(): void
    {
        parent::setUp();

        Date::$now = strtotime('2026-08-27 10:00:00 UTC');

        Fixture::createSuperUser();
        Fixture::createWebsite('2026-01-01 00:00:00');
        FakeAccess::$superUser = true;

        $this->idSignupGoal = (int) GoalsApi::getInstance()->addGoal(self::IDSITE, 'Signup', 'manually', '', 'contains');
        $this->idPurchaseGoal = (int) GoalsApi::getInstance()->addGoal(self::IDSITE, 'Purchase', 'manually', '', 'contains');

        $this->trackWeekWithConversions();
        $this->buildArchive();
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testTheMetricsReaderReturnsLastWeeksFiguresForEveryConfiguredGoal(): void
    {
        $metrics = StaticContainer::get(WeeklyGoalMetrics::class)->read(self::IDSITE);

        $this->assertSame(self::VISITS, $metrics['siteVisits']);
        $this->assertCount(2, $metrics['goals']);

        $byId = array_column($metrics['goals'], null, 'idGoal');
        $this->assertSame(self::SIGNUP_CONVERSIONS, $byId[$this->idSignupGoal]['nbConversions']);
        $this->assertSame(self::PURCHASE_CONVERSIONS, $byId[$this->idPurchaseGoal]['nbConversions']);
        $this->assertSame('Purchase', $byId[$this->idPurchaseGoal]['name']);

        // One conversion per visit, so the two are equal here; they are separate metrics
        // and the conversion rate is derived from the converting visits.
        $this->assertSame(self::SIGNUP_CONVERSIONS, $byId[$this->idSignupGoal]['nbVisitsConverted']);
    }

    public function testTheAbTestingTriggerPicksTheGoalWithTheHighConversionRate(): void
    {
        $result = StaticContainer::get(HighConversionRateTrigger::class)->evaluate(self::IDSITE);

        $this->assertTrue($result->isTriggered());

        // Purchase converted five times, far below the 500 conversion floor.
        $this->assertSame($this->idSignupGoal, $result->getContext()['goalId']);
        $this->assertSame('Signup', $result->getContext()['goalName']);
        $this->assertSame(self::SIGNUP_CONVERSIONS, $result->getContext()['nbConversions']);
        $this->assertGreaterThan(0.03, $result->getContext()['conversionRate']);

        $this->assertSame('2026-08-17', $result->getPeriodStart());
        $this->assertSame('2026-08-23', $result->getPeriodEnd());
    }

    public function testTheFunnelsTriggerReadsTheArchiveAndFindsNoQualifyingGoal(): void
    {
        $result = StaticContainer::get(LowConversionRateTrigger::class)->evaluate(self::IDSITE);

        // Signup converts for almost every visit, well above the 3% ceiling, and Purchase
        // is below the 100 conversion floor.
        $this->assertFalse($result->isTriggered());
        $this->assertSame('2026-08-17', $result->getPeriodStart());
        $this->assertSame('2026-08-23', $result->getPeriodEnd());
    }

    private function trackWeekWithConversions(): void
    {
        $tracker = Fixture::getTracker(self::IDSITE, '2026-08-18 08:00:00', true, true);

        for ($i = 0; $i < self::VISITS; $i++) {
            $tracker->setForceVisitDateTime(
                Date::factory('2026-08-18 08:00:00')->addPeriod($i, 'minute')->getDatetime()
            );
            $tracker->setNewVisitorId();
            $tracker->setIp('10.20.' . (int) ($i / 250) . '.' . ($i % 250 + 1));
            $tracker->setUrl('http://example.org/pricing');
            Fixture::checkResponse($tracker->doTrackPageView('Pricing'));

            // The two goals convert on separate visits, so neither affects the other's
            // converting visit count and therefore its conversion rate.
            if ($i < self::SIGNUP_CONVERSIONS) {
                Fixture::checkResponse($tracker->doTrackGoal($this->idSignupGoal));
            } elseif ($i < self::SIGNUP_CONVERSIONS + self::PURCHASE_CONVERSIONS) {
                Fixture::checkResponse($tracker->doTrackGoal($this->idPurchaseGoal));
            }
        }
    }

    private function buildArchive(): void
    {
        Request::processRequest('Goals.get', [
            'idSite' => self::IDSITE,
            'period' => ReportPeriod::PERIOD,
            'date' => ReportPeriod::DATE,
        ], []);
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
