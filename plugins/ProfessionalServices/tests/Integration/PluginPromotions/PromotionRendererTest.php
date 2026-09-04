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

        $this->renderer = new PromotionRenderer();
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testRendersTheBannerWithItsDynamicValue(): void
    {
        $html = $this->render(SegmentsTrigger::NAME, ['count' => 6]);

        $this->assertStringContainsString('class="productPromotion"', $html);
        $this->assertStringContainsString('Understand your 6 segments even better', $html);
        $this->assertStringContainsString('Dig deeper into your segment data', $html);
        $this->assertStringContainsString(
            "Why you&#039;re seeing this: recommended when you have 5+ segments available to analyse.",
            $html
        );
        // A body with no placeholders must still come out interpolated, not raw.
        $this->assertStringNotContainsString('%1$s', $html);
        $this->assertStringContainsString('Try Custom Reports', $html);
        $this->assertStringContainsString('data-role="dismiss"', $html);
    }

    /**
     * The headline names the problem and the body carries the figure behind it, so the
     * two take different translation arguments.
     */
    public function testTheHeadlineNamesTheProblemAndTheBodyCarriesTheFigure(): void
    {
        $html = $this->render(LowConversionRateTrigger::NAME, [
            'goalId' => 2,
            'goalName' => 'Purchase',
            'nbVisits' => 5000,
            'nbConversions' => 100,
            'conversionRate' => 0.02,
        ]);

        $this->assertStringContainsString('Find where visitors drop off before converting', $html);
        $this->assertStringContainsString('Only 2% of visits convert for Purchase', $html);
        $this->assertStringNotContainsString('%%', $html);
        $this->assertStringNotContainsString('%1$s', $html);
    }

    public function testTheOutboundLinkCarriesTheCampaignParametersAndIsSafeToOpen(): void
    {
        $html = $this->render(SegmentsTrigger::NAME, ['count' => 6]);

        $this->assertStringContainsString('https://plugins.matomo.org/CustomReports', $html);
        $this->assertStringContainsString('trigger_name=segments', $html);
        $this->assertStringContainsString('mtm_content=custom_reports', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
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
                'ProfessionalServices_PromotionSegments',
                'product-promotion-custom-reports.png',
            ],
            LowConversionRateTrigger::NAME => [
                'Funnels',
                'ProfessionalServices_PromotionProductFunnels',
                'funnels',
                'ProfessionalServices_PromotionConversionRate',
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
