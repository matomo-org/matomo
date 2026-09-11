<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Integration\PluginPromotions;

use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TriggerResult;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 * @group Plugins
 */
class DailyTriggerCacheTest extends IntegrationTestCase
{
    private DailyTriggerCache $cache;

    /**
     * @var int Counts how often the expensive report evaluation actually ran.
     */
    private int $evaluations = 0;

    public function setUp(): void
    {
        parent::setUp();

        Date::$now = strtotime('2026-08-27 10:00:00 UTC');
        $this->cache = new DailyTriggerCache();
        $this->evaluations = 0;
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testTheSameDayIsNotEvaluatedTwice(): void
    {
        $this->cache->getOrEvaluate('bounce_rate', 1, $this->triggering());

        Date::$now = strtotime('2026-08-27 23:59:00 UTC');
        $result = $this->cache->getOrEvaluate('bounce_rate', 1, $this->triggering());

        $this->assertSame(1, $this->evaluations);
        $this->assertTrue($result->isTriggered());
        $this->assertSame('/pricing', $result->getContext()['url']);
        $this->assertSame('2026-08-17', $result->getPeriodStart());
    }

    public function testANewDayIsEvaluatedOnceAndOverwritesYesterday(): void
    {
        $this->cache->getOrEvaluate('bounce_rate', 1, $this->triggering());

        Date::$now = strtotime('2026-08-28 08:00:00 UTC');
        $this->cache->getOrEvaluate('bounce_rate', 1, $this->notTriggering());
        $result = $this->cache->getOrEvaluate('bounce_rate', 1, $this->notTriggering());

        $this->assertSame(2, $this->evaluations);
        $this->assertFalse($result->isTriggered());

        $stored = json_decode(Option::get(DailyTriggerCache::getOptionName('bounce_rate', 1)), true);
        $this->assertSame('2026-08-28', $stored['evaluationDate']);
    }

    /**
     * Without this a website that does not qualify would recompute its reports on every
     * single dashboard request of the day.
     */
    public function testNegativeResultsAreCachedToo(): void
    {
        $this->cache->getOrEvaluate('conversion_rate_funnels', 1, $this->notTriggering());
        $this->cache->getOrEvaluate('conversion_rate_funnels', 1, $this->notTriggering());

        $this->assertSame(1, $this->evaluations);

        $stored = json_decode(Option::get(DailyTriggerCache::getOptionName('conversion_rate_funnels', 1)), true);
        $this->assertFalse($stored['triggered']);
    }

    public function testResultsAreKeptPerWebsite(): void
    {
        $this->cache->getOrEvaluate('bounce_rate', 1, $this->triggering());
        $result = $this->cache->getOrEvaluate('bounce_rate', 2, $this->notTriggering());

        $this->assertSame(2, $this->evaluations);
        $this->assertFalse($result->isTriggered());
        $this->assertTrue($this->cache->getOrEvaluate('bounce_rate', 1, $this->notTriggering())->isTriggered());
    }

    public function testDeletingASiteRemovesEveryCachedTriggerForIt(): void
    {
        $this->cache->getOrEvaluate('bounce_rate', 1, $this->triggering());
        $this->cache->getOrEvaluate('conversion_rate_funnels', 1, $this->triggering());
        $this->cache->getOrEvaluate('bounce_rate', 2, $this->triggering());

        DailyTriggerCache::deleteForSite(1);

        $this->assertFalse(Option::get(DailyTriggerCache::getOptionName('bounce_rate', 1)));
        $this->assertFalse(Option::get(DailyTriggerCache::getOptionName('conversion_rate_funnels', 1)));
        $this->assertNotFalse(Option::get(DailyTriggerCache::getOptionName('bounce_rate', 2)));
    }

    private function triggering(): callable
    {
        return function (): TriggerResult {
            $this->evaluations++;

            return TriggerResult::triggered(
                ['url' => '/pricing', 'entryVisits' => 540, 'bounceRate' => 0.712],
                '2026-08-17',
                '2026-08-23'
            );
        };
    }

    private function notTriggering(): callable
    {
        return function (): TriggerResult {
            $this->evaluations++;

            return TriggerResult::notTriggered('2026-08-17', '2026-08-23');
        };
    }
}
