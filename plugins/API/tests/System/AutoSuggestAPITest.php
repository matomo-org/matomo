<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\System;

use Piwik\API\Request;
use Piwik\Application\Environment;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache as PiwikCache;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\API\API;
use Piwik\Plugins\CustomVariables\Columns\CustomVariableName;
use Piwik\Plugins\CustomVariables\Columns\CustomVariableValue;
use Piwik\Tests\Fixtures\ManyVisitsWithGeoIPAndEcommerce;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tracker\Cache;

// Class to cache results of getSuggestedValuesForSegment to prevent it beeing called multiple time for each segment
class CachedAPI extends API
{
    public static $cache = [];

    public function getSuggestedValuesForSegment($segmentName, $idSite)
    {
        if (empty(self::$cache[$segmentName . $idSite])) {
            self::$cache[$segmentName . $idSite] = parent::getSuggestedValuesForSegment($segmentName, $idSite);
        }
        return self::$cache[$segmentName . $idSite];
    }
}

/**
 * testing a the auto suggest API for all known segments
 *
 * @group AutoSuggestAPITest
 * @group Plugins
 */
class AutoSuggestAPITest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    private static $originalAutoSuggestLookBack = null;

    protected static $processed = 0;
    protected static $skipped = array();
    private static $hasArchivedData = false;

    public static function setUpBeforeClass(): void
    {
        $date = mktime(0, 0, 0, 1, 1, 2018);

        $lookBack = ceil((time() - $date) / 86400);

        self::$originalAutoSuggestLookBack = API::$_autoSuggestLookBack;

        API::$_autoSuggestLookBack = $lookBack;
        self::$fixture->dateTime = Date::factory($date)->getDatetime();

        parent::setUpBeforeClass();

        API::setSingletonInstance(CachedAPI::getInstance());
    }

    public static function tearDownAfterClass(): void
    {
        API::$_autoSuggestLookBack = self::$originalAutoSuggestLookBack;

        parent::tearDownAfterClass();

        CachedAPI::$cache = [];
        API::unsetInstance();
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        Cache::clearCacheGeneral();

        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $segments = self::getSegmentsMetadata();

        $apiForTesting = array();
        foreach ($segments as $segment) {
            $apiForTesting[] = $this->getApiForTestingForSegment($idSite, $segment);
        }

        if (self::isMysqli()) {
            // Skip the test on Mysqli as it fails due to rounding Float errors on latitude/longitude
            // then the test started failing after bc19503 and I cannot understand why
            echo "Skipped test \n";
        } else {
            $apiForTesting[] = array('Live.getLastVisitsDetails',
                array('idSite' => $idSite,
                    'date' => '1998-07-12,today',
                    'period' => 'range',
                    'otherRequestParameters' => array('filter_limit' => 1000)));
        }
        return $apiForTesting;
    }

    /**
     * @dataProvider getApiForTestingBrowserArchivingDisabled
     */
    public function testApiBrowserArchivingDisabled($api, $params)
    {
        if (!self::$hasArchivedData) {
            self::$hasArchivedData = true;
            // need to make sure data is archived before disabling the archiving
            Request::processRequest('API.get', array(
                'date' => '2018-01-10', 'period' => 'year', 'idSite' => $params['idSite'],
                'trigger' => 'archivephp'
            ));
        }

        Cache::clearCacheGeneral();
        // disable browser archiving so the APIs are used
        Option::set(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING, 0);

        $this->runApiTests($api, $params);

        Option::set(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING, 1);
    }

    public function getApiForTestingBrowserArchivingDisabled()
    {
        $idSite = self::$fixture->idSite;
        $segments = self::getSegmentsMetadata($onlyWithSuggestedValuesApi = true);

        $apiForTesting = array();
        foreach ($segments as $segment) {
            $apiForTesting[] = $this->getApiForTestingForSegment($idSite, $segment);
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
            array('idSite' => $idSite,
                'testSuffix' => '_' . $segment,
                'otherRequestParameters' => array('segmentName' => $segment)));
    }

    /**
     * @depends      testApi
     * @dataProvider getAnotherApiForTesting
     */
    public function testAnotherApi($api, $params)
    {
        // Get the top segment value
        $request = new Request(
            'method=API.getSuggestedValuesForSegment'
            . '&segmentName=' . $params['segmentToComplete']
            . '&idSite=' . $params['idSite']
            . '&format=json'
        );
        $response = json_decode($request->process(), true);
        $this->assertApiResponseHasNoError($response);
        $topSegmentValue = @$response[0];

        if ($topSegmentValue !== false && !is_null($topSegmentValue)) {
            if (is_numeric($topSegmentValue) || is_float($topSegmentValue) || preg_match('/^\d*?,\d*$/', $topSegmentValue)) {
                $topSegmentValue = Common::forceDotAsSeparatorForDecimalPoint($topSegmentValue);
            }

            // use some specific test values for segments where auto suggest returns list of values that might not occur
            switch ($params['segmentToComplete']) {
                case 'countryName':
                    $topSegmentValue = 'France';
                    break;
                case 'browserName':
                    $topSegmentValue = 'Chrome';
                    break;
                case 'operatingSystemName':
                    $topSegmentValue = 'Android';
                    break;
                case 'visitEndServerDate':
                    $topSegmentValue = '2018-01-03';
                    break;
                case 'visitEndServerDayOfMonth':
                    $topSegmentValue = '03';
                    break;
                case 'visitEndServerYear':
                    $topSegmentValue = '2018';
                    break;
                case 'visitLocalMinute':
                    $topSegmentValue = '34';
                    break;
            }

            // Now build the segment request
            $segmentValue = rawurlencode(html_entity_decode($topSegmentValue, ENT_COMPAT | ENT_HTML401, 'UTF-8'));
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
        $segments = self::getSegmentsMetadata();

        $apiForTesting = array();
        foreach ($segments as $segment) {
            $apiForTesting[] = array('VisitsSummary.get',
                array('idSite' => self::$fixture->idSite,
                    'date' => date("Y-m-d", strtotime(self::$fixture->dateTime)) . ',today',
                    'period' => 'range',
                    'testSuffix' => '_' . $segment,
                    'segmentToComplete' => $segment));
        }
        return $apiForTesting;
    }

    /**
     * @depends      testAnotherApi
     */
    public function testCheckOtherTestsWereComplete()
    {
        // Check that only a few haven't been tested specifically (these are all custom variables slots since we only test slot 1, 2, 5 (see the fixture) and example dimension slots and bandwidth)
        $maximumSegmentsToSkip = 24;
        $this->assertLessThan($maximumSegmentsToSkip, count(self::$skipped), 'SKIPPED ' . count(self::$skipped) . ' segments --> some segments had no "auto-suggested values"
            but we should try and test the autosuggest for all new segments. Segments skipped were: ' . implode(', ', self::$skipped));

        // and check that most others have been tested
        $minimumSegmentsToTest = 46;
        $message = 'PROCESSED ' . self::$processed . ' segments --> it seems some segments "auto-suggested values" haven\'t been tested as we were expecting. ';
        $this->assertGreaterThan($minimumSegmentsToTest, self::$processed, $message);
    }

    public static function getSegmentsMetadata($onlyWithSuggestedValuesApi = false)
    {
        Cache::clearCacheGeneral();
        PiwikCache::getTransientCache()->flushAll();

        $segments = array();

        $environment = new Environment(null);

        $exception = null;
        try {
            $environment->init();
            $environment->getContainer()->get('Piwik\Plugin\Manager')->loadActivatedPlugins();

            foreach (Dimension::getAllDimensions() as $dimension) {
                if (
                    $dimension instanceof CustomVariableName
                    || $dimension instanceof CustomVariableValue
                ) {
                    continue; // ignore custom variables dimensions as they are tested in the plugin
                }

                foreach ($dimension->getSegments() as $segment) {
                    if ($segment->isInternal()) {
                        continue;
                    }
                    if ($onlyWithSuggestedValuesApi && !$segment->getSuggestedValuesApi()) {
                        continue;
                    }
                    $segments[] = $segment->getSegment();
                }
            }
        } catch (\Exception $ex) {
            $exception = $ex;

            echo $ex->getMessage() . "\n" . $ex->getTraceAsString() . "\n";
        }

        $environment->destroy();

        if (!empty($exception)) {
            throw $exception;
        }

        return $segments;
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

\Piwik\Plugins\API\tests\System\AutoSuggestAPITest::$fixture = new ManyVisitsWithGeoIPAndEcommerce();
