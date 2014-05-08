<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Access;
use Piwik\Plugins\SitesManager\API;

/**
 * Tests the log importer.
 */
class Test_Piwik_Integration_ImportLogs extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition
    
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
        $apis = array(
            array('all', array('idSite'  => self::$fixture->idSite,
                               'date'    => '2012-08-09',
                               'periods' => 'month')),

            array('MultiSites.getAll', array('idSite'   => self::$fixture->idSite,
                                             'date'     => '2012-08-09',
                                             'periods'  => array('month'),
                                             'setDateLastN' => true,
                                             'otherRequestParameters' => array('enhanced' => 1),
                                             'testSuffix' => '_withEnhancedAndLast7')),

            // report generated from custom log format including generation time
            array('Actions.getPageUrls', array('idSite'  => self::$fixture->idSite,
                                               'date'    => '2012-09-30',
                                               'periods' => 'day')),

            array('VisitsSummary.get', array('idSite'     => self::$fixture->idSite2,
                                             'date'       => '2012-08-09',
                                             'periods'    => 'month',
                                             'testSuffix' => '_siteIdTwo_TrackedUsingLogReplay')),
        );

        // Running a few interesting tests for Log Replay use case
        $apiMethods = array();
        if (getenv('MYSQL_ADAPTER') != 'MYSQLI') {
            // Mysqli rounds latitude/longitude
            $apiMethods = array('Live.getLastVisitsDetails');
        }
        $apiMethods[] = 'Actions';
        $apiMethods[] = 'VisitorInterest';
        $apiMethods[] = 'VisitFrequency';
        $apis[] = array($apiMethods, array(
            'idSite'  => self::$fixture->idSite,
            'date'    => '2012-08-09,2014-04-01',
            'periods' => 'range',
            'otherRequestParameters' => array(
                'filter_limit' => 1000
        )));
        return $apis;
    }

    /**
     * @group        Integration
     *
     * 
     * NOTE: This test must be last since the new sites that get added are added in
     *       random order.
     */
    public function testDynamicResolverSitesCreated()
    {
        self::$fixture->logVisitsWithDynamicResolver();

        // reload access so new sites are viewable
        Access::getInstance()->setSuperUserAccess(true);

        // make sure sites aren't created twice
        $piwikDotNet = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.net');
        $this->assertEquals(1, count($piwikDotNet));

        $anothersiteDotCom = API::getInstance()->getSitesIdFromSiteUrl('http://anothersite.com');
        $this->assertEquals(1, count($anothersiteDotCom));

        $whateverDotCom = API::getInstance()->getSitesIdFromSiteUrl('http://whatever.com');
        $this->assertEquals(1, count($whateverDotCom));
    }

    public static function getOutputPrefix()
    {
        return 'ImportLogs';
    }
}

Test_Piwik_Integration_ImportLogs::$fixture = new Test_Piwik_Fixture_ManySitesImportedLogs();

