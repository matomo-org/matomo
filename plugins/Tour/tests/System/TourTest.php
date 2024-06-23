<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Tour\tests\System;

use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugins\Tour\Engagement\ChallengeAddedAnnotation;
use Piwik\Plugins\Tour\Engagement\ChallengeInvitedUser;
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

    public function testHasCreatedGoal()
    {
        $goal = StaticContainer::get(ChallengeCreatedGoal::class);

        $this->assertFalse($goal->isCompleted(Piwik::getCurrentUserLogin()));

        Request::processRequest('Goals.addGoal', array(
            'idSite' => self::$fixture->idSite, 'name' => 'MyGoal', 'matchAttribute' => 'url', 'pattern' => 'foobar', 'patternType' => 'contains'
        ));

        $this->assertTrue($goal->isCompleted(Piwik::getCurrentUserLogin()));
    }

    public function testHasAddedUser()
    {
        $user = StaticContainer::get(ChallengeInvitedUser::class);
        $this->assertFalse($user->isCompleted(Piwik::getCurrentUserLogin()));

        Request::processRequest('UsersManager.inviteUser', array('userLogin' => 'myerwerwer', 'email' => 'tesr@matomo.org', 'initialIdSite' => 1));

        $this->assertTrue($user->isCompleted(Piwik::getCurrentUserLogin()));
    }

    public function testHasAddedAnnotation()
    {
        $annotation = StaticContainer::get(ChallengeAddedAnnotation::class);
        $this->assertFalse($annotation->isCompleted(Piwik::getCurrentUserLogin()));

        Request::processRequest('Annotations.add', array(
            'idSite' => self::$fixture->idSite, 'date' => Date::now()->getDatetime(), 'note' => 'foo bar'));

        $this->assertTrue($annotation->isCompleted(Piwik::getCurrentUserLogin()));
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
