<?php
namespace Piwik\Plugins\ExampleLogTables\tests\System;

use Piwik\Plugins\ExampleLogTables\tests\Fixtures\VisitsWithUserIdAndCustomData;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Framework\TestRequest\ApiTestConfig;
use Piwik\Tests\Framework\TestRequest\Response;

/**
 * Testing Custom Log Tables
 *
 * @group ExampleLogTables
 * @group Plugins
 */
class CustomLogTablesTest extends SystemTestCase
{
    /**
     * @var VisitsWithUserIdAndCustomData
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    /**
     * @dataProvider getSegmentsToTest
     */
    public function testNoApiReturnsError($segment)
    {
        self::expectNotToPerformAssertions();

        $dateTime = self::$fixture->dateTime;
        $idSite1 = self::$fixture->idSite;

        $params = [
            'idSite'       => $idSite1,
            'date'         => $dateTime,
            'periods'      => 'month',
            'setDateLastN' => false,
            'format'       => 'JSON',
            'segment'      => $segment,
            'testSuffix'   => ''
        ];

        $testConfig = new ApiTestConfig($params);
        $testRequests = $this->getTestRequestsCollection('all', $testConfig, 'all');

        foreach ($testRequests->getRequestUrls() as $apiId => $requestUrl) {
            $response = Response::loadFromApi($params, $requestUrl, false);
            $decoded = json_decode($response->getResponseText(), true);

            if (is_array($decoded) && isset($decoded['result']) && $decoded['result'] == 'error') {
                $this->fail('API returned an error when requesting ' . http_build_query($requestUrl) . "\nMessage: " . $decoded['message']);
            }
        }
    }

    public function getSegmentsToTest()
    {
        return [
            ['attrgender==men'],
            ['isadmin==1'],
        ];
    }

    public function getApiForTesting()
    {
        $dateTime = self::$fixture->dateTime;
        $idSite1 = self::$fixture->idSite;

        $result = [
            [[
                'Actions.get',
                'UserId.getUsers',
                'VisitsSummary.get'
            ], [
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => 'month',
                'setDateLastN' => false,
                'segment'      => 'attrgender==men',
                'testSuffix'   => '_men']
            ],
            [[
                'Actions.get',
                'UserId.getUsers',
                'VisitsSummary.get'
            ], [
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => 'month',
                'setDateLastN' => false,
                'segment'      => 'attrgender==women',
                'testSuffix'   => '_women']
            ],
            [[
                'Actions.get',
                'UserId.getUsers',
                'VisitsSummary.get'
            ], [
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => 'month',
                'setDateLastN' => false,
                'segment'      => 'isadmin==1',
                'testSuffix'   => '_admin']
            ],
            [[
                'Actions.get',
                'UserId.getUsers',
                'VisitsSummary.get'
            ], [
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => 'month',
                'setDateLastN' => false,
                'testSuffix'   => '_all']
            ],
        ];

        return $result;
    }

    public static function getOutputPrefix()
    {
        return 'ExampleLogTables';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

CustomLogTablesTest::$fixture = new VisitsWithUserIdAndCustomData();
