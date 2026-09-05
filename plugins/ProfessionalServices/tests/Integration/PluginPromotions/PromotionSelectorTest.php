<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Integration\PluginPromotions;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionEligibility;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionRegistry;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionSelector;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\BounceRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\PromotionTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ScheduledReportsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SegmentsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TriggerResult;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\UserPromotionState;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 * @group Plugins
 */
class PromotionSelectorTest extends IntegrationTestCase
{
    private const SITE_ONE = 1;

    private const SITE_TWO = 2;

    private UserPromotionState $userState;

    /**
     * @var array<string, bool> which trigger names fire, per test
     */
    private array $triggering = [];

    /**
     * @var array<string, int> how often each trigger was evaluated
     */
    private array $evaluations = [];

    public function setUp(): void
    {
        parent::setUp();

        Date::$now = strtotime('2026-08-27 10:00:00 UTC');

        Fixture::createWebsite('2026-01-01 00:00:00');
        Fixture::createWebsite('2026-01-01 00:00:00');

        $this->asUser('alice');
        $_GET['idSite'] = self::SITE_ONE;

        $this->userState = new UserPromotionState(StaticContainer::get(UserScopedSettingsAccessManager::class));
        $this->triggering = [];
        $this->evaluations = [];
    }

    public function tearDown(): void
    {
        Date::$now = null;
        unset($_GET['idSite']);

        parent::tearDown();
    }

    public function testTheHighestPriorityTriggeredPromotionWins(): void
    {
        $this->triggering = ['segments' => true, 'bounce_rate' => true, 'conversion_rate_funnels' => true];

        $selected = $this->makeSelector()->select();

        $this->assertSame('CustomReports', $selected->getPromotion()->getPluginName());
        $this->assertSame('segments', $selected->getPromotion()->getTriggerName());
    }

    public function testLowerPriorityTriggersAreNotEvenEvaluated(): void
    {
        $this->triggering = ['segments' => true, 'bounce_rate' => true];

        $this->makeSelector()->select();

        $this->assertSame(1, $this->evaluations['segments'] ?? 0);
        $this->assertSame(0, $this->evaluations['bounce_rate'] ?? 0, 'the goals and entry pages reports must not be read');
    }

    public function testNothingIsShownWhenNoTriggerFires(): void
    {
        $this->assertNull($this->makeSelector()->select());
    }

    public function testDismissingStartsASevenDayGlobalCooldownAndThenTheNextProductIsShown(): void
    {
        $this->triggering = ['segments' => true, 'bounce_rate' => true];
        $selector = $this->makeSelector();

        $this->assertSame('CustomReports', $selector->select()->getPromotion()->getPluginName());

        $this->userState->dismiss('CustomReports', 'segments');

        // Nothing at all for the next seven days, not even a different product.
        $this->assertNull($selector->select());

        Date::$now = strtotime('2026-09-04 10:00:00 UTC');

        // Custom Reports is still in its six month cooldown, so the next eligible product
        // takes its place.
        $this->assertSame('HeatmapSessionRecording', $selector->select()->getPromotion()->getPluginName());
    }

    public function testAGlobalCooldownSkipsEveryTriggerEvaluation(): void
    {
        $this->triggering = ['segments' => true];
        $this->userState->dismiss('Funnels', 'conversion_rate_funnels');

        $this->assertNull($this->makeSelector()->select());
        $this->assertSame([], $this->evaluations, 'no trigger may run while the user is in a global cooldown');
    }

    /**
     * Custom Reports is promoted by two different triggers, but they share one product,
     * so dismissing either of them silences both.
     */
    public function testBothCustomReportsTriggersShareOneProductCooldown(): void
    {
        $this->triggering = ['scheduled_reports' => true];
        $selector = $this->makeSelector();

        $this->assertSame('scheduled_reports', $selector->select()->getPromotion()->getTriggerName());

        $this->userState->dismiss('CustomReports', 'segments');
        Date::$now = strtotime('2026-09-04 10:00:00 UTC');

        $this->assertNull($selector->select());
    }

    public function testDismissingOnOneWebsiteAlsoSilencesTheOther(): void
    {
        $this->triggering = ['segments' => true];
        $selector = $this->makeSelector();

        $_GET['idSite'] = self::SITE_TWO;
        $this->assertNotNull($selector->select());

        $this->userState->dismiss('CustomReports', 'segments');
        Date::$now = strtotime('2026-09-04 10:00:00 UTC');

        $_GET['idSite'] = self::SITE_ONE;
        $this->assertNull($selector->select(), 'dismissing on site 2 must also silence site 1');
    }

    public function testAnotherUserIsUnaffectedByADismissal(): void
    {
        $this->triggering = ['segments' => true];
        $selector = $this->makeSelector();

        $this->userState->dismiss('CustomReports', 'segments');
        Date::$now = strtotime('2026-09-04 10:00:00 UTC');

        $this->assertNull($selector->select());

        $this->asUser('bob');
        $this->assertNotNull($selector->select());
    }

    public function testAnonymousUsersNeverSeeAPromotion(): void
    {
        $this->triggering = ['segments' => true];

        FakeAccess::$identity = 'anonymous';

        $this->assertNull($this->makeSelector()->select());
    }

    public function testAPendingTrialRequestSuppressesThatProduct(): void
    {
        $this->triggering = ['segments' => true, 'bounce_rate' => true];

        Option::set('Marketplace.PluginTrialRequest.CustomReports', json_encode([
            'requestTime' => Date::getNowTimestamp(),
            'displayName' => 'Custom Reports',
            'dismissed' => [],
            'requestedBy' => 'alice',
        ]));

        $this->assertSame('HeatmapSessionRecording', $this->makeSelector()->select()->getPromotion()->getPluginName());
    }

    public function testNothingIsShownWhenPromotionsAreNotAllowed(): void
    {
        $this->triggering = ['segments' => true, 'bounce_rate' => true];

        $this->assertNull($this->makeSelector(false)->select());
    }

    private function makeSelector(bool $promotionsAllowed = true): PromotionSelector
    {
        $eligibility = $this->createMock(PromotionEligibility::class);
        $eligibility->method('isAllowedForPlugin')->willReturn($promotionsAllowed);

        $registry = new PromotionRegistry(
            $this->makeTrigger(SegmentsTrigger::class, SegmentsTrigger::NAME),
            $this->makeTrigger(BounceRateTrigger::class, BounceRateTrigger::NAME),
            $this->makeTrigger(LowConversionRateTrigger::class, LowConversionRateTrigger::NAME),
            $this->makeTrigger(HighConversionRateTrigger::class, HighConversionRateTrigger::NAME),
            $this->makeTrigger(ScheduledReportsTrigger::class, ScheduledReportsTrigger::NAME)
        );

        return new PromotionSelector($registry, $eligibility, $this->userState);
    }

    /**
     * @param class-string<PromotionTrigger> $className
     * @return PromotionTrigger&\PHPUnit\Framework\MockObject\MockObject
     */
    private function makeTrigger(string $className, string $name)
    {
        $trigger = $this->createMock($className);
        $trigger->method('getName')->willReturn($name);
        $trigger->method('evaluate')->willReturnCallback(function () use ($name): TriggerResult {
            $this->evaluations[$name] = ($this->evaluations[$name] ?? 0) + 1;

            return empty($this->triggering[$name])
                ? TriggerResult::notTriggered()
                : TriggerResult::triggered(['count' => 5]);
        });

        return $trigger;
    }

    private function asUser(string $login): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = $login;
        FakeAccess::$idSitesView = [self::SITE_ONE, self::SITE_TWO];
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
