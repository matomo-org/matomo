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
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\BusinessBundleTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\CustomLogoTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\EnterpriseBundleTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\FormPageTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ManyUsersTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleActiveSitesTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleSuperusersTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\PromotionTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ScheduledReportsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\CampaignConversionsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\KeywordsNotDefinedTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ManySitesTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MediaOutlinksTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleConversionChannelsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultiplePageVisitsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ReturningVisitorsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SlowPageTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SegmentsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TeamBundleTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\WooCommerceUrlsTrigger;
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

    private const SITE_THREE = 3;

    private const SITE_FOUR = 4;

    private UserPromotionState $userState;

    /**
     * @var array<string, bool> which trigger names fire, per test
     */
    private array $triggering = [];

    /**
     * @var array<int, array<string, bool>> which trigger names fire for one website, where
     *                                      a test needs the websites to differ
     */
    private array $triggeringPerSite = [];

    /**
     * @var string[]|null plugin names still allowed to be promoted, or null for all of them
     */
    private ?array $allowedPlugins = null;

    /**
     * @var array<string, mixed> the context the last shown promotion rendered with
     */
    private array $lastContext = [];

    /**
     * @var int what a firing trigger currently reports, so a test can move the underlying
     *          figure between renders
     */
    private int $reportedCount = 5;

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

    public function testDismissingStartsAnEighteenDayGlobalCooldownAndThenTheNextProductIsShown(): void
    {
        $this->triggering = ['segments' => true, 'bounce_rate' => true];
        $selector = $this->makeSelector();

        $this->assertSame('CustomReports', $selector->select()->getPromotion()->getPluginName());

        $this->userState->dismiss('CustomReports', 'segments');

        // Nothing at all for the next eighteen days, not even a different product.
        $this->assertNull($selector->select());

        Date::$now = strtotime('2026-09-15 10:00:00 UTC');

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

        // `time()`, not the frozen clock. Marketplace's trial storage expires a request
        // against the real clock rather than `Date::getNowTimestamp()`, so a request
        // stamped with this suite's frozen 2026-08-27 silently counted as expired once the
        // real date passed the 28 day window - and the test began failing on its own,
        // months after it was written, with no change to the code it covers.
        Option::set('Marketplace.PluginTrialRequest.CustomReports', json_encode([
            'requestTime' => time(),
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

    /**
     * Only one promotion is active at a time for a user, across every website they can see.
     * Which one it is depends on where they happened to look first: the promotion they were
     * shown takes the slot, and the other website shows nothing while it holds it.
     */
    public function testTheFirstWebsiteLookedAtDecidesWhichPromotionTheUserGets(): void
    {
        $this->triggeringPerSite = [
            self::SITE_ONE => ['segments' => true],
            self::SITE_TWO => ['bounce_rate' => true],
        ];

        $this->asUser('alice');

        // Alice opens website two first, so she gets its promotion...
        $this->assertSame('HeatmapSessionRecording', $this->showOn(self::SITE_TWO));

        // ...and website one shows nothing, even though its own promotion would fire there.
        $this->assertNull($this->showOn(self::SITE_ONE));
    }

    public function testAnotherUserLookingTheOtherWayRoundGetsTheOtherPromotion(): void
    {
        $this->triggeringPerSite = [
            self::SITE_ONE => ['segments' => true],
            self::SITE_TWO => ['bounce_rate' => true],
        ];

        $this->asUser('bob');

        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));

        $this->assertNull($this->showOn(self::SITE_TWO));
    }

    /**
     * The slot holds a promotion, not a website. When the same promotion is what fires on
     * the other website too, there is nothing to suppress.
     */
    public function testThePromotionHoldingTheSlotStillShowsOnEveryWebsiteItFiresOn(): void
    {
        $this->triggeringPerSite = [
            self::SITE_ONE => ['segments' => true],
            self::SITE_TWO => ['segments' => true],
        ];

        $this->asUser('carol');

        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));

        $this->assertSame('CustomReports', $this->showOn(self::SITE_TWO));
    }

    /**
     * Nothing below the promotion holding the slot is even looked at, so holding the slot
     * costs no archive reads on the websites it does not fire on.
     */
    public function testNoOtherTriggerIsEvaluatedWhileThePromotionHoldsTheSlot(): void
    {
        $this->triggeringPerSite = [
            self::SITE_ONE => ['segments' => true],
            self::SITE_TWO => ['bounce_rate' => true],
        ];

        $this->asUser('dave');

        $this->showOn(self::SITE_ONE);

        $this->evaluations = [];

        $this->showOn(self::SITE_TWO);

        $this->assertSame(['segments' => 1], $this->evaluations);
    }

    /**
     * A promotion recorded against the slot that no longer exists - renamed or removed
     * between releases - releases the slot, and must take its recorded outcome with it.
     * Leaving that outcome behind handed the vanished promotion's figures to whichever
     * promotion was shown in its place, so the copy quoted a number from somewhere else.
     */
    public function testAVanishedPromotionDoesNotLendItsFiguresToTheNextOne(): void
    {
        $this->triggeringPerSite = [self::SITE_ONE => ['segments' => true]];
        $this->reportedCount = 9;

        $this->asUser('mona');

        // A slot held by something the registry has never heard of, with a figure of its own.
        $this->userState->recordShown('RemovedPlugin', 'removed_trigger', [
            'triggered' => true,
            'context' => ['count' => 4321],
            'periodStart' => '2026-01-01',
            'periodEnd' => '2026-01-07',
        ]);

        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));
        $this->assertSame(9, $this->lastContext['count'], 'the figure must be its own, not the vanished one');

        // And the slot now belongs to the promotion actually being shown.
        $active = $this->userState->getActivePromotion();
        $this->assertSame('CustomReports', $active['pluginName']);
        $this->assertSame('segments', $active['triggerName']);
    }

    /**
     * The worked example the behaviour was specified with: Custom Reports is chosen on the
     * first website looked at, and every other website then answers only the question "may
     * Custom Reports be shown here?" - never "what else would qualify?".
     */
    public function testOnceChosenEveryOtherWebsiteOnlyAsksAboutThatOnePromotion(): void
    {
        $this->triggeringPerSite = [
            // eight segments, so Custom Reports qualifies
            self::SITE_ONE => ['segments' => true],
            // three segments: Custom Reports does not qualify, and nothing else is asked
            self::SITE_TWO => [],
            // two segments: same again
            self::SITE_THREE => [],
            // three segments but a high bounce rate, so Heatmaps would qualify here - and
            // is still not shown, because Custom Reports holds the slot
            self::SITE_FOUR => ['bounce_rate' => true],
        ];

        $this->asUser('ivan');

        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));
        $this->assertNull($this->showOn(self::SITE_TWO));
        $this->assertNull($this->showOn(self::SITE_THREE));
        $this->assertNull($this->showOn(self::SITE_FOUR));

        // Only the chosen promotion was ever evaluated on the later websites.
        $this->assertSame(['segments' => 4], $this->evaluations);
    }

    /**
     * The figure a promotion quotes is settled when it is first shown and then kept. A
     * number the user has already read must not move underneath them as each new week is
     * archived, so the trigger is asked again only whether it still qualifies - never for a
     * fresh figure.
     */
    public function testTheFigureInTheCopyIsLockedWhenThePromotionIsFirstShown(): void
    {
        $this->triggeringPerSite = [self::SITE_ONE => ['segments' => true]];
        $this->reportedCount = 8;

        $this->asUser('karl');

        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));
        $this->assertSame(8, $this->lastContext['count']);

        // A later week archives, and the user has since deleted two segments.
        Date::$now = strtotime('2026-09-15 10:00:00 UTC');
        $this->reportedCount = 6;

        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));
        $this->assertSame(8, $this->lastContext['count'], 'the figure first shown must not move');
    }

    /**
     * The lock belongs to the slot, not to the product: once the slot is handed on, the
     * promotion that takes it quotes its own, current figure.
     */
    public function testAPromotionTakingTheSlotQuotesItsOwnFigure(): void
    {
        $this->triggeringPerSite = [self::SITE_ONE => ['segments' => true, 'bounce_rate' => true]];
        $this->reportedCount = 8;

        $this->asUser('lena');

        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));
        $this->assertSame(8, $this->lastContext['count']);

        $this->userState->dismiss('CustomReports', 'segments');

        Date::$now = strtotime('2026-09-15 10:00:00 UTC');
        $this->reportedCount = 3;

        $this->assertSame('HeatmapSessionRecording', $this->showOn(self::SITE_ONE));
        $this->assertSame(3, $this->lastContext['count']);
    }

    /**
     * The slot is kept for as long as the promotion stays eligible - there is no expiry on
     * it, so a user who neither dismisses it nor installs the plugin keeps being offered
     * the same one.
     */
    public function testTheSlotIsKeptIndefinitelyWhileThePromotionStaysEligible(): void
    {
        $this->triggeringPerSite = [
            self::SITE_ONE => ['segments' => true],
            self::SITE_TWO => ['bounce_rate' => true],
        ];

        $this->asUser('judy');

        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));

        // Months later, with no dismissal in between, nothing has changed hands.
        Date::$now = strtotime('2027-03-01 10:00:00 UTC');

        $this->assertNull($this->showOn(self::SITE_TWO));
        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));
    }

    /**
     * Dismissing hands the slot back. Otherwise the global cooldown would expire only for
     * the slot to stay locked to a promotion the user has already refused.
     */
    public function testDismissingFreesTheSlotForAnotherPromotion(): void
    {
        $this->triggeringPerSite = [
            self::SITE_ONE => ['segments' => true],
            self::SITE_TWO => ['bounce_rate' => true],
        ];

        $this->asUser('frank');

        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));

        $this->userState->dismiss('CustomReports', 'segments');

        // The global cooldown silences everything first.
        $this->assertNull($this->showOn(self::SITE_TWO));

        Date::$now = strtotime('2026-09-15 10:00:00 UTC');

        $this->assertSame('HeatmapSessionRecording', $this->showOn(self::SITE_TWO));
    }

    /**
     * Installing the promoted plugin makes its promotion ineligible, which hands the slot
     * back. The next promotion takes it on that same dashboard, rather than the user seeing
     * one blank render first.
     */
    public function testInstallingThePromotedPluginHandsTheSlotOnImmediately(): void
    {
        $this->triggeringPerSite = [
            self::SITE_ONE => ['segments' => true, 'bounce_rate' => true],
        ];

        $this->asUser('grace');

        $this->assertSame('CustomReports', $this->showOn(self::SITE_ONE));

        // Custom Reports is now installed, so nothing may promote it any more.
        $this->allowedPlugins = ['HeatmapSessionRecording'];

        $this->assertSame('HeatmapSessionRecording', $this->showOn(self::SITE_ONE));
    }

    /**
     * Selects for one website and records the result the way the dashboard does, since it
     * is displaying a promotion - not choosing one - that claims the single slot.
     *
     * @return string|null the plugin promoted, or null when nothing was shown
     */
    private function showOn(int $idSite): ?string
    {
        $_GET['idSite'] = $idSite;

        $selected = $this->makeSelector()->select();

        if (null === $selected) {
            return null;
        }

        $this->userState->recordShown(
            $selected->getPromotion()->getPluginName(),
            $selected->getPromotion()->getTriggerName(),
            $selected->getTriggerResult()->toArray()
        );

        $this->lastContext = $selected->getTriggerResult()->getContext();

        return $selected->getPromotion()->getPluginName();
    }

    private function makeSelector(bool $promotionsAllowed = true): PromotionSelector
    {
        $eligibility = $this->createMock(PromotionEligibility::class);
        $eligibility->method('isAllowedForPlugin')->willReturnCallback(
            function (string $pluginName) use ($promotionsAllowed): bool {
                if (!$promotionsAllowed) {
                    return false;
                }

                return null === $this->allowedPlugins || in_array($pluginName, $this->allowedPlugins, true);
            }
        );

        $registry = new PromotionRegistry(
            $this->makeTrigger(SegmentsTrigger::class, SegmentsTrigger::NAME),
            $this->makeTrigger(BounceRateTrigger::class, BounceRateTrigger::NAME),
            $this->makeTrigger(LowConversionRateTrigger::class, LowConversionRateTrigger::NAME),
            $this->makeTrigger(HighConversionRateTrigger::class, HighConversionRateTrigger::NAME),
            $this->makeTrigger(ScheduledReportsTrigger::class, ScheduledReportsTrigger::NAME),
            $this->makeTrigger(CampaignConversionsTrigger::class, CampaignConversionsTrigger::NAME),
            $this->makeTrigger(KeywordsNotDefinedTrigger::class, KeywordsNotDefinedTrigger::NAME),
            $this->makeTrigger(ManySitesTrigger::class, ManySitesTrigger::NAME),
            $this->makeTrigger(MediaOutlinksTrigger::class, MediaOutlinksTrigger::NAME),
            $this->makeTrigger(MultipleConversionChannelsTrigger::class, MultipleConversionChannelsTrigger::NAME),
            $this->makeTrigger(MultiplePageVisitsTrigger::class, MultiplePageVisitsTrigger::NAME),
            $this->makeTrigger(ReturningVisitorsTrigger::class, ReturningVisitorsTrigger::NAME),
            $this->makeTrigger(SlowPageTrigger::class, SlowPageTrigger::NAME),
            $this->makeTrigger(ManyUsersTrigger::class, ManyUsersTrigger::NAME),
            $this->makeTrigger(CustomLogoTrigger::class, CustomLogoTrigger::NAME),
            $this->makeTrigger(FormPageTrigger::class, FormPageTrigger::NAME),
            $this->makeTrigger(WooCommerceUrlsTrigger::class, WooCommerceUrlsTrigger::NAME),
            $this->makeTrigger(MultipleActiveSitesTrigger::class, MultipleActiveSitesTrigger::NAME),
            $this->makeTrigger(MultipleSuperusersTrigger::class, MultipleSuperusersTrigger::NAME),
            $this->makeTrigger(TeamBundleTrigger::class, TeamBundleTrigger::NAME),
            $this->makeTrigger(BusinessBundleTrigger::class, BusinessBundleTrigger::NAME),
            $this->makeTrigger(EnterpriseBundleTrigger::class, EnterpriseBundleTrigger::NAME)
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
        $trigger->method('evaluate')->willReturnCallback(function (int $idSite) use ($name): TriggerResult {
            $this->evaluations[$name] = ($this->evaluations[$name] ?? 0) + 1;

            $fires = array_key_exists($idSite, $this->triggeringPerSite)
                ? !empty($this->triggeringPerSite[$idSite][$name])
                : !empty($this->triggering[$name]);

            return $fires
                ? TriggerResult::triggered(['count' => $this->reportedCount])
                : TriggerResult::notTriggered();
        });

        return $trigger;
    }

    private function asUser(string $login): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = $login;
        FakeAccess::$idSitesView = [self::SITE_ONE, self::SITE_TWO, self::SITE_THREE, self::SITE_FOUR];
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
