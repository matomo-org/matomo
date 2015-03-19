<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Unit\CronArchive;

use Piwik\Config;
use Piwik\Date;
use Piwik\CronArchive\SegmentArchivingRequestUrlProvider;

/**
 * @group Core
 */
class SegmentArchivingRequestUrlProviderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NOW = '2015-03-01';

    private $mockSegmentEntries;

    public function setUp()
    {
        Config::getInstance()->General['enabled_periods_API'] = 'day,week,month,year,range';

        $this->mockSegmentEntries = array(
            array(
                'ts_created' => '2014-01-01',
                'definition' => 'browserName==FF',
                'enable_only_idsite' => 1
            ),

            array(
                'ts_created' => '2014-01-01',
                'definition' => 'countryCode==us',
                'enable_only_idsite' => 1
            ),

            array(
                'ts_created' => '2012-01-01',
                'definition' => 'countryCode==us',
                'enable_only_idsite' => 1
            ),

            array(
                'ts_created' => '2014-01-01',
                'definition' => 'countryCode==ca',
                'enable_only_idsite' => 2
            ),

            array(
                'ts_created' => '2012-01-01',
                'definition' => 'countryCode==ca',
                'enable_only_idsite' => 2
            ),

            array(
                'ts_created' => '2011-01-01',
                'definition' => 'countryCode==ca',
                'enable_only_idsite' => 0
            ),

            array(
                'ts_created' => '2015-03-01',
                'definition' => 'pageUrl==a',
                'enable_only_idsite' => 1
            )
        );
    }

    /**
     * @dataProvider getUrlToArchiveSegmentTestData
     */
    public function test_getUrlToArchiveSegment_CorrectlyModifiesDateInOutputUrl($processNewSegmentsFrom, $idSite, $date, $period, $segment, $expected)
    {
        $urlProvider = $this->createUrlProviderToTest($processNewSegmentsFrom);

        $actual = $urlProvider->getUrlParameterDateString($idSite, $period, $date, $segment);
        $this->assertEquals($expected, $actual);
    }

    public function getUrlToArchiveSegmentTestData()
    {
        $dateRange = '2010-02-01,' . self::TEST_NOW;

        return array(
            array( // test beginning_of_time does not modify date
                'beginning_of_time',
                1,
                $dateRange,
                'week',
                'browserName==FF',
                $dateRange
            ),

            array( // test garbage string does not modify date
                'salkdfjsdfl',
                1,
                $dateRange,
                'week',
                'browserName==FF',
                $dateRange
            ),

            array( // test creation_time uses creation time of segment
                'segment_creation_time',
                1,
                $dateRange,
                'week',
                'browserName==FF',
                "2014-01-01,2015-03-01"
            ),

            array( // test segment_creation_time uses earliest time of segment if multiple match (multiple for site)
                'segment_creation_time',
                1,
                $dateRange,
                'week',
                'countryCode==us',
                '2012-01-01,2015-03-01'
            ),

            array( // test segment_creation_time uses earliest time of segment if multiple match (multiple for site + one for all)
                'segment_creation_time',
                2,
                $dateRange,
                'week',
                'countryCode==ca',
                '2011-01-01,2015-03-01'
            ),

            array( // test 'now' is used if no site matches (testing w/o any segments)
                'segment_creation_time',
                1,
                $dateRange,
                'week',
                'pageTitle==abc',
                "2015-03-01,2015-03-01"
            ),

            array( // test 'now' is used if no site matches (testing w/ segment for another site)
                'segment_creation_time',
                3,
                $dateRange,
                'week',
                'countryCode==us',
                "2015-03-01,2015-03-01"
            ),

            array( // test lastN rewinds created date by N days
                'last10',
                1,
                $dateRange,
                'week',
                'countryCode==us',
                "2011-12-22,2015-03-01"
            ),

            array( // test lastN rewinds now by N days (testing w/ no found segment)
                'last10',
                3,
                $dateRange,
                'week',
                'countryCode==us',
                "2015-02-19,2015-03-01"
            ),

            array( // test when creation_time is greater than date range end date
                'segment_creation_time',
                1,
                '2010-02-01,2015-02-22',
                'week',
                'pageUrl==a',
                '2015-02-22,2015-02-22'
            ),

            array(
                'segment_creation_time',
                1,
                '2015-02-01,' . self::TEST_NOW,
                'week',
                'countryCode==us',
                '2015-02-01,2015-03-01'
            ),
        );
    }

    private function createUrlProviderToTest($processNewSegmentsFrom)
    {
        $mockSegmentEditorModel = $this->getMock('Piwik\Plugins\SegmentEditor\Model', array('getAllSegmentsAndIgnoreVisibility'));
        $mockSegmentEditorModel->expects($this->any())->method('getAllSegmentsAndIgnoreVisibility')->will($this->returnValue($this->mockSegmentEntries));

        return new SegmentArchivingRequestUrlProvider($processNewSegmentsFrom, $mockSegmentEditorModel, null, Date::factory(self::TEST_NOW));
    }
}