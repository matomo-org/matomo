<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\API\Request;
use Piwik\Date;

/**
 * testing a the auto suggest API for all known segments
 */
class Test_Piwik_Integration_AutoSuggestAPITest extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    protected static $processed = 0;
    protected static $skipped = array();

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

        // on Travis this test seg faults for no reason eg: https://github.com/piwik/piwik/commit/94d0ce393b2c496cda571571a0425af846406fda
        $isPhp53 = strpos(PHP_VERSION, '5.3') == 0;
        if($isPhp53) {
            $this->markTestSkipped("Skipping this test as it seg faults on php 5.3 (bug triggered on travis)");
        }
        // we will test all segments from all plugins
        Fixture::loadAllPlugins();

        $idSite = self::$fixture->idSite;
        $apiForTesting = array();

        $segments = \Piwik\Plugins\API\API::getInstance()->getSegmentsMetadata(self::$fixture->idSite);
        foreach ($segments as $segment) {
            $apiForTesting[] = $this->getApiForTestingForSegment($idSite, $segment['segment']);
        }

        // Skip the test on Mysqli as it fails due to rounding Float errors on latitude/longitude
        $skipThisTest = getenv('MYSQL_ADAPTER') != 'MYSQLI';

        if ($skipThisTest) {
            $apiForTesting[] = array('Live.getLastVisitsDetails',
                                     array('idSite' => $idSite,
                                           'date'   => '1998-07-12,today',
                                           'period' => 'range',
                                           'otherRequestParameters' => array('filter_limit' => 1000)));

        }
        return $apiForTesting;
    }


    /**
     * @param $idSite
     * @param $segment
     * @return array
     */
    protected function getApiForTestingForSegment($idSite, $segment)
    {
        return array('API.getSuggestedValuesForSegment',
                     array('idSite'                 => $idSite,
                           'testSuffix'             => '_' . $segment,
                           'otherRequestParameters' => array('segmentName' => $segment)));
    }

    /**
     * @depends      testApi
     * @dataProvider getAnotherApiForTesting
     * @group        Integration
     */
    public function testAnotherApi($api, $params)
    {
        // Get the top segment value
        $request = new Request(
            'method=API.getSuggestedValuesForSegment'
                . '&segmentName=' . $params['segmentToComplete']
                . '&idSite=' . $params['idSite']
                . '&format=php&serialize=0'
        );
        $response = $request->process();
        $this->checkRequestResponse($response);
        $topSegmentValue = @$response[0];

        if ($topSegmentValue !== false && !is_null($topSegmentValue)) {
            // Now build the segment request
            $segmentValue = rawurlencode(html_entity_decode($topSegmentValue));
            $params['segment'] = $params['segmentToComplete'] . '==' . $segmentValue;
            unset($params['segmentToComplete']);
            $this->runApiTests($api, $params);
            self::$processed++;
        } else {
            self::$skipped[] = $params['segmentToComplete'];
        }

    }

    public function getAnotherApiForTesting()
    {
        $apiForTesting = array();
        $segments = \Piwik\Plugins\API\API::getInstance()->getSegmentsMetadata(self::$fixture->idSite);
        foreach ($segments as $segment) {
            $apiForTesting[] = array('VisitsSummary.get',
                                     array('idSite'            => self::$fixture->idSite,
                                           'date'              => date("Y-m-d", strtotime(self::$fixture->dateTime)) . ',today',
                                           'period'            => 'range',
                                           'testSuffix'        => '_' . $segment['segment'],
                                           'segmentToComplete' => $segment['segment']));
        }
        return $apiForTesting;
    }

    /**
     * @group Integration
     * @depends      testAnotherApi
     */
    public function testCheckOtherTestsWereComplete()
    {
        // Check that only a few haven't been tested specifically (these are all custom variables slots since we only test slot 1, 2, 5 (see the fixture))
        $maximumSegmentsToSkip = 11;
        $this->assertTrue(count(self::$skipped) <= $maximumSegmentsToSkip, 'SKIPPED ' . count(self::$skipped) . ' segments --> some segments had no "auto-suggested values"
            but we should try and test the autosuggest for all new segments. Segments skipped were: ' . implode(', ', self::$skipped));

        // and check that most others have been tested
        $minimumSegmentsToTest = 46;
        $this->assertTrue(self::$processed >= $minimumSegmentsToTest, 'PROCESSED ' . self::$processed . ' segments --> it seems some segments "auto-suggested values" haven\'t been tested as we were expecting');
    }
}

Test_Piwik_Integration_AutoSuggestAPITest::$fixture = new Test_Piwik_Fixture_ManyVisitsWithGeoIP();
Test_Piwik_Integration_AutoSuggestAPITest::$fixture->dateTime = Date::yesterday()->subDay(30)->getDatetime();
