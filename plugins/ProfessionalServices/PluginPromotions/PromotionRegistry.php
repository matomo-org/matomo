<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

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
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ScheduledReportsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\CampaignConversionsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\KeywordsNotDefinedTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ManyPagesTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MediaOutlinksTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleConversionChannelsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultiplePageVisitsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ReturningVisitsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SlowPageTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SegmentsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TeamBundleTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\WooCommerceUrlsTrigger;

/**
 * The contextual dashboard promotions, in priority order.
 *
 * Custom Reports appears twice, once per trigger. The two entries keep separate trigger
 * names for campaign attribution but share a single dismissal cooldown, because that is
 * keyed on the plugin name.
 */
class PromotionRegistry
{
    /**
     * @var Promotion[]
     */
    private array $promotions;

    public function __construct(
        SegmentsTrigger $segmentsTrigger,
        BounceRateTrigger $bounceRateTrigger,
        LowConversionRateTrigger $lowConversionRateTrigger,
        HighConversionRateTrigger $highConversionRateTrigger,
        ScheduledReportsTrigger $scheduledReportsTrigger,
        CampaignConversionsTrigger $campaignConversionsTrigger,
        KeywordsNotDefinedTrigger $keywordsNotDefinedTrigger,
        ManyPagesTrigger $manyPagesTrigger,
        MediaOutlinksTrigger $mediaOutlinksTrigger,
        MultipleConversionChannelsTrigger $multipleConversionChannelsTrigger,
        MultiplePageVisitsTrigger $multiplePageVisitsTrigger,
        ReturningVisitsTrigger $returningVisitsTrigger,
        SlowPageTrigger $slowPageTrigger,
        ManyUsersTrigger $manyUsersTrigger,
        CustomLogoTrigger $customLogoTrigger,
        FormPageTrigger $formPageTrigger,
        WooCommerceUrlsTrigger $wooCommerceUrlsTrigger,
        MultipleActiveSitesTrigger $multipleActiveSitesTrigger,
        MultipleSuperusersTrigger $multipleSuperusersTrigger,
        TeamBundleTrigger $teamBundleTrigger,
        BusinessBundleTrigger $businessBundleTrigger,
        EnterpriseBundleTrigger $enterpriseBundleTrigger
    ) {
        $this->promotions = [
            new Promotion(
                1,
                'CustomReports',
                'ProfessionalServices_PromotionProductCustomReports',
                $segmentsTrigger,
                'custom_reports',
                'ProfessionalServices_PromotionCustomReportsSegments',
                'product-promotion-custom-reports.png'
            ),
            new Promotion(
                2,
                'HeatmapSessionRecording',
                'ProfessionalServices_PromotionProductHeatmapSessionRecording',
                $bounceRateTrigger,
                'heatmap_session_recording',
                'ProfessionalServices_PromotionHeatmapSessionRecordingBounceRate',
                'product-promotion-heatmap-session-recording.png'
            ),
            new Promotion(
                3,
                'Funnels',
                'ProfessionalServices_PromotionProductFunnels',
                $lowConversionRateTrigger,
                'funnels',
                'ProfessionalServices_PromotionFunnelsConversionRate',
                'product-promotion-funnels.png'
            ),
            new Promotion(
                4,
                'AbTesting',
                'ProfessionalServices_PromotionProductAbTesting',
                $highConversionRateTrigger,
                'ab_testing',
                'ProfessionalServices_PromotionAbTestingConversionRate',
                'product-promotion-ab-testing.png'
            ),
            new Promotion(
                5,
                'CustomReports',
                'ProfessionalServices_PromotionProductCustomReports',
                $scheduledReportsTrigger,
                'custom_reports',
                'ProfessionalServices_PromotionCustomReportsScheduledReports',
                'product-promotion-custom-reports.png'
            ),
            // These three read the shape of the instance rather than a website's reports,
            // so they sit below every promotion that can say something about the site the
            // user is actually looking at.
            new Promotion(
                10,
                'LoginSaml',
                'ProfessionalServices_PromotionProductLoginSaml',
                $manyUsersTrigger,
                'login_saml',
                'ProfessionalServices_PromotionLoginSaml',
                'product-promotion-login-saml.png'
            ),
            new Promotion(
                6,
                'CrashAnalytics',
                'ProfessionalServices_PromotionProductCrashAnalytics',
                $manyPagesTrigger,
                'crash_analytics',
                'ProfessionalServices_PromotionCrashAnalytics',
                'product-promotion-crash-analytics.png'
            ),
            new Promotion(
                7,
                'MediaAnalytics',
                'ProfessionalServices_PromotionProductMediaAnalytics',
                $mediaOutlinksTrigger,
                'media_analytics',
                'ProfessionalServices_PromotionMediaAnalytics',
                'product-promotion-media-analytics.png'
            ),
            new Promotion(
                8,
                'UsersFlow',
                'ProfessionalServices_PromotionProductUsersFlow',
                $multiplePageVisitsTrigger,
                'users_flow',
                'ProfessionalServices_PromotionUsersFlow',
                'product-promotion-users-flow.png'
            ),
            new Promotion(
                9,
                'SearchEngineKeywordsPerformance',
                'ProfessionalServices_PromotionProductSearchEngineKeywordsPerformance',
                $keywordsNotDefinedTrigger,
                'search_engine_keywords_performance',
                'ProfessionalServices_PromotionSearchEngineKeywordsPerformance',
                'product-promotion-search-engine-keywords-performance.png'
            ),
            new Promotion(
                11,
                'AdvertisingConversionExport',
                'ProfessionalServices_PromotionProductAdvertisingConversionExport',
                $campaignConversionsTrigger,
                'advertising_conversion_export',
                'ProfessionalServices_PromotionAdvertisingConversionExport',
                'product-promotion-advertising-conversion-export.png'
            ),
            new Promotion(
                12,
                'FormAnalytics',
                'ProfessionalServices_PromotionProductFormAnalytics',
                $formPageTrigger,
                'form_analytics',
                'ProfessionalServices_PromotionFormAnalytics',
                'product-promotion-form-analytics.png'
            ),
            new Promotion(
                14,
                'WooCommerceAnalytics',
                'ProfessionalServices_PromotionProductWooCommerceAnalytics',
                $wooCommerceUrlsTrigger,
                'woocommerce_analytics',
                'ProfessionalServices_PromotionWooCommerceAnalytics',
                'product-promotion-woocommerce-analytics.png'
            ),
            new Promotion(
                13,
                'WhiteLabel',
                'ProfessionalServices_PromotionProductWhiteLabel',
                $customLogoTrigger,
                'white_label',
                'ProfessionalServices_PromotionWhiteLabel',
                'product-promotion-white-label.png'
            ),
            new Promotion(
                15,
                'RollUpReporting',
                'ProfessionalServices_PromotionProductRollUpReporting',
                $multipleActiveSitesTrigger,
                'roll_up_reporting',
                'ProfessionalServices_PromotionRollUpReporting',
                'product-promotion-roll-up-reporting.png'
            ),
            new Promotion(
                16,
                'Cohorts',
                'ProfessionalServices_PromotionProductCohorts',
                $returningVisitsTrigger,
                'cohorts',
                'ProfessionalServices_PromotionCohorts',
                'product-promotion-cohorts.png'
            ),
            new Promotion(
                17,
                'MultiChannelConversionAttribution',
                'ProfessionalServices_PromotionProductMultiChannelConversionAttribution',
                $multipleConversionChannelsTrigger,
                'multi_channel_conversion_attribution',
                'ProfessionalServices_PromotionMultiChannelConversionAttribution',
                'product-promotion-multi-channel-conversion-attribution.png'
            ),
            new Promotion(
                18,
                'SEOWebVitals',
                'ProfessionalServices_PromotionProductSEOWebVitals',
                $slowPageTrigger,
                'seo_web_vitals',
                'ProfessionalServices_PromotionSEOWebVitals',
                'product-promotion-seo-web-vitals.png'
            ),
            new Promotion(
                19,
                'ActivityLog',
                'ProfessionalServices_PromotionProductActivityLog',
                $multipleSuperusersTrigger,
                'activity_log',
                'ProfessionalServices_PromotionActivityLog',
                'product-promotion-activity-log.png'
            ),
            // The bundles come last: a bundle is only worth pitching once none of the
            // individual products above it has something more specific to say.
            //
            // The design ships one illustration for the three, so they share it rather
            // than carrying three copies of the same file.
            new Promotion(
                20,
                PremiumBundle::TEAM,
                'ProfessionalServices_PromotionProductTeamBundle',
                $teamBundleTrigger,
                'team_bundle',
                'ProfessionalServices_PromotionTeamBundle',
                'product-promotion-bundle.png'
            ),
            new Promotion(
                21,
                PremiumBundle::BUSINESS,
                'ProfessionalServices_PromotionProductBusinessBundle',
                $businessBundleTrigger,
                'business_bundle',
                'ProfessionalServices_PromotionBusinessBundle',
                'product-promotion-bundle.png'
            ),
            new Promotion(
                22,
                PremiumBundle::ENTERPRISE,
                'ProfessionalServices_PromotionProductEnterpriseBundle',
                $enterpriseBundleTrigger,
                'enterprise_bundle',
                'ProfessionalServices_PromotionEnterpriseBundle',
                'product-promotion-bundle.png'
            ),
        ];
    }

    /**
     * @return Promotion[] ordered by priority, most important first
     */
    public function getAllByPriority(): array
    {
        $promotions = $this->promotions;

        usort($promotions, static function (Promotion $a, Promotion $b): int {
            return $a->getPriority() <=> $b->getPriority();
        });

        return $promotions;
    }

    /**
     * Looks up a promotion by the plugin and trigger it was rendered with. Used to
     * validate the values a dismissal request carries.
     */
    public function findByPluginAndTrigger(string $pluginName, string $triggerName): ?Promotion
    {
        foreach ($this->promotions as $promotion) {
            if ($promotion->getPluginName() === $pluginName && $promotion->getTriggerName() === $triggerName) {
                return $promotion;
            }
        }

        return null;
    }
}
