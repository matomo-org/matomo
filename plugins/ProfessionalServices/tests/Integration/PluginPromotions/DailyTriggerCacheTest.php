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

    /**
     * A trigger that throws is cached as "did not fire" before the failure is passed on.
     * Otherwise a reliably broken trigger - a missing plugin, a report that throws - would
     * repeat its archive reads on every dashboard request for every user, indefinitely,
     * while being logged only at debug level.
     */
    public function testAFailingTriggerIsCachedSoItIsNotRetriedAllDay(): void
    {
        $throwing = function (): TriggerResult {
            $this->evaluations++;

            throw new \RuntimeException('the report blew up');
        };

        try {
            $this->cache->getOrEvaluate('bounce_rate', 1, $throwing);
            $this->fail('the failure must reach the caller so it can be logged');
        } catch (\RuntimeException $e) {
            $this->assertSame('the report blew up', $e->getMessage());
        }

        // Same day, same site: the stored negative answers instead of running it again.
        $result = $this->cache->getOrEvaluate('bounce_rate', 1, $throwing);

        $this->assertSame(1, $this->evaluations, 'the failing trigger must not run twice in a day');
        $this->assertFalse($result->isTriggered());

        // A new day retries it, so a transient failure is not cached forever.
        Date::$now = strtotime('2026-08-28 10:00:00 UTC');

        try {
            $this->cache->getOrEvaluate('bounce_rate', 1, $throwing);
        } catch (\RuntimeException $e) {
            // expected
        }

        $this->assertSame(2, $this->evaluations);
    }

    /**
     * A promotion that could not be judged because archiving had not finished must not be
     * held back for the rest of the day once it has.
     *
     * "This website's data does not qualify" is worth remembering until midnight. "The
     * archive is not ready" is a different answer, and remembering it meant a promotion
     * whose reports finished archiving a minute later stayed hidden until the next day.
     */
    public function testAnAnswerTheTriggerCouldNotSettleYetIsNotRemembered(): void
    {
        $archiveReady = false;
        $evaluate = function () use (&$archiveReady): TriggerResult {
            $this->evaluations++;

            if (!$archiveReady) {
                return TriggerResult::notYetKnown('2026-08-17', '2026-08-23');
            }

            return TriggerResult::triggered(['url' => '/pricing'], '2026-08-17', '2026-08-23');
        };

        $this->assertFalse($this->cache->getOrEvaluate('bounce_rate', 1, $evaluate)->isTriggered());
        $this->assertSame(1, $this->evaluations);

        // Still the same day, and archiving has since finished.
        $archiveReady = true;
        $result = $this->cache->getOrEvaluate('bounce_rate', 1, $evaluate);

        $this->assertTrue($result->isTriggered(), 'the promotion must appear once the archive is there');
        $this->assertSame('/pricing', $result->getContext()['url']);
        $this->assertSame(2, $this->evaluations, 'the provisional answer must not have been remembered');

        // And now that it is settled, it is remembered for the rest of the day.
        $this->cache->getOrEvaluate('bounce_rate', 1, $evaluate);
        $this->assertSame(2, $this->evaluations);
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

        $this->cache->deleteForSite(1);

        $this->assertFalse(Option::get(DailyTriggerCache::getOptionName('bounce_rate', 1)));
        $this->assertFalse(Option::get(DailyTriggerCache::getOptionName('conversion_rate_funnels', 1)));
        $this->assertNotFalse(Option::get(DailyTriggerCache::getOptionName('bounce_rate', 2)));
    }

    /**
     * A website's entries are read in one query and then answered from memory, so deleting
     * them has to empty that memory too. Otherwise the website would keep being handed
     * outcomes that no longer exist for the rest of the request.
     */
    public function testDeletingASiteAlsoForgetsWhatWasReadForIt(): void
    {
        $this->cache->getOrEvaluate('bounce_rate', 1, $this->triggering());
        $this->cache->getOrEvaluate('bounce_rate', 2, $this->triggering());

        $this->cache->deleteForSite(1);

        $this->assertSame(2, $this->evaluations);

        // Gone, so it has to be worked out again.
        $this->cache->getOrEvaluate('bounce_rate', 1, $this->triggering());
        $this->assertSame(3, $this->evaluations);

        // The other website was not touched, and is still answered from what was read.
        $this->cache->getOrEvaluate('bounce_rate', 2, $this->triggering());
        $this->assertSame(3, $this->evaluations);
    }

    /**
     * Every trigger's entry for a website is fetched at once, so the pattern that fetches
     * them has to match the names that are written - and only those. A neighbouring site id
     * sharing a leading digit is the way that goes wrong.
     */
    public function testOneWebsitesEntriesAreNotReadForAnother(): void
    {
        $this->cache->getOrEvaluate('bounce_rate', 1, $this->triggering());
        $this->cache->getOrEvaluate('bounce_rate', 12, $this->notTriggering());

        $this->assertSame(2, $this->evaluations);
        $this->assertTrue($this->cache->getOrEvaluate('bounce_rate', 1, $this->triggering())->isTriggered());
        $this->assertFalse($this->cache->getOrEvaluate('bounce_rate', 12, $this->triggering())->isTriggered());
        $this->assertSame(2, $this->evaluations);
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
