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
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\PromotionTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ScheduledReportsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SegmentsTrigger;

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
            $this->makeTrigger(ScheduledReportsTrigger::class, ScheduledReportsTrigger::NAME)
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
            ['AbTesting', 'conversion_rate_ab'],
            ['CustomReports', 'scheduled_reports'],
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
