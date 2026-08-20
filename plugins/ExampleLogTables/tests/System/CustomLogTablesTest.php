<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

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
    public function testApi($api, $params): void
    {
        $this->runApiTests($api, $params);
    }

    /**
     * @dataProvider getSegmentsToTest
     */
    public function testNoApiReturnsError(string $segment): void
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
            'testSuffix'   => '',
        ];

        $testConfig = new ApiTestConfig($params);
        $testRequests = $this->getTestRequestsCollection('all', $testConfig, 'all');

        foreach ($testRequests->getRequestUrls() as $apiId => $requestUrl) {
            $response = Response::loadFromApi($params, $requestUrl, false);
            $decoded = json_decode($response->getResponseText(), true);

            if (is_array($decoded) && isset($decoded['result']) && $decoded['result'] == 'error') {
                $this->fail(
                    'API returned an error when requesting ' . http_build_query($requestUrl)
                    . "\nMessage: " . $decoded['message']
                );
            }
        }
    }

    public function getSegmentsToTest(): array
    {
        return [
            ['userPlan==pro'],
            ['accountIsPaying==1'],
        ];
    }

    public function getApiForTesting(): array
    {
        $dateTime = self::$fixture->dateTime;
        $idSite1 = self::$fixture->idSite;

        return [
            [[
                'Actions.get',
                'UserId.getUsers',
                'VisitsSummary.get',
                'ExampleLogTables.getPayingAccountVisits',
            ], [
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => 'month',
                'setDateLastN' => false,
                'segment'      => 'userPlan==pro',
                'testSuffix'   => '_pro'],
            ],
            [[
                'Actions.get',
                'UserId.getUsers',
                'VisitsSummary.get',
                'ExampleLogTables.getPayingAccountVisits',
            ], [
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => 'month',
                'setDateLastN' => false,
                'segment'      => 'userPlan==free',
                'testSuffix'   => '_free'],
            ],
            [[
                'Actions.get',
                'UserId.getUsers',
                'VisitsSummary.get',
                'ExampleLogTables.getPayingAccountVisits',
            ], [
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => 'month',
                'setDateLastN' => false,
                'segment'      => 'accountIsPaying==1',
                'testSuffix'   => '_paying'],
            ],
            [[
                'Actions.get',
                'UserId.getUsers',
                'VisitsSummary.get',
                'ExampleLogTables.getPayingAccountVisits',
            ], [
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => 'month',
                'setDateLastN' => false,
                'testSuffix'   => '_all'],
            ],
        ];
    }

    public static function getOutputPrefix()
    {
        return 'ExampleLogTables';
    }

    public static function getPathToTestDirectory()
    {
        return __DIR__;
    }
}

CustomLogTablesTest::$fixture = new VisitsWithUserIdAndCustomData();
