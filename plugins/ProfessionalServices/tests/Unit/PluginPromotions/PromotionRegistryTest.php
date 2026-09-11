<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionRegistry;
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

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class PromotionRegistryTest extends TestCase
{
    private PromotionRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new PromotionRegistry(
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
    }

    public function testPromotionsAreOrderedByPriority(): void
    {
        $ordered = [];
        foreach ($this->registry->getAllByPriority() as $promotion) {
            $ordered[] = [$promotion->getPluginName(), $promotion->getTriggerName()];
        }

        $this->assertSame([
            ['CustomReports', 'segments'],
            ['HeatmapSessionRecording', 'bounce_rate'],
            ['Funnels', 'conversion_rate_funnels'],
            ['AbTesting', 'conversion_rate_ABtesting'],
            ['CustomReports', 'scheduled_reports'],
            ['CrashAnalytics', 'many_sites'],
            ['MediaAnalytics', 'media_outlinks'],
            ['UsersFlow', 'multiple_page_visits'],
            ['SearchEngineKeywordsPerformance', 'keywords_not_defined'],
            ['LoginSaml', 'many_users'],
            ['AdvertisingConversionExport', 'campaign_conversions'],
            ['FormAnalytics', 'form_visits'],
            ['WhiteLabel', 'custom_logo'],
            ['WooCommerceAnalytics', 'woocommerce_add_to_cart_urls'],
            ['RollUpReporting', 'multiple_active_sites'],
            ['Cohorts', 'returning_visitors'],
            ['MultiChannelConversionAttribution', 'multiple_conversion_channels'],
            ['SEOWebVitals', 'slow_page'],
            ['ActivityLog', 'multiple_superusers'],
            ['TeamBundle', 'multi_product_team'],
            ['BusinessBundle', 'multi_product_business'],
            ['EnterpriseBundle', 'multi_product_enterprise'],
        ], $ordered);
    }

    public function testCampaignContentIsSharedByBothCustomReportsTriggers(): void
    {
        $segments = $this->registry->findByPluginAndTrigger('CustomReports', 'segments');
        $scheduledReports = $this->registry->findByPluginAndTrigger('CustomReports', 'scheduled_reports');

        $this->assertSame('custom_reports', $segments->getCampaignContent());
        $this->assertSame('custom_reports', $scheduledReports->getCampaignContent());

        // Same product, so they must share the dismissal cooldown, but they stay
        // distinguishable for campaign attribution.
        $this->assertSame($segments->getPluginName(), $scheduledReports->getPluginName());
        $this->assertNotSame($segments->getTriggerName(), $scheduledReports->getTriggerName());
    }

    public function testEachPromotionHasItsOwnCopy(): void
    {
        $titleKeys = [];
        foreach ($this->registry->getAllByPriority() as $promotion) {
            $titleKeys[] = $promotion->getTitleTranslationKey();
        }

        $this->assertCount(count($titleKeys), array_unique($titleKeys));
    }

    /**
     * @dataProvider getUnknownCombinations
     */
    public function testUnknownCombinationsAreNotFound(string $pluginName, string $triggerName): void
    {
        $this->assertNull($this->registry->findByPluginAndTrigger($pluginName, $triggerName));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function getUnknownCombinations(): array
    {
        return [
            'unknown plugin' => ['NotAPlugin', 'segments'],
            'unknown trigger' => ['CustomReports', 'not_a_trigger'],
            'trigger of another plugin' => ['CustomReports', 'bounce_rate'],
            'empty values' => ['', ''],
        ];
    }

    /**
     * @param class-string<PromotionTrigger> $className
     * @return PromotionTrigger&\PHPUnit\Framework\MockObject\MockObject
     */
    private function makeTrigger(string $className, string $name)
    {
        $trigger = $this->createMock($className);
        $trigger->method('getName')->willReturn($name);

        return $trigger;
    }
}
