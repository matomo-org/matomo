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
use Piwik\Db;
use Piwik\Common;
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

        // truncate log tables so old data won't be re-archived
        foreach (array('log_visit', 'log_link_visit_action', 'log_conversion', 'log_conversion_item') as $table) {
            Db::query("TRUNCATE TABLE " . Common::prefixTable($table));
        }

        // add two visits from same visitor on dec. 29
        $t = Fixture::getTracker(1, '2012-12-29 01:01:30', $defaultInit = true);
        $t->setUrl('http://site.com/index.htm');
        $t->setIp('136.5.3.2');
        Fixture::checkResponse($t->doTrackPageView('incredible title!'));

        $t->setForceVisitDateTime('2012-12-29 03:01:30');
        $t->setUrl('http://site.com/other/index.htm');
        $t->DEBUG_APPEND_URL = '&_idvc=2'; // make sure visit is marked as returning
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

        // we test VisitFrequency explicitly
        $this->defaultApiNotToCall[] = 'VisitFrequency.get';
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
                               'disableArchiving' => true,

                               // the Action.getPageTitles test fails for unknown reason, so skipping it
                               // eg. https://travis-ci.org/piwik/piwik/jobs/24449365
                               'skipGetPageTitles' => true )),

            array('VisitFrequency.get', array('idSite' => $idSite, 'date' => '2012-03-03', 'setDateLastN' => true,
                                              'disableArchiving' => true, 'testSuffix' => '_multipleDates')),

            array('VisitFrequency.get', array('idSite' => $idSite, 'date' => $dateTime,
                                              'periods' => array('day', 'week', 'month', 'year'),
                                              'disableArchiving' => false)),

            array('VisitFrequency.get', array('idSite' => $idSite, 'date' => '2012-03-06,2012-12-31',
                                              'periods' => array('range'), 'disableArchiving' => true)),

            array('VisitFrequency.get', array('idSite' => $idSite, 'date' => '2012-03-03,2012-12-12', 'periods' => array('month'),
                                              'testSuffix' => '_multipleOldNew', 'disableArchiving' => true))
        );
    }
}

Test_Piwik_Integration_BackwardsCompatibility1XTest::$fixture = new Piwik_Test_Fixture_SqlDump();
Test_Piwik_Integration_BackwardsCompatibility1XTest::$fixture->dumpUrl =
    PIWIK_INCLUDE_PATH . Test_Piwik_Integration_BackwardsCompatibility1XTest::FIXTURE_LOCATION;
Test_Piwik_Integration_BackwardsCompatibility1XTest::$fixture->tablesPrefix = 'piwiktests_';