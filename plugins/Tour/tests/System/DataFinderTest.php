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
use Piwik\Plugins\Tour\Dao\DataFinder;
use Piwik\Plugins\Tour\tests\Fixtures\SimpleFixtureTrackFewVisits;
use Piwik\Plugins\Goals\API as ApiGoals;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Tour
 * @group DataFinderTest
 * @group Plugins
 */
class DataFinderTest extends SystemTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @var DataFinder
     */
    private $dataFinder;

    public function setUp()
    {
        parent::setUp();
        $this->dataFinder = new DataFinder();
    }

    public function test_hasTracked()
    {
        $this->assertTrue($this->dataFinder->hasTrackedData());
    }

    public function test_hasCreatedGoal()
    {
        $this->assertFalse($this->dataFinder->hasCreatedGoal());

        $api = ApiGoals::getInstance();
        $api->addGoal(self::$fixture->idSite, 'My Goal', 'url', 'foobar', 'contains', $caseSensitive = false, $revenue = 0);

        $this->assertTrue($this->dataFinder->hasCreatedGoal());
    }

    public function test_hasAddedUser()
    {
        $this->assertFalse($this->dataFinder->hasAddedUser());

        Request::processRequest('UsersManager.addUser', array('userLogin' => 'myerwerwer', 'password' => '2342k4234234', 'email' => 'tesr@matomo.org'));
        $this->assertTrue($this->dataFinder->hasAddedUser());
    }

    public function test_hasAddedWebsite()
    {
        $this->assertFalse($this->dataFinder->hasAddedWebsite());

        Fixture::createWebsite('2016-03-03 00:00:00');

        $this->assertTrue($this->dataFinder->hasAddedWebsite());
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

DataFinderTest::$fixture = new SimpleFixtureTrackFewVisits();