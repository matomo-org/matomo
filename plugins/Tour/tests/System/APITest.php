<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Tour\tests\System;

use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Plugins\Tour\Engagement\Challenge;
use Piwik\Plugins\Tour\tests\Fixtures\SimpleFixtureTrackFewVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Tour
 * @group APITest
 * @group Plugins
 */
class APITest extends SystemTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

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

    public function test_skipStep_alreadyCompleted()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Challenge already completed');

        Request::processRequest('Tour.skipChallenge', array('id' => 'track_data'));
    }

    public function test_skipStep_invalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Challenge not found');

        Request::processRequest('Tour.skipChallenge', array('id' => 'foobarbaz'));
    }

    public function test_getLevel_WhenNothingCompleted()
    {
        Piwik::addAction('API.Tour.getChallenges.end', function (&$challenges) {
            foreach ($challenges as &$challenge) {
                $challenge['isSkipped'] = false;
                $challenge['isCompleted'] = false;
            }
        });
        $this->runApiTests(array('Tour.getLevel'), array(
            'testSuffix' => 'nothingCompleted'
        ));
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

APITest::$fixture = new SimpleFixtureTrackFewVisits();
