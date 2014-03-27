<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyApi;
use Piwik\Updater;
use Piwik\Plugins\CoreUpdater\CoreUpdater;
use \Fixture;

/**
 * Tests that Piwik 2.0 works w/ data from Piwik 1.12.
 */
class Test_Piwik_Integration_BackwardsCompatibility1XTest extends IntegrationTestCase
{
    const FIXTURE_LOCATION = '/tests/resources/piwik-1.13-dump.sql';

    public static $fixture = null; // initialized below class

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::updateDatabase();
        Fixture::loadAllPlugins();

        // add two visits from same visitor on dec. 29
        $t = Fixture::getTracker(1, '2012-12-29 01:01:30', $defaultInit = true);
        $t->setUrl('http://site.com/index.htm');
        Fixture::checkResponse($t->doTrackPageView('incredible title!'));

        $t->setForceVisitDateTime('2012-12-29 03:01:30');
        $t->setUrl('http://site.com/other/index.htm');
        Fixture::checkResponse($t->doTrackPageView('other incredible title!'));

        // launch archiving
        VisitFrequencyApi::getInstance()->get(1, 'year', '2012-12-29');
    }

    private static function updateDatabase()
    {
        $updater = new Updater();
        $componentsWithUpdateFile = CoreUpdater::getComponentUpdates($updater);
        if (empty($componentsWithUpdateFile)) {
            throw new \Exception("Failed to update pre-2.0 database (nothing to update).");
        }

        $result = CoreUpdater::updateComponents($updater, $componentsWithUpdateFile);
        if (!empty($result['coreError'])
            && !empty($result['warnings'])
            && !empty($result['errors'])
        ) {
            throw new \Exception("Failed to update pre-2.0 database (errors or warnings found): " . print_r($result, true));
        }
    }

    public function setUp()
    {
        parent::setUp();

        $this->defaultApiNotToCall[] = 'Referrers';

        // changes made to SQL dump to test VisitFrequency change the day of week
        $this->defaultApiNotToCall[] = 'VisitTime.getByDayOfWeek';
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = 1;
        $dateTime = '2012-03-06 11:22:33';

        return array(
            array('all', array('idSite' => $idSite, 'date' => $dateTime,
                               'compareAgainst' => 'OneVisitorTwoVisits',
                               'disableArchiving' => true)),

            array('VisitFrequency.get', array('idSite' => $idSite, 'date' => '2012-03-03', 'setDateLastN' => true,
                                              'disableArchiving' => true)),

            array('VisitFrequency.get', array('idSite' => $idSite, 'date' => $dateTime, 'periods' => array('year'),
                                              'disableArchiving' => true)),

            array('VisitFrequency.get', array('idSite' => $idSite, 'date' => '2012-03-06,2012-12-31',
                                              'periods' => array('range'), 'disableArchiving' => true))
        );
    }
}

Test_Piwik_Integration_BackwardsCompatibility1XTest::$fixture = new Piwik_Test_Fixture_SqlDump();
Test_Piwik_Integration_BackwardsCompatibility1XTest::$fixture->dumpUrl =
    PIWIK_INCLUDE_PATH . Test_Piwik_Integration_BackwardsCompatibility1XTest::FIXTURE_LOCATION;
Test_Piwik_Integration_BackwardsCompatibility1XTest::$fixture->tablesPrefix = 'piwiktests_';
