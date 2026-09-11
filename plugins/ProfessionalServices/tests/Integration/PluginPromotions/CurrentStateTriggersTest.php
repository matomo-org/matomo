<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Integration\PluginPromotions;

use Piwik\Plugin\Manager;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ScheduledReportsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SegmentsTrigger;
use Piwik\Plugins\ScheduledReports\API as ScheduledReportsApi;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The two triggers that look at the current state rather than at a report. Both are
 * scoped to the selected website and to the requesting user.
 *
 * @group ProfessionalServices
 * @group PluginPromotions
 * @group Plugins
 */
class CurrentStateTriggersTest extends IntegrationTestCase
{
    private const SITE_ONE = 1;

    private const SITE_TWO = 2;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2026-01-01 00:00:00');
        Fixture::createWebsite('2026-01-01 00:00:00');

        $this->asUser('alice');
    }

    public function testSegmentsTriggerNeedsFiveAccessibleSegments(): void
    {
        $trigger = $this->makeSegmentsTrigger();

        $this->addSegments(self::SITE_ONE, 4, 'alice');
        $this->assertFalse($trigger->evaluate(self::SITE_ONE)->isTriggered(), '4 segments must not trigger');

        $this->addSegments(self::SITE_ONE, 1, 'alice', 4);
        $result = $trigger->evaluate(self::SITE_ONE);

        $this->assertTrue($result->isTriggered(), '5 segments must trigger');
        $this->assertSame(5, $result->getContext()['count']);
    }

    public function testSegmentsOfOtherUsersDoNotCount(): void
    {
        $trigger = $this->makeSegmentsTrigger();

        $this->addSegments(self::SITE_ONE, 4, 'alice');
        $this->addSegments(self::SITE_ONE, 4, 'bob', 100);

        $this->asUser('alice');

        // Eight segments exist for this website but only four are Alice's.
        $this->assertFalse($trigger->evaluate(self::SITE_ONE)->isTriggered());
    }

    public function testSegmentsOfAnotherWebsiteDoNotCount(): void
    {
        $trigger = $this->makeSegmentsTrigger();

        $this->addSegments(self::SITE_TWO, 6, 'alice');

        $this->assertTrue($trigger->evaluate(self::SITE_TWO)->isTriggered());
        $this->assertFalse($trigger->evaluate(self::SITE_ONE)->isTriggered());
    }

    public function testScheduledReportsTriggerNeedsThreeReports(): void
    {
        $trigger = $this->makeScheduledReportsTrigger();

        $this->addScheduledReports(self::SITE_ONE, 2);
        $this->assertFalse($trigger->evaluate(self::SITE_ONE)->isTriggered(), '2 reports must not trigger');

        $this->addScheduledReports(self::SITE_ONE, 1);
        $result = $trigger->evaluate(self::SITE_ONE);

        $this->assertTrue($result->isTriggered(), '3 reports must trigger');
        $this->assertSame(3, $result->getContext()['count']);
    }

    public function testScheduledReportsAreCountedPerWebsiteAndPerUser(): void
    {
        $trigger = $this->makeScheduledReportsTrigger();

        $this->addScheduledReports(self::SITE_ONE, 4);
        $this->addScheduledReports(self::SITE_TWO, 1);

        $this->assertTrue($trigger->evaluate(self::SITE_ONE)->isTriggered());
        $this->assertFalse($trigger->evaluate(self::SITE_TWO)->isTriggered());

        // Bob has none of his own, so Alice's reports must not trigger anything for him.
        $this->asUser('bob');
        $this->assertFalse($trigger->evaluate(self::SITE_ONE)->isTriggered());
    }

    public function testNothingTriggersWhenTheSourcePluginIsDeactivated(): void
    {
        $this->addSegments(self::SITE_ONE, 6, 'alice');
        $this->addScheduledReports(self::SITE_ONE, 4);

        $manager = $this->createMock(Manager::class);
        $manager->method('isPluginActivated')->willReturn(false);

        $this->assertFalse((new SegmentsTrigger($manager))->evaluate(self::SITE_ONE)->isTriggered());
        $this->assertFalse((new ScheduledReportsTrigger($manager))->evaluate(self::SITE_ONE)->isTriggered());
    }

    private function makeSegmentsTrigger(): SegmentsTrigger
    {
        return new SegmentsTrigger($this->makeManagerWithActivePlugin('SegmentEditor'));
    }

    private function makeScheduledReportsTrigger(): ScheduledReportsTrigger
    {
        return new ScheduledReportsTrigger($this->makeManagerWithActivePlugin('ScheduledReports'));
    }

    private function makeManagerWithActivePlugin(string $pluginName): Manager
    {
        $manager = $this->createMock(Manager::class);
        $manager->method('isPluginActivated')->willReturnCallback(
            static function (string $name) use ($pluginName): bool {
                return $name === $pluginName;
            }
        );

        return $manager;
    }

    private function addSegments(int $idSite, int $count, string $login, int $offset = 0): void
    {
        $this->asUser($login);

        for ($i = 0; $i < $count; $i++) {
            $index = $offset + $i;
            SegmentEditorApi::getInstance()->add('Segment ' . $login . ' ' . $index, 'countryCode==FR', $idSite);
        }
    }

    private function addScheduledReports(int $idSite, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            ScheduledReportsApi::getInstance()->addReport(
                $idSite,
                'Report ' . $idSite . ' ' . uniqid(),
                'day',
                0,
                'email',
                'pdf',
                ['VisitsSummary_get'],
                ['displayFormat' => 1, 'emailMe' => true, 'additionalEmails' => [], 'evolutionGraph' => false]
            );
        }

        // addReport() does not invalidate the per request static cache of getReports(),
        // unlike updateReport() and deleteReport().
        ScheduledReportsApi::$cache = [];
    }

    private function asUser(string $login): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = $login;
        FakeAccess::$idSitesView = [self::SITE_ONE, self::SITE_TWO];
        FakeAccess::$idSitesWrite = [self::SITE_ONE, self::SITE_TWO];

        // getReports() keeps a per request static cache keyed on the arguments, not on the
        // current login, so it has to be cleared when the acting user changes.
        ScheduledReportsApi::$cache = [];
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
