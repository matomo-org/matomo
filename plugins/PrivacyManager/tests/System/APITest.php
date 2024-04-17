<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\PrivacyManager\API;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\MultipleSitesMultipleVisitsFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group PrivacyManager
 * @group APITest
 * @group Plugins
 */
class APITest extends SystemTestCase
{
    /**
     * @var MultipleSitesMultipleVisitsFixture
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = API::getInstance();
    }

    public function testExportDataSubjectsFailsWhenNoVisitsGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No list of visits given');

        $this->assertNull($this->api->exportDataSubjects(false));
    }

    public function testExportDataSubjectsWhenOneVisitGiven()
    {
        $result = $this->api->exportDataSubjects([['idsite' => '1', 'idvisit' => '1']]);
        $this->assertJsonResponse('exportDataSubject_oneVisitGiven', $result);
    }

    public function testExportDataSubjectsWhenNotMatchingVisitGiven()
    {
        $noMatch = $this->api->exportDataSubjects([['idsite' => '9999', 'idvisit' => '9999']]);
        $this->assertJsonResponse('exportDataSubject_noMatch', $noMatch);
    }

    public function testExportDataSubjectsWhenAllVisitsGiven()
    {
        $rows = Db::fetchAll('SELECT idsite, idvisit from ' . Common::prefixTable('log_visit'));
        $result = $this->api->exportDataSubjects($rows);
        $this->assertJsonResponse('exportDataSubject_allVisits', $result);
    }

    public function testExportDataSubjectsFailsWhenMissingIdSite()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No idsite key set for visit at index 1');

        $this->assertNull($this->api->exportDataSubjects([['idsite' => '9999', 'idvisit' => '9999'], []]));
    }

    private function assertJsonResponse($fileName, $result)
    {
        $result = MultipleSitesMultipleVisitsFixture::cleanResult($result);
        $result = json_encode($result, JSON_PRETTY_PRINT);
        $fileExpected = PIWIK_DOCUMENT_ROOT . '/plugins/PrivacyManager/tests/System/expected/' . $fileName . '.json';
        $fileProcessed = str_replace('/expected/', '/processed/', $fileExpected);
        \Piwik\Filesystem::mkdir(dirname($fileProcessed));
        file_put_contents($fileProcessed, $result);

        $this->assertJsonStringEqualsJsonFile($fileExpected, $result);
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $params['xmlFieldsToRemove'] = ['totalEcommerceRevenue', 'revenue', 'revenueDiscount'];
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $api = [
            'PrivacyManager.getAvailableVisitColumnsToAnonymize',
            'PrivacyManager.getAvailableLinkVisitActionColumnsToAnonymize',
        ];

        $apiToTest   = [];
        $apiToTest[] = [$api,
            [
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => ['day'],
                'testSuffix' => ''
            ]
        ];

        $apiToTest[] = [['Live.getLastVisitsDetails'],
            [
                'idSite'     => 'all',
                'date'       => self::$fixture->dateTime,
                'periods'    => ['year'],
                'otherRequestParameters' => ['filter_limit' => '-1'],
                'testSuffix' => 'allSites'
            ]
        ];

        $apiToTest[] = [['Live.getLastVisitsDetails'],
            [
                'idSite'     => 'all',
                'date'       => self::$fixture->dateTime,
                'periods'    => ['year'],
                'otherRequestParameters' => ['doNotFetchActions' => '1', 'filter_limit' => '-1'],
                'testSuffix' => 'allSites_noActions'
            ]
        ];

        return $apiToTest;
    }

    public function testFindDataSubjectsAllSites()
    {
        $this->runAnyApiTest('PrivacyManager.findDataSubjects', 'allSites', [
            'idSite'     => 'all',
            'segment'    => 'countryCode==CN',
        ]);
    }

    public function testFindDataSubjectsSpecificSite()
    {
        $this->runAnyApiTest('PrivacyManager.findDataSubjects', 'specificSite', [
            'idSite'     => '5',
            'segment'    => 'countryCode==CN',
        ]);
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

APITest::$fixture = new MultipleSitesMultipleVisitsFixture();
