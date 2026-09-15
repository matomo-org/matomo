<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Integration\PluginPromotions;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionRegistry;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Promotion;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionRenderer;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\SelectedPromotion;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\PromotionTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SegmentsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TriggerResult;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 * @group Plugins
 */
class PromotionRendererTest extends IntegrationTestCase
{
    private PromotionRenderer $renderer;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2026-01-01 00:00:00');
        Fixture::loadAllTranslations();

        FakeAccess::$superUser = true;
        FakeAccess::$identity = 'alice';
        $_GET['idSite'] = 1;
        // The banner only ever renders inside a dashboard request, and the campaign medium
        // names that request: without a module and an action core adds no campaign
        // parameters at all.
        $_GET['module'] = 'Dashboard';
        $_GET['action'] = 'embeddedIndex';

        $this->renderer = new PromotionRenderer();
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['module'], $_GET['action']);
        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testRendersTheBannerWithItsDynamicValue(): void
    {
        $html = $this->render(SegmentsTrigger::NAME, ['count' => 6]);

        $this->assertStringContainsString('class="productPromotion"', $html);
        $this->assertStringContainsString('You&#039;ve tailored your audience. Now tailor your reports.', $html);
        $this->assertStringContainsString('You&#039;re already using 6 segments to focus your analysis', $html);
        // The reason stands as its own sentence, with no lead-in wrapped around it.
        $this->assertStringContainsString(
            'Custom Reports is recommended when you use multiple segments and want more control over your reporting.',
            $html
        );
        $this->assertStringNotContainsString('Why you&#039;re seeing this', $html);
        // A body with no placeholders must still come out interpolated, not raw.
        $this->assertStringNotContainsString('%1$s', $html);
        $this->assertStringContainsString('Try Custom Reports', $html);
        $this->assertStringContainsString('data-role="dismiss"', $html);
    }

    /**
     * No headline takes a placeholder, so every figure the promotion reports has to reach
     * the body.
     */
    public function testTheHeadlineIsFixedAndTheBodyCarriesTheFigures(): void
    {
        $html = $this->render(LowConversionRateTrigger::NAME, [
            'goalId' => 2,
            'goalName' => 'Purchase',
            'nbVisits' => 5000,
            'nbConversions' => 100,
            'conversionRate' => 0.02,
        ]);

        $this->assertStringContainsString('Where are you losing momentum?', $html);
        $this->assertStringContainsString('Only 2% of visits convert for Purchase', $html);
        $this->assertStringNotContainsString('%%', $html);
        $this->assertStringNotContainsString('%1$s', $html);
    }

    public function testTheOutboundLinkCarriesTheCampaignParametersAndIsSafeToOpen(): void
    {
        $html = $this->render(SegmentsTrigger::NAME, ['count' => 6]);

        $this->assertStringContainsString('https://plugins.matomo.org/CustomReports', $html);
        $this->assertStringContainsString('mtm_campaign=app_premiumplugins', $html);
        $this->assertStringContainsString('mtm_source=matomo_app_onpremise', $html);
        $this->assertStringContainsString('mtm_medium=app.Dashboard.embeddedIndex', $html);
        $this->assertStringContainsString('mtm_group=triggered_ad', $html);
        $this->assertStringContainsString('mtm_content=CustomReports', $html);
        $this->assertStringContainsString('mtm_placement=top_banner', $html);
        // The campaign helper takes no argument for `mtm_kwd`, so the trigger name is put
        // on the URL itself and must survive the helper merging its own parameters in.
        $this->assertStringContainsString('mtm_kwd=segments', $html);
        // Both the headline and the call to action leave the app, so both open in a new
        // tab and withhold the referrer.
        $this->assertSame(2, substr_count($html, 'target="_blank"'));
        $this->assertSame(2, substr_count($html, 'rel="noreferrer noopener"'));
    }

    /**
     * Every registered promotion, rendered.
     *
     * The copy is 22 translated strings carrying positional placeholders, and the renderer
     * supplies the arguments per trigger. If the two ever disagree, `sprintf` throws a
     * ValueError - not an Exception - which the dashboard catches and turns into a banner
     * that silently does not appear. Nothing else in the suite renders more than two of
     * them, so this walks the registry and checks each one comes out as finished prose.
     */
    public function testEveryRegisteredPromotionRendersWithoutLeftoverPlaceholders(): void
    {
        $registry = StaticContainer::get(PromotionRegistry::class);
        $renderer = StaticContainer::get(PromotionRenderer::class);

        $promotions = $registry->getAllByPriority();
        $this->assertCount(22, $promotions, 'the registry is expected to hold 22 promotions');

        foreach ($promotions as $promotion) {
            $selected = new SelectedPromotion(
                $promotion,
                TriggerResult::triggered($this->everyContextValue(), '2026-08-17', '2026-08-23')
            );

            $html = $renderer->render($selected);
            $where = $promotion->getPluginName() . '/' . $promotion->getTriggerName();

            $this->assertNotSame('', trim($html), $where . ' rendered nothing');

            // An unconsumed placeholder means the copy asks for an argument the renderer
            // does not pass for this trigger.
            $this->assertDoesNotMatchRegularExpression('/%\d+\$s/', $html, $where . ' left a placeholder unfilled');
            $this->assertStringNotContainsString('%s', $html, $where . ' left a placeholder unfilled');
        }
    }

    /**
     * A context carrying every key any trigger produces, so one render call can stand in
     * for all of them. A trigger only ever reads the keys it set itself.
     *
     * @return array<string, mixed>
     */
    private function everyContextValue(): array
    {
        return [
            'count' => 7,
            'numUsers' => 24,
            'numSuperusers' => 4,
            'name' => 'Spring sale',
            'goalName' => 'Newsletter signup',
            'nbConversions' => 640,
            'conversionRate' => 0.0271,
            'bounceRate' => 0.72,
            'entryVisits' => 310,
            'title' => 'Pricing',
            'url' => 'example.org/pricing',
            'loadTime' => 4.2,
        ];
    }

    /**
     * Everything else here renders as a super user, who is never offered a trial, so the
     * trial half of the template went unrendered by any test. This covers it - and it is
     * the variant an ordinary user actually gets: a button rather than a link, and the
     * confirmation dialog the directive hands to modalConfirm().
     */
    public function testANonSuperUserIsOfferedTheTrialControls(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [1];

        $html = $this->render(SegmentsTrigger::NAME, ['count' => 6]);

        $this->assertStringContainsString('data-role="requestTrial"', $html);

        // The directive takes hold of this node when it mounts, because modalConfirm()
        // moves it out of the banner and it cannot be found again afterwards.
        $this->assertStringContainsString('data-role="requestTrialConfirm"', $html);

        $this->assertStringContainsString('data-role="dismiss"', $html);
    }

    /**
     * `disable_tracking_matomo_app_links` exists so that an instance can stop links out of
     * the app from identifying it. The helper honours it by returning the URL untouched,
     * which is easy to defeat by hand-appending a parameter of one's own afterwards.
     */
    public function testTheOutboundLinkCarriesNoCampaignParametersWhenTrackingThemIsDisabled(): void
    {
        Config::getInstance()->General['disable_tracking_matomo_app_links'] = 1;

        $html = $this->render(SegmentsTrigger::NAME, ['count' => 6]);

        $this->assertStringContainsString('https://plugins.matomo.org/CustomReports', $html);

        foreach (['mtm_campaign', 'mtm_source', 'mtm_medium', 'mtm_group', 'mtm_content', 'mtm_placement', 'mtm_kwd'] as $parameter) {
            $this->assertStringNotContainsString($parameter, $html, $parameter . ' survived the opt-out');
        }
    }

    /**
     * Goal names and page URLs are entered by users of the instance, so they must never
     * reach the page unescaped.
     */
    public function testUserSuppliedValuesAreEscaped(): void
    {
        $html = $this->render(LowConversionRateTrigger::NAME, [
            'goalId' => 2,
            'goalName' => '<script>alert(1)</script>',
            'nbVisits' => 5000,
            'nbConversions' => 100,
            'conversionRate' => 0.02,
        ]);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    /**
     * Ad blocker filter lists match these substrings in class names and asset paths alike.
     */
    public function testNoMarkupUsesAnAdBlockerProneName(): void
    {
        $html = $this->render(SegmentsTrigger::NAME, ['count' => 6]);

        preg_match_all('/(?:class|src)="([^"]*)"/', $html, $matches);
        $this->assertNotEmpty($matches[1]);

        foreach ($matches[1] as $value) {
            foreach (['advert', 'banner', '-ad-', 'ads'] as $forbidden) {
                $this->assertStringNotContainsStringIgnoringCase($forbidden, $value);
            }
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function render(string $triggerName, array $context): string
    {
        $definitions = [
            SegmentsTrigger::NAME => [
                'CustomReports',
                'ProfessionalServices_PromoCustomReports',
                'ProfessionalServices_PromotionCustomReportsSegments',
                'product-promotion-custom-reports.png',
            ],
            LowConversionRateTrigger::NAME => [
                'Funnels',
                'ProfessionalServices_PromoFunnels',
                'ProfessionalServices_PromotionFunnelsConversionRate',
                'product-promotion-funnels.png',
            ],
        ];

        [$pluginName, $productKey, $translationPrefix, $image] = $definitions[$triggerName];

        $trigger = $this->createMock(PromotionTrigger::class);
        $trigger->method('getName')->willReturn($triggerName);

        $promotion = new Promotion(1, $pluginName, $productKey, $trigger, $translationPrefix, $image);

        return $this->renderer->render(
            new SelectedPromotion($promotion, TriggerResult::triggered($context, '2026-08-17', '2026-08-23'))
        );
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
