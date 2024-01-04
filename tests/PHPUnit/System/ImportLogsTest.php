<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Access;
use Piwik\Plugins\SitesManager\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\ManySitesImportedLogs;
use Piwik\Tests\Framework\TestingEnvironmentVariables;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestSet;

/**
 * Tests the log importer.
 *
 * @group ImportLogsTest
 * @group Core
 */
class ImportLogsTest extends SystemTestCase
{
    /** @var ManySitesImportedLogs */
    public static $fixture = null; // initialized below class definition

    public function setUp(): void
    {
        parent::setUp();

        $this->resetTestingEnvironmentChanges();
    }

    /**
     * @dataProvider getApiForTesting
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

            array('Referrers.getNumberOfDistinctWebsites', array('idSite'  => self::$fixture->idSite,
                                                                 'date'    => '2012-08-09',
                                                                 'setDateLastN' => true,
                                                                 'periods' => 'day')),

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

            [
                ['Live.getLastVisitsDetails', 'VisitsSummary.get'],
                [
                    'idSite'                 => self::$fixture->idSite,
                    'date'                   => '2012-08-09',
                    'periods'                => 'month',
                    'segment'                => 'pageUrl=@/docs/,pageUrl=@/blog;pageUrl!@/docs/manage',
                    'otherRequestParameters' => [
                        'filter_limit' => 5,
                    ],
                    'testSuffix'             => '_complexActionSegment',
                ],
            ],
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
            ),
            'xmlFieldsToRemove' => array('fingerprint')
        ));

        // imported via --replay-tracking --idsite=3  should ignore idSite from logs and use fixed idSite instead
        $apis[] = array($apiMethods, array(
            'idSite'  => 3,
            'date'    => '2012-08-09,2014-04-01',
            'periods' => 'range',
            'otherRequestParameters' => array(
                'filter_limit' => 1000
            ),
            'testSuffix' => '_siteIdThree_TrackedUsingLogReplayWithFixedSiteId'
        ));

        return $apis;
    }

    /**
     * NOTE: This test must be last since the new sites that get added are added in
     *       random order.
     * NOTE: This test combines two tests in order to avoid executing the log importer another time.
     *       If the log importer were refactored, the invalid requests test could be a unit test in
     *       python.
     */
    public function test_LogImporter_CreatesSitesWhenDynamicResolverUsed_AndReportsOnInvalidRequests()
    {
        $this->simulateInvalidTrackerRequest();

        $output = self::$fixture->logVisitsWithDynamicResolver($maxPayloadSize = 3);

        // reload access so new sites are viewable
        Access::getInstance()->setSuperUserAccess(true);

        // make sure sites aren't created twice
        $piwikDotNet = API::getInstance()->getSitesIdFromSiteUrl('http://piwik.net');
        $this->assertEquals(1, count($piwikDotNet));

        $anothersiteDotCom = API::getInstance()->getSitesIdFromSiteUrl('http://anothersite.com');
        $this->assertEquals(1, count($anothersiteDotCom));

        $whateverDotCom = API::getInstance()->getSitesIdFromSiteUrl('http://whatever.com');
        $this->assertEquals(1, count($whateverDotCom));

        // make sure invalid requests are reported correctly
        self::assertStringContainsString('The Matomo tracker identified 2 invalid requests on lines: 10, 11', $output);
        self::assertStringContainsString("The following lines were not tracked by Matomo, either due to a malformed tracker request or error in the tracker:\n\n10, 11", $output);
    }

    public function test_LogImporter_RetriesWhenServerFails()
    {
        $this->simulateTrackerFailure();

        $logFile = PIWIK_INCLUDE_PATH . '/tests/resources/access-logs/fake_logs_enable_all.log';

        $options = array(
            '--idsite'                    => self::$fixture->idSite,
            '--token-auth'                => Fixture::getTokenAuth(),
            '--retry-max-attempts'        => 5,
            '--retry-delay'               => 1
        );

        $output = Fixture::executeLogImporter($logFile, $options, $allowFailure = true);
        $output = implode("\n", $output);

        for ($i = 2; $i != 6; ++$i) {
            self::assertStringContainsString("Retrying request, attempt number $i", $output);
        }

        self::assertStringNotContainsString("Retrying request, attempt number 6", $output);

        self::assertStringContainsString("Max number of attempts reached, server is unreachable!", $output);
    }

    private function simulateTrackerFailure()
    {
        $testingEnvironment = new TestingEnvironmentVariables();
        $testingEnvironment->_triggerTrackerFailure = true;
        $testingEnvironment->save();
    }

    public static function getOutputPrefix()
    {
        return 'ImportLogs';
    }

    private function resetTestingEnvironmentChanges()
    {
        $testingEnvironment = new TestingEnvironmentVariables();
        $testingEnvironment->_triggerTrackerFailure = null;
        $testingEnvironment->_triggerInvalidRequests = null;
        $testingEnvironment->save();
    }

    private function simulateInvalidTrackerRequest()
    {
        $testEnvironment = new TestingEnvironmentVariables();
        $testEnvironment->_triggerInvalidRequests = true;
        $testEnvironment->save();
    }

    public static function provideContainerConfigBeforeClass()
    {
        $result = array();
        $observers = array();

        $testingEnvironment = new TestingEnvironmentVariables();
        if ($testingEnvironment->_triggerTrackerFailure) {
            $observers[] = array('Tracker.newHandler', \Piwik\DI::value(function () {
                @http_response_code(500);

                throw new \Exception("injected exception");
            }));
        }

        if ($testingEnvironment->_triggerInvalidRequests) {
            // we trigger an invalid request by checking for triggerInvalid=1 in a request, and if found replacing the
            // request w/ a request that has an nonexistent idsite
            $observers[] = array('Tracker.initRequestSet', \Piwik\DI::value(function (RequestSet $requestSet) {
                $requests = $requestSet->getRequests();
                foreach ($requests as $index => $request) {
                    $url = $request->getParam('url');
                    if (strpos($url, 'triggerInvalid=1') !== false) {
                        $newParams = $request->getParams();
                        $newParams['idsite'] = 1000;

                        $requests[$index] = new Request($newParams);
                    }
                }
                $requestSet->setRequests($requests);
            }));
        }

        if (!empty($observers)) {
            $result['observers.global'] = \Piwik\DI::add($observers);
        }

        return $result;
    }
}

ImportLogsTest::$fixture = new ManySitesImportedLogs();
ImportLogsTest::$fixture->includeIisWithCustom = true;
ImportLogsTest::$fixture->includeNetscaler = true;
ImportLogsTest::$fixture->includeCloudfront = true;
ImportLogsTest::$fixture->includeCloudfrontRtmp = true;
ImportLogsTest::$fixture->includeNginxJson = true;
ImportLogsTest::$fixture->includeApiCustomVarMapping = true;
