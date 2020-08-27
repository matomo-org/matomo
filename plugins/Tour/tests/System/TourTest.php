<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\tests\System;

use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Plugins\Tour\Engagement\ChallengeAddedAnnotation;
use Piwik\Plugins\Tour\Engagement\ChallengeAddedUser;
use Piwik\Plugins\Tour\Engagement\ChallengeCreatedGoal;
use Piwik\Plugins\Tour\tests\Fixtures\SimpleFixtureTrackFewVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Tour
 * @group TourTest
 * @group Plugins
 */
class TourTest extends SystemTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_hasCreatedGoal()
    {
        $goal = StaticContainer::get(ChallengeCreatedGoal::class);

        $this->assertFalse($goal->isCompleted());

        Request::processRequest('Goals.addGoal', array(
            'idSite' => self::$fixture->idSite, 'name' => 'MyGoal', 'matchAttribute' => 'url', 'pattern' => 'foobar', 'patternType' => 'contains'
        ));

        $this->assertTrue($goal->isCompleted());
    }

    public function test_hasAddedUser()
    {
        $user = StaticContainer::get(ChallengeAddedUser::class);
        $this->assertFalse($user->isCompleted());

        Request::processRequest('UsersManager.addUser', array('userLogin' => 'myerwerwer', 'password' => '2342k4234234', 'email' => 'tesr@matomo.org'));

        $this->assertTrue($user->isCompleted());
    }

    public function test_hasAddedAnnotation()
    {
        $annotation = StaticContainer::get(ChallengeAddedAnnotation::class);
        $this->assertFalse($annotation->isCompleted());

        Request::processRequest('Annotations.add', array(
            'idSite' => self::$fixture->idSite, 'date' => Date::now()->getDatetime(), 'note' => 'foo bar'));

        $this->assertTrue($annotation->isCompleted());
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

}

TourTest::$fixture = new SimpleFixtureTrackFewVisits();