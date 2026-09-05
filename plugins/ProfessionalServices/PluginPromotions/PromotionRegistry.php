<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\BounceRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ScheduledReportsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SegmentsTrigger;

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
        ScheduledReportsTrigger $scheduledReportsTrigger
    ) {
        $this->promotions = [
            new Promotion(
                1,
                'CustomReports',
                'ProfessionalServices_PromotionProductCustomReports',
                $segmentsTrigger,
                'custom_reports',
                'ProfessionalServices_PromotionSegments',
                'product-promotion-custom-reports.png'
            ),
            new Promotion(
                2,
                'HeatmapSessionRecording',
                'ProfessionalServices_PromotionProductHeatmapSessionRecording',
                $bounceRateTrigger,
                'heatmap_session_recording',
                'ProfessionalServices_PromotionBounceRate',
                'product-promotion-heatmap-session-recording.png'
            ),
            new Promotion(
                3,
                'Funnels',
                'ProfessionalServices_PromotionProductFunnels',
                $lowConversionRateTrigger,
                'funnels',
                'ProfessionalServices_PromotionConversionRate',
                'product-promotion-funnels.png'
            ),
            new Promotion(
                4,
                'AbTesting',
                'ProfessionalServices_PromotionProductAbTesting',
                $highConversionRateTrigger,
                'ab_testing',
                'ProfessionalServices_PromotionConversions',
                'product-promotion-ab-testing.png'
            ),
            new Promotion(
                5,
                'CustomReports',
                'ProfessionalServices_PromotionProductCustomReports',
                $scheduledReportsTrigger,
                'custom_reports',
                'ProfessionalServices_PromotionScheduledReports',
                'product-promotion-custom-reports.png'
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
