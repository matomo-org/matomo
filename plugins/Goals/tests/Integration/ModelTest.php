<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\Goals\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Covers how the goal model stores the starred state of goals.
 *
 * @group Goals
 * @group Plugins
 * @group GoalsModelTest
 */
class ModelTest extends IntegrationTestCase
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var int
     */
    private $idSite;

    /**
     * @var int
     */
    private $otherIdSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new Model();
        $this->idSite = (int) Fixture::createWebsite('2014-01-01 00:00:00');
        $this->otherIdSite = (int) Fixture::createWebsite('2014-01-01 00:00:00');
    }

    public function testCreateGoalForSiteStoresAGoalAsNotStarredByDefault(): void
    {
        $idGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Newsletter'));

        self::assertSame(['deleted' => 0, 'starred' => 0], $this->getStoredState($this->idSite, $idGoal));
    }

    public function testCreateGoalForSiteStoresTheGivenStarredState(): void
    {
        $idGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Newsletter', ['starred' => 1]));

        self::assertSame(['deleted' => 0, 'starred' => 1], $this->getStoredState($this->idSite, $idGoal));
    }

    public function testSetGoalStarredStarsAndUnstarsAGoal(): void
    {
        $idGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Newsletter'));

        $this->model->setGoalStarred($this->idSite, $idGoal, true);
        self::assertSame(1, $this->getStoredState($this->idSite, $idGoal)['starred']);

        // setting the same state again keeps it
        $this->model->setGoalStarred($this->idSite, $idGoal, true);
        self::assertSame(1, $this->getStoredState($this->idSite, $idGoal)['starred']);

        $this->model->setGoalStarred($this->idSite, $idGoal, false);
        self::assertSame(0, $this->getStoredState($this->idSite, $idGoal)['starred']);
    }

    public function testSetGoalStarredOnlyChangesTheGivenGoalOfTheGivenSite(): void
    {
        $idGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Newsletter'));
        $idOtherGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Download'));
        // goal IDs are counted per site, so the other site has a goal with the same ID
        $idGoalOfOtherSite = (int) $this->model->createGoalForSite($this->otherIdSite, $this->goalRow('Newsletter'));
        self::assertSame($idGoal, $idGoalOfOtherSite);

        $this->model->setGoalStarred($this->idSite, $idGoal, true);

        self::assertSame(1, $this->getStoredState($this->idSite, $idGoal)['starred']);
        self::assertSame(0, $this->getStoredState($this->idSite, $idOtherGoal)['starred']);
        self::assertSame(0, $this->getStoredState($this->otherIdSite, $idGoalOfOtherSite)['starred']);
    }

    public function testSetGoalStarredDoesNotStarADeletedGoal(): void
    {
        $idGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Newsletter'));
        $this->model->deleteGoal($this->idSite, $idGoal);

        $this->model->setGoalStarred($this->idSite, $idGoal, true);

        self::assertSame(['deleted' => 1, 'starred' => 0], $this->getStoredState($this->idSite, $idGoal));
    }

    public function testDeleteGoalMarksTheGoalAsDeletedAndClearsItsStarredState(): void
    {
        $idGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Newsletter', ['starred' => 1]));
        $idOtherGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Download', ['starred' => 1]));

        $this->model->deleteGoal($this->idSite, $idGoal);

        // the row is kept, as goals are deleted softly
        self::assertSame(['deleted' => 1, 'starred' => 0], $this->getStoredState($this->idSite, $idGoal));
        self::assertSame(['deleted' => 0, 'starred' => 1], $this->getStoredState($this->idSite, $idOtherGoal));
        self::assertFalse($this->model->doesGoalExist($idGoal, $this->idSite));
    }

    public function testUpdateGoalKeepsTheStarredStateWhenItIsNotGiven(): void
    {
        $idGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Newsletter', ['starred' => 1]));

        $this->model->updateGoal($this->idSite, $idGoal, ['name' => 'Newsletter renamed', 'pattern' => 'news']);

        $goal = $this->model->getActiveGoal($this->idSite, $idGoal);
        self::assertSame('Newsletter renamed', $goal['name']);
        self::assertSame(1, (int) $goal['starred']);
    }

    public function testUpdateGoalStoresTheGivenStarredState(): void
    {
        $idGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Newsletter'));

        $this->model->updateGoal($this->idSite, $idGoal, ['name' => 'Newsletter', 'starred' => 1]);
        self::assertSame(1, $this->getStoredState($this->idSite, $idGoal)['starred']);

        $this->model->updateGoal($this->idSite, $idGoal, ['name' => 'Newsletter', 'starred' => 0]);
        self::assertSame(0, $this->getStoredState($this->idSite, $idGoal)['starred']);
    }

    public function testGetActiveGoalsReturnTheStarredStateAndNoDeletedGoals(): void
    {
        $idStarredGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Newsletter', ['starred' => 1]));
        $idGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Download'));
        $idDeletedGoal = (int) $this->model->createGoalForSite($this->idSite, $this->goalRow('Contact', ['starred' => 1]));
        $this->model->deleteGoal($this->idSite, $idDeletedGoal);

        $starredById = [];
        foreach ($this->model->getActiveGoals([$this->idSite]) as $goal) {
            $starredById[(int) $goal['idgoal']] = (int) $goal['starred'];
        }

        self::assertSame([$idStarredGoal => 1, $idGoal => 0], $starredById);
        self::assertSame(1, (int) $this->model->getActiveGoal($this->idSite, $idStarredGoal)['starred']);
    }

    /**
     * @param array<string, int|string> $values
     * @return array<string, int|string>
     */
    private function goalRow(string $name, array $values = []): array
    {
        return array_merge([
            'name' => $name,
            'description' => '',
            'match_attribute' => 'url',
            'pattern' => strtolower($name),
            'pattern_type' => 'contains',
            'case_sensitive' => 0,
            'allow_multiple' => 0,
            'revenue' => 0,
            'deleted' => 0,
            'event_value_as_revenue' => 0,
        ], $values);
    }

    /**
     * @return array{deleted: int, starred: int}
     */
    private function getStoredState(int $idSite, int $idGoal): array
    {
        $row = Db::fetchRow(
            'SELECT deleted, starred FROM ' . Common::prefixTable('goal') . ' WHERE idsite = ? AND idgoal = ?',
            [$idSite, $idGoal]
        );

        return ['deleted' => (int) $row['deleted'], 'starred' => (int) $row['starred']];
    }
}
