<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\API\Request;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\NoAccessException;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;

/**
 * @group Goals
 * @group Plugins
 * @group StarredGoalsTest
 */
class StarredGoalsTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var int
     */
    private $idSite;

    /**
     * @var int
     */
    private $otherIdSite;

    /**
     * @var array<string, int> goal name => goal ID
     */
    private $idGoals = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();

        FakeAccess::clearAccess(true);

        $this->idSite = Fixture::createWebsite('2014-01-01 00:00:00');
        foreach (['Newsletter', 'Download', 'Contact'] as $name) {
            $this->idGoals[$name] = (int) $this->api->addGoal($this->idSite, $name, 'url', strtolower($name), 'contains');
        }

        $this->otherIdSite = Fixture::createWebsite('2014-01-01 00:00:00');
        $this->api->addGoal($this->otherIdSite, 'Other site goal', 'url', 'other', 'contains');
    }

    public function provideContainerConfig()
    {
        return ['Piwik\Access' => new FakeAccess()];
    }

    public function testANewGoalIsNotStarred(): void
    {
        $goal = $this->api->getGoal($this->idSite, $this->idGoals['Newsletter']);

        self::assertIsArray($goal);
        self::assertFalse($goal['starred']);
        self::assertSame(0, $this->getStoredStarredValue($this->idSite, $this->idGoals['Newsletter']));

        foreach ($this->api->getGoals($this->idSite) as $goal) {
            self::assertFalse($goal['starred']);
        }
    }

    public function testStarGoalAndUnstarGoalAreSavedAndReturnedByGetGoalAndGetGoals(): void
    {
        $idGoal = $this->idGoals['Download'];

        self::assertSame(['success' => true], $this->api->starGoal($this->idSite, $idGoal));

        self::assertTrue($this->api->getGoal($this->idSite, $idGoal)['starred']);
        self::assertSame([$idGoal], $this->getStarredGoalIds($this->idSite));
        self::assertSame(1, $this->getStoredStarredValue($this->idSite, $idGoal));

        self::assertSame(['success' => true], $this->api->unstarGoal($this->idSite, $idGoal));

        self::assertFalse($this->api->getGoal($this->idSite, $idGoal)['starred']);
        self::assertSame([], $this->getStarredGoalIds($this->idSite));
        self::assertSame(0, $this->getStoredStarredValue($this->idSite, $idGoal));
    }

    public function testStarringOrUnstarringTwiceKeepsTheState(): void
    {
        $idGoal = $this->idGoals['Download'];

        $this->api->starGoal($this->idSite, $idGoal);
        $this->api->starGoal($this->idSite, $idGoal);
        self::assertTrue($this->api->getGoal($this->idSite, $idGoal)['starred']);

        $this->api->unstarGoal($this->idSite, $idGoal);
        $this->api->unstarGoal($this->idSite, $idGoal);
        self::assertFalse($this->api->getGoal($this->idSite, $idGoal)['starred']);
    }

    public function testGetGoalsKeepsItsOrderAndStructureWhenGoalsAreStarred(): void
    {
        $before = $this->api->getGoals($this->idSite);

        $this->api->starGoal($this->idSite, $this->idGoals['Contact']);

        $after = $this->api->getGoals($this->idSite);

        // indexed by goal ID and in the order of the goal IDs, the starred goal is not moved to the front
        self::assertSame(array_keys($before), array_keys($after));
        self::assertSame(array_values($this->idGoals), array_keys($after));
        self::assertSame(['Newsletter', 'Download', 'Contact'], array_column($after, 'name'));
    }

    /**
     * @dataProvider getStarMethods
     */
    public function testAUserWithViewAccessCannotStarOrUnstar(string $method): void
    {
        FakeAccess::clearAccess(false, [], [$this->idSite], 'viewUser');

        $this->expectException(NoAccessException::class);
        $this->api->$method($this->idSite, $this->idGoals['Download']);
    }

    /**
     * @dataProvider getStarMethods
     */
    public function testTheAnonymousUserCannotStarOrUnstar(string $method): void
    {
        FakeAccess::clearAccess(false, [], [$this->idSite], 'anonymous');

        $this->expectException(NoAccessException::class);
        $this->api->$method($this->idSite, $this->idGoals['Download']);
    }

    /**
     * @dataProvider getStarMethods
     */
    public function testAUserWithoutAccessToTheSiteCannotStarOrUnstar(string $method): void
    {
        FakeAccess::clearAccess(false, [], [], 'otherUser', [$this->otherIdSite]);

        $this->expectException(NoAccessException::class);
        $this->api->$method($this->idSite, $this->idGoals['Download']);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function getStarMethods(): iterable
    {
        yield 'starGoal' => ['starGoal'];
        yield 'unstarGoal' => ['unstarGoal'];
    }

    /**
     * @dataProvider getAccessAllowedToStar
     * @param callable(int): void $grantAccess
     */
    public function testUsersWithWriteAdminOrSuperUserAccessCanStarAndUnstar(callable $grantAccess): void
    {
        $idGoal = $this->idGoals['Download'];

        $grantAccess($this->idSite);
        $this->api->starGoal($this->idSite, $idGoal);
        self::assertTrue($this->api->getGoal($this->idSite, $idGoal)['starred']);

        $this->api->unstarGoal($this->idSite, $idGoal);
        self::assertFalse($this->api->getGoal($this->idSite, $idGoal)['starred']);
    }

    /**
     * @return iterable<string, array{callable(int): void}>
     */
    public function getAccessAllowedToStar(): iterable
    {
        yield 'write access' => [function (int $idSite): void {
            FakeAccess::clearAccess(false, [], [], 'writeUser', [$idSite]);
        }];
        yield 'admin access' => [function (int $idSite): void {
            FakeAccess::clearAccess(false, [$idSite], [], 'adminUser');
        }];
        yield 'super user access' => [function (int $idSite): void {
            FakeAccess::clearAccess(true);
        }];
    }

    /**
     * @dataProvider getStarMethods
     */
    public function testAnUnknownGoalFailsWithAClearMessage(string $method): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("There is no goal with id '999' for site with id '{$this->idSite}'.");

        $this->api->$method($this->idSite, 999);
    }

    /**
     * @dataProvider getStarMethods
     */
    public function testADeletedGoalFailsWithAClearMessage(string $method): void
    {
        $idGoal = $this->idGoals['Download'];
        $this->api->deleteGoal($this->idSite, $idGoal);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("There is no goal with id '$idGoal' for site with id '{$this->idSite}'.");

        $this->api->$method($this->idSite, $idGoal);
    }

    /**
     * @dataProvider getStarMethods
     */
    public function testAGoalOfAnotherSiteFailsWithAClearMessage(string $method): void
    {
        // the goal ID exists for the first site only
        $idGoal = $this->idGoals['Contact'];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("There is no goal with id '$idGoal' for site with id '{$this->otherIdSite}'.");

        $this->api->$method($this->otherIdSite, $idGoal);
    }

    public function testDeletingAStarredGoalClearsItsStoredStarredState(): void
    {
        $idGoal = $this->idGoals['Download'];
        $this->api->starGoal($this->idSite, $idGoal);
        self::assertSame(1, $this->getStoredStarredValue($this->idSite, $idGoal));

        $this->api->deleteGoal($this->idSite, $idGoal);

        self::assertNull($this->api->getGoal($this->idSite, $idGoal));
        self::assertSame([], $this->getStarredGoalIds($this->idSite));
        // the goal is soft deleted, its row is kept
        self::assertSame(0, $this->getStoredStarredValue($this->idSite, $idGoal));
    }

    public function testUpdatingAGoalKeepsItsStarredState(): void
    {
        $idGoal = $this->idGoals['Download'];
        $this->api->starGoal($this->idSite, $idGoal);

        $this->api->updateGoal($this->idSite, $idGoal, 'Download renamed', 'title', 'other pattern', 'exact', true, 5);

        $goal = $this->api->getGoal($this->idSite, $idGoal);
        self::assertSame('Download renamed', $goal['name']);
        self::assertSame('title', $goal['match_attribute']);
        self::assertTrue($goal['starred']);

        // and an update does not star a goal either
        $idOtherGoal = $this->idGoals['Contact'];
        $this->api->updateGoal($this->idSite, $idOtherGoal, 'Contact renamed', 'url', 'contact', 'contains');
        self::assertFalse($this->api->getGoal($this->idSite, $idOtherGoal)['starred']);
    }

    public function testAddGoalCanStarTheNewGoal(): void
    {
        $idStarredGoal = (int) $this->api->addGoal($this->idSite, 'Starred right away', 'url', 'starred', 'contains', false, false, false, '', false, true);
        $idGoal = (int) $this->api->addGoal($this->idSite, 'Not starred', 'url', 'not-starred', 'contains', false, false, false, '', false, false);

        self::assertTrue($this->api->getGoal($this->idSite, $idStarredGoal)['starred']);
        self::assertFalse($this->api->getGoal($this->idSite, $idGoal)['starred']);
        self::assertSame([$idStarredGoal], $this->getStarredGoalIds($this->idSite));
    }

    public function testUpdateGoalCanStarAndUnstarTheGoal(): void
    {
        $idGoal = $this->idGoals['Contact'];

        $this->api->updateGoal($this->idSite, $idGoal, 'Contact', 'url', 'contact', 'contains', false, false, false, '', false, true);
        self::assertTrue($this->api->getGoal($this->idSite, $idGoal)['starred']);

        // not giving the starred state keeps it
        $this->api->updateGoal($this->idSite, $idGoal, 'Contact renamed', 'url', 'contact', 'contains');
        self::assertTrue($this->api->getGoal($this->idSite, $idGoal)['starred']);

        $this->api->updateGoal($this->idSite, $idGoal, 'Contact renamed', 'url', 'contact', 'contains', false, false, false, '', false, false);
        self::assertFalse($this->api->getGoal($this->idSite, $idGoal)['starred']);
    }

    public function testAddGoalAndUpdateGoalAcceptTheStarredStateThroughTheApiRequestLayer(): void
    {
        $goalParameters = ['idSite' => $this->idSite, 'matchAttribute' => 'url', 'pattern' => 'request', 'patternType' => 'contains'];

        $idGoal = (int) Request::processRequest('Goals.addGoal', $goalParameters + ['name' => 'Added starred', 'starred' => '1']);
        self::assertTrue($this->api->getGoal($this->idSite, $idGoal)['starred']);

        $idOtherGoal = (int) Request::processRequest('Goals.addGoal', $goalParameters + ['name' => 'Added without star']);
        self::assertFalse($this->api->getGoal($this->idSite, $idOtherGoal)['starred']);

        // an update without the parameter, as the Manage Goals form sends it, keeps the star
        Request::processRequest('Goals.updateGoal', $goalParameters + ['idGoal' => $idGoal, 'name' => 'Renamed']);
        self::assertTrue($this->api->getGoal($this->idSite, $idGoal)['starred']);

        Request::processRequest('Goals.updateGoal', $goalParameters + ['idGoal' => $idGoal, 'name' => 'Renamed', 'starred' => 'false']);
        self::assertFalse($this->api->getGoal($this->idSite, $idGoal)['starred']);

        Request::processRequest('Goals.updateGoal', $goalParameters + ['idGoal' => $idOtherGoal, 'name' => 'Starred later', 'starred' => 'true']);
        self::assertTrue($this->api->getGoal($this->idSite, $idOtherGoal)['starred']);
    }

    public function testAStarredValueThatIsNoBooleanNeitherStarsNorUnstarsAGoal(): void
    {
        // like the other boolean parameters of the API, an unusable value falls back to the default
        $goalParameters = ['idSite' => $this->idSite, 'matchAttribute' => 'url', 'pattern' => 'invalid', 'patternType' => 'contains'];

        $idGoal = (int) Request::processRequest('Goals.addGoal', $goalParameters + ['name' => 'Invalid', 'starred' => 'maybe']);
        self::assertFalse($this->api->getGoal($this->idSite, $idGoal)['starred']);

        $this->api->starGoal($this->idSite, $idGoal);
        Request::processRequest('Goals.updateGoal', $goalParameters + ['idGoal' => $idGoal, 'name' => 'Invalid', 'starred' => 'maybe']);
        self::assertTrue($this->api->getGoal($this->idSite, $idGoal)['starred']);
    }

    public function testStarringAGoalDoesNotAffectTheGoalsOfAnotherSite(): void
    {
        // both sites have a goal with the ID 1
        self::assertSame(1, $this->idGoals['Newsletter']);
        self::assertArrayHasKey(1, $this->api->getGoals($this->otherIdSite));

        $this->api->starGoal($this->idSite, 1);

        self::assertSame([1], $this->getStarredGoalIds($this->idSite));
        self::assertSame([], $this->getStarredGoalIds($this->otherIdSite));
        self::assertSame(0, $this->getStoredStarredValue($this->otherIdSite, 1));

        $this->api->starGoal($this->otherIdSite, 1);
        $this->api->unstarGoal($this->idSite, 1);

        self::assertSame([], $this->getStarredGoalIds($this->idSite));
        self::assertSame([1], $this->getStarredGoalIds($this->otherIdSite));
    }

    public function testTheStarredStateIsSharedByAllUsersOfTheSite(): void
    {
        $idGoal = $this->idGoals['Contact'];

        FakeAccess::clearAccess(false, [], [], 'writeUser', [$this->idSite]);
        $this->api->starGoal($this->idSite, $idGoal);

        FakeAccess::clearAccess(false, [], [$this->idSite], 'viewUser');
        self::assertSame([$idGoal], $this->getStarredGoalIds($this->idSite));
        self::assertTrue($this->api->getGoal($this->idSite, $idGoal)['starred']);

        FakeAccess::clearAccess(false, [$this->idSite], [], 'adminUser');
        self::assertSame([$idGoal], $this->getStarredGoalIds($this->idSite));

        FakeAccess::clearAccess(true);
        self::assertSame([$idGoal], $this->getStarredGoalIds($this->idSite));
    }

    public function testStarringAGoalUpdatesTheGoalsInTheTrackerCache(): void
    {
        $idGoal = $this->idGoals['Download'];

        $cached = Cache::getCacheWebsiteAttributes($this->idSite);
        self::assertFalse($cached['goals'][$idGoal]['starred']);

        $this->api->starGoal($this->idSite, $idGoal);

        $cached = Cache::getCacheWebsiteAttributes($this->idSite);
        self::assertTrue($cached['goals'][$idGoal]['starred']);
    }

    public function testStarringAGoalDoesNotInvalidateArchives(): void
    {
        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $invalidationsBefore = $this->countArchiveInvalidations();
        $rememberedBefore = $invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->api->starGoal($this->idSite, $this->idGoals['Download']);
        $this->api->unstarGoal($this->idSite, $this->idGoals['Download']);

        self::assertSame($invalidationsBefore, $this->countArchiveInvalidations());
        self::assertSame($rememberedBefore, $invalidator->getRememberedArchivedReportsThatShouldBeInvalidated());
    }

    public function testStarAndUnstarCanBeCalledThroughTheApiRequestLayer(): void
    {
        $idGoal = $this->idGoals['Newsletter'];

        $result = Request::processRequest('Goals.starGoal', ['idSite' => $this->idSite, 'idGoal' => $idGoal]);
        self::assertSame(['success' => true], $result);

        $goals = Request::processRequest('Goals.getGoals', ['idSite' => $this->idSite, 'filter_limit' => '-1']);
        self::assertTrue($goals[$idGoal]['starred']);

        $result = Request::processRequest('Goals.unstarGoal', ['idSite' => $this->idSite, 'idGoal' => $idGoal]);
        self::assertSame(['success' => true], $result);

        $goals = Request::processRequest('Goals.getGoals', ['idSite' => $this->idSite, 'filter_limit' => '-1']);
        self::assertFalse($goals[$idGoal]['starred']);
    }

    /**
     * @return list<int>
     */
    private function getStarredGoalIds(int $idSite): array
    {
        $starred = array_filter($this->api->getGoals($idSite), function (array $goal): bool {
            return true === $goal['starred'];
        });

        return array_values(array_map('intval', array_column($starred, 'idgoal')));
    }

    private function getStoredStarredValue(int $idSite, int $idGoal): int
    {
        return (int) Db::fetchOne(
            'SELECT starred FROM ' . Common::prefixTable('goal') . ' WHERE idsite = ? AND idgoal = ?',
            [$idSite, $idGoal]
        );
    }

    private function countArchiveInvalidations(): int
    {
        return (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations'));
    }
}
