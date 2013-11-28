<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin\tests;
use Piwik\Plugins\ExamplePlugin\tests\fixtures\SimpleFixtureTrackFewVisits;

/**
 * @group ExamplePlugin
 * @group SimpleIntegrationTest
 * @group Plugins
 */
class SimpleIntegrationTest extends \IntegrationTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     * @group SimpleIntegrationTest
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $api = array('API.get',
                     'Goals.getItemsSku');

        $apiToTest = array();
        $apiToTest[] = array($api,
            array(  'idSite' => 1,
                    'date' => self::$fixture->dateTime,
                    'periods' => array('day'),
                    'testSuffix' => ''

        ));

        return $apiToTest;
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

SimpleIntegrationTest::$fixture = new SimpleFixtureTrackFewVisits();

