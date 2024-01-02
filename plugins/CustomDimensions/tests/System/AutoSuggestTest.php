<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\System;

use Piwik\Date;
use Piwik\Plugins\CustomDimensions\Dao\AutoSuggest;
use Piwik\Plugins\CustomDimensions\tests\Fixtures\TrackVisitsWithCustomDimensionsFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group CustomDimensions
 * @group AutoSuggestTest
 * @group Plugins
 */
class AutoSuggestTest extends SystemTestCase
{
    /**
     * @var TrackVisitsWithCustomDimensionsFixture
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToTest[] = array(array('API.getSuggestedValuesForSegment'),
            array(
                'idSite' => 1,
                'date' => self::$fixture->dateTime,
                'periods' => array('year'),
                'otherRequestParameters' => array(
                    'segmentName' => 'dimension1',
                    'idSite' => self::$fixture->idSite,
                ),
                'testSuffix' => '_visitScope'
            )
        );

        $apiToTest[] = array(array('API.getSuggestedValuesForSegment'),
            array(
                'idSite'  => 1,
                'date'    => self::$fixture->dateTime,
                'periods' => array('year'),
                'otherRequestParameters' => array(
                    'segmentName' => 'dimension3',
                    'idSite' => self::$fixture->idSite,
                ),
                'testSuffix' => '_actionScope'
            )
        );

        return $apiToTest;
    }

    public function test_getMostUsedActionDimensionValues_shouldReturnMostUsedValues()
    {
        $autoSuggest = new AutoSuggest();
        $values = $autoSuggest->getMostUsedActionDimensionValues(array('idcustomdimension' => 3), $idSite = 1, $limit = 60);
        $this->assertEquals(array('en', 'value3', 'value5 3'), $values);
    }

    public function test_getMostUsedActionDimensionValues_shouldApplyLimit()
    {
        $autoSuggest = new AutoSuggest();
        $values = $autoSuggest->getMostUsedActionDimensionValues(array('idcustomdimension' => 3), $idSite = 1, $limit = 2);
        $this->assertEquals(array('en','value3'), $values);
    }

    public function test_getMostUsedActionDimensionValues_shouldApplyIdSite()
    {
        $autoSuggest = new AutoSuggest();
        $values = $autoSuggest->getMostUsedActionDimensionValues(array('idcustomdimension' => 1), $idSite = 2, $limit = 2);
        $this->assertEquals(array('site2 value1'), $values);
    }

    public function test_getMostUsedActionDimensionValues_shouldApplyIndex()
    {
        $autoSuggest = new AutoSuggest();
        $values = $autoSuggest->getMostUsedActionDimensionValues(array('idcustomdimension' => 5), $idSite = 1, $limit = 10);
        $this->assertEquals(array('en_US', '343', 'value5', 'value5 5'), $values);
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

AutoSuggestTest::$fixture = new TrackVisitsWithCustomDimensionsFixture();
AutoSuggestTest::$fixture->dateTime = Date::yesterday()->subDay(30)->getDatetime();
