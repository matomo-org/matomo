<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\Db;
use Piwik\Plugins\Goals\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Goals
 * @group Plugins
 */
class ModelTest extends IntegrationTestCase
{
    public function testCreateGoalRetriesAfterAnotherRequestUsesTheSameId()
    {
        $idSite = Fixture::createWebsite('2014-01-01 00:00:00');
        $goal = ['name' => 'first goal', 'match_attribute' => 'url', 'pattern' => 'example', 'pattern_type' => 'contains'];
        $model = new Model();
        $this->assertEquals(1, $model->createGoalForSite($idSite, $goal));

        $model = new class extends Model {
            public $attempts = 0;

            protected function getNextIdGoal($idSite)
            {
                if (++$this->attempts === 1) {
                    return 1;
                }
                return parent::getNextIdGoal($idSite);
            }
        };

        $goal['name'] = 'second goal';
        $this->assertEquals(2, $model->createGoalForSite($idSite, $goal));
        $this->assertSame(2, $model->attempts);
        $this->assertCount(2, $model->getActiveGoals([$idSite]));
    }

    public function testCreateGoalStopsAfterRepeatedCollisions()
    {
        $idSite = Fixture::createWebsite('2014-01-01 00:00:00');
        $goal = ['name' => 'existing goal', 'match_attribute' => 'url', 'pattern' => 'example', 'pattern_type' => 'contains'];
        (new Model())->createGoalForSite($idSite, $goal);
        $model = new class extends Model {
            public $attempts = 0;

            protected function getNextIdGoal($idSite)
            {
                ++$this->attempts;
                return 1;
            }
        };

        try {
            $model->createGoalForSite($idSite, $goal);
            $this->fail('Expected the duplicate entry error after repeated collisions');
        } catch (\Exception $e) {
            $this->assertTrue(Db::get()->isErrNo($e, 1062));
            $this->assertSame(10, $model->attempts);
        }
        $this->assertCount(1, $model->getActiveGoals([$idSite]));
    }

    public function testCreateGoalDoesNotRetryOtherDatabaseErrors()
    {
        $idSite = Fixture::createWebsite('2014-01-01 00:00:00');
        $model = new class extends Model {
            public $attempts = 0;

            protected function getNextIdGoal($idSite)
            {
                ++$this->attempts;
                return parent::getNextIdGoal($idSite);
            }
        };

        try {
            $model->createGoalForSite($idSite, ['nonexistent_goal_column' => 'invalid']);
            $this->fail('Expected an unknown column error');
        } catch (\Exception $e) {
            $this->assertTrue(Db::get()->isErrNo($e, 1054));
            $this->assertSame(1, $model->attempts);
        }
    }
}
