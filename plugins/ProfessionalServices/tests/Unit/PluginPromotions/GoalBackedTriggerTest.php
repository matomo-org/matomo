<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions;

use PHPUnit\Framework\TestCase;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ArchivedReportReader;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\GoalBackedTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleConversionChannelsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\WeeklyGoalMetrics;

/**
 * The archive gate every goal backed promotion shares.
 *
 * The triggers' own tests cover which goal they pick; this covers what they answer before
 * there is anything to pick from. {@see WeeklyGoalMetrics::read()} reads only archives that
 * already exist, so a week that has not finished archiving comes back indistinguishable
 * from a website with no goal worth promoting - and a "does not qualify" is remembered by
 * {@see DailyTriggerCache} until the website's local midnight, while a provisional answer
 * is evaluated again on the next dashboard.
 *
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class GoalBackedTriggerTest extends TestCase
{
    /**
     * @dataProvider getGoalBackedTriggers
     * @param class-string<GoalBackedTrigger> $className
     */
    public function testItCannotTellYetWhileLastWeekIsStillBeingArchived(string $className): void
    {
        $result = $this->evaluate($className, $archiveExists = false);

        $this->assertTrue(
            $result->isProvisional(),
            $className . ' must report that it cannot tell yet, or the promotion it backs stays'
                . ' hidden for the rest of the day over a week that finished archiving moments later'
        );
        $this->assertFalse($result->isTriggered());
    }

    /**
     * The gate has to be asked at all. A trigger that names no archive never reaches
     * {@see \Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TriggerResult::notYetKnown()},
     * whatever the state of the archive.
     *
     * Asked with the archives in place, because the gate stops at the first one that is
     * missing - so a trigger naming several would only ever be seen to ask for the first.
     *
     * @dataProvider getGoalBackedTriggers
     * @param class-string<GoalBackedTrigger> $className
     */
    public function testItNamesAnArchiveItDependsOn(string $className): void
    {
        $asked = [];

        $this->evaluate($className, true, $asked);

        $this->assertContains(
            'Goals',
            $asked,
            $className . ' reads last week\'s goal metrics, so it depends on the Goals archive'
        );
    }

    /**
     * With the archive in place the gate is out of the way, and a website with no goals is
     * an answer worth remembering for the day rather than one to keep retrying.
     *
     * @dataProvider getGoalBackedTriggers
     * @param class-string<GoalBackedTrigger> $className
     */
    public function testItSettlesOnceTheArchiveIsThere(string $className): void
    {
        $result = $this->evaluate($className, $archiveExists = true);

        $this->assertFalse($result->isProvisional());
        $this->assertFalse($result->isTriggered());
    }

    /**
     * @return array<string, array{class-string<GoalBackedTrigger>}>
     */
    public function getGoalBackedTriggers(): array
    {
        return [
            'Funnels' => [LowConversionRateTrigger::class],
            'A/B Testing' => [HighConversionRateTrigger::class],
            'Multi Channel Conversion Attribution' => [MultipleConversionChannelsTrigger::class],
        ];
    }

    /**
     * @param class-string<GoalBackedTrigger> $className
     * @param string[] $asked receives the plugin names whose archives were gated on
     */
    private function evaluate(string $className, bool $archiveExists, array &$asked = [])
    {
        // What read() returns for a week that was never archived, and equally for a website
        // with no goals at all: the two are the same answer to the caller.
        $goalMetrics = $this->createMock(WeeklyGoalMetrics::class);
        $goalMetrics->method('read')->willReturn(['siteVisits' => 0, 'goals' => []]);

        $reader = $this->createMock(ArchivedReportReader::class);
        $reader->method('hasCompletedArchive')->willReturnCallback(
            function (int $idSite, string $pluginName) use (&$asked, $archiveExists): bool {
                $asked[] = $pluginName;

                return $archiveExists;
            }
        );

        $reportPeriod = $this->createMock(ReportPeriod::class);
        $reportPeriod->method('forSite')->willReturn(PeriodFactory::build('week', '2026-09-21'));

        // Straight through to the evaluation, so the outcome under test is the trigger's own
        // rather than one the cache has already answered.
        $cache = $this->createMock(DailyTriggerCache::class);
        $cache->method('getOrEvaluate')->willReturnCallback(
            static function (string $triggerName, int $idSite, callable $evaluate) {
                return $evaluate();
            }
        );

        $trigger = new $className($goalMetrics, $reader, $reportPeriod, $cache);

        return $trigger->evaluate(1);
    }
}
