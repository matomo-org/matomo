<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Integration\PluginPromotions;

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
        $this->assertStringContainsString('mtm_content=CustomReports', $html);
        // Until matomo-org/matomo#25153 lets the campaign helper carry `mtm_kwd`, the
        // trigger name travels under its own parameter.
        $this->assertStringContainsString('trigger_name=segments', $html);
        // Both the headline and the call to action leave the app, so both open in a new
        // tab and withhold the referrer.
        $this->assertSame(2, substr_count($html, 'target="_blank"'));
        $this->assertSame(2, substr_count($html, 'rel="noreferrer noopener"'));
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
                'ProfessionalServices_PromotionProductCustomReports',
                'custom_reports',
                'ProfessionalServices_PromotionCustomReportsSegments',
                'product-promotion-custom-reports.png',
            ],
            LowConversionRateTrigger::NAME => [
                'Funnels',
                'ProfessionalServices_PromotionProductFunnels',
                'funnels',
                'ProfessionalServices_PromotionFunnelsConversionRate',
                'product-promotion-funnels.png',
            ],
        ];

        [$pluginName, $productKey, $campaignContent, $translationPrefix, $image] = $definitions[$triggerName];

        $trigger = $this->createMock(PromotionTrigger::class);
        $trigger->method('getName')->willReturn($triggerName);

        $promotion = new Promotion(1, $pluginName, $productKey, $trigger, $campaignContent, $translationPrefix, $image);

        return $this->renderer->render(
            new SelectedPromotion($promotion, TriggerResult::triggered($context, '2026-08-17', '2026-08-23'), 1)
        );
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
