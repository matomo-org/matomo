<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TriggerResult;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class TriggerResultTest extends TestCase
{
    public function testATriggeredResultSurvivesTheCacheRoundTrip(): void
    {
        $result = TriggerResult::triggered(
            ['url' => '/pricing', 'entryVisits' => 540, 'bounceRate' => 0.712],
            '2026-08-17',
            '2026-08-23'
        );

        $restored = TriggerResult::fromArray(json_decode(json_encode($result->toArray()), true));

        $this->assertTrue($restored->isTriggered());
        $this->assertSame(['url' => '/pricing', 'entryVisits' => 540, 'bounceRate' => 0.712], $restored->getContext());
        $this->assertSame('2026-08-17', $restored->getPeriodStart());
        $this->assertSame('2026-08-23', $restored->getPeriodEnd());
    }

    public function testANegativeResultSurvivesTheCacheRoundTrip(): void
    {
        $result = TriggerResult::notTriggered('2026-08-17', '2026-08-23');

        $restored = TriggerResult::fromArray(json_decode(json_encode($result->toArray()), true));

        $this->assertFalse($restored->isTriggered());
        $this->assertSame([], $restored->getContext());
        $this->assertSame('2026-08-17', $restored->getPeriodStart());
    }

    public function testAnEmptyCacheEntryIsReadAsNotTriggered(): void
    {
        $restored = TriggerResult::fromArray([]);

        $this->assertFalse($restored->isTriggered());
        $this->assertSame([], $restored->getContext());
        $this->assertNull($restored->getPeriodStart());
    }
}
