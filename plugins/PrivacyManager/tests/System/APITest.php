<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public function setUp()
    {
        parent::setUp();
        $this->api = API::getInstance();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No list of visits given
     */
    public function test_exportDataSubjects_failsWhenNoVisitsGiven()
    {
        $this->assertNull($this->api->exportDataSubjects(false));
    }

    public function test_exportDataSubjects_whenOneVisitGiven()
    {
        $result = $this->api->exportDataSubjects([['idsite' => '1', 'idvisit' => '1']]);
        $this->assertJsonResponse('exportDataSubject_oneVisitGiven', $result);
    }

    public function test_exportDataSubjects_whenNotMatchingVisitGiven()
    {
        $noMatch = $this->api->exportDataSubjects([['idsite' => '9999', 'idvisit' => '9999']]);
        $this->assertJsonResponse('exportDataSubject_noMatch', $noMatch);
    }

    public function test_exportDataSubjects_whenAllVisitsGiven()
    {
        $rows = Db::fetchAll('SELECT idsite, idvisit from ' . Common::prefixTable('log_visit'));
        $result = $this->api->exportDataSubjects($rows);
        $this->assertJsonResponse('exportDataSubject_allVisits', $result);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No idsite key set for visit at index 1
     */
    public function test_exportDataSubjects_failsWhenMissingIdSite()
    {
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
        $params['xmlFieldsToRemove'] = array('totalEcommerceRevenue', 'revenue', 'revenueDiscount');
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $api = array(
            'PrivacyManager.getAvailableVisitColumnsToAnonymize',
            'PrivacyManager.getAvailableLinkVisitActionColumnsToAnonymize',
        );

        $apiToTest   = array();
        $apiToTest[] = array($api,
            array(
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => array('day'),
                'testSuffix' => ''
            )
        );

        $apiToTest[] = array(array('Live.getLastVisitsDetails'),
            array(
                'idSite'     => 'all',
                'date'       => self::$fixture->dateTime,
                'periods'    => array('year'),
                'otherRequestParameters' => array('filter_limit' => '-1'),
                'testSuffix' => 'allSites'
            )
        );

        $apiToTest[] = array(array('Live.getLastVisitsDetails'),
            array(
                'idSite'     => 'all',
                'date'       => self::$fixture->dateTime,
                'periods'    => array('year'),
                'otherRequestParameters' => array('doNotFetchActions' => '1', 'filter_limit' => '-1'),
                'testSuffix' => 'allSites_noActions'
            )
        );

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

APITest::$fixture = new MultipleSitesMultipleVisitsFixture();