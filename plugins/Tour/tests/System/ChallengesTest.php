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
use Piwik\Date;
use Piwik\Plugins\AbTesting\Model\Experiments;
use Piwik\Plugins\AbTesting\tests\Fixtures\ExperimentsFixture;
use Piwik\Plugins\Tour\Dao\DataFinder;
use Piwik\Plugins\Tour\Engagement\Challenge;
use Piwik\Plugins\Tour\Engagement\Challenges;
use Piwik\Plugins\Tour\tests\Fixtures\SimpleFixtureTrackFewVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Tour
 * @group ChallengesTest
 * @group Plugins
 */
class ChallengesTest extends SystemTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @var Challenges
     */
    private $part1;

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToTest   = array();

        $apiToTest[] = array(array('Tour.getLevel', 'Tour.getChallenges'),
            array(
                'idSite'  => 1,
            )
        );

        return $apiToTest;
    }

    public function test_skipStep()
    {
        $steps = Request::processRequest('Tour.getChallenges');

        $this->assertFalse($steps[1]['isSkipped']);

        Request::processRequest('Tour.skipChallenge', array('id' => $steps[1]['id']));

        Challenge::clearCache();

        $steps = Request::processRequest('Tour.getChallenges');
        $this->assertTrue($steps[1]['isSkipped']);
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

ChallengesTest::$fixture = new SimpleFixtureTrackFewVisits();