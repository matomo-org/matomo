<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Unit\CronArchive;

use Piwik\Date;
use Piwik\CronArchive\SegmentArchivingRequestUrlProvider;

/**
 * @group Core
 */
class SegmentArchivingRequestUrlProviderTest extends \PHPUnit_Framework_TestCase
{
    const BASE_URL = 'http://base/';
    const TOKEN_AUTH = 'tokenauth';
    const TEST_NOW = '2015-03-01';

    private $mockSegmentEntries;

    public function setUp()
    {
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
            )
        );
    }

    /**
     * @dataProvider getUrlToArchiveSegmentTestData
     */
    public function test_getUrlToArchiveSegment_CorrectlyModifiesDateInOutputUrl($processNewSegmentsFrom, $idSite, $date, $period, $segment, $expected)
    {
        $urlProvider = $this->createUrlProviderToTest($processNewSegmentsFrom);

        $actual = $urlProvider->getUrlToArchiveSegment($idSite, $period, $date, $segment);
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
                "http://base/?module=API&method=API.get&idSite=1&period=week&date=$dateRange&format=php&token_auth=tokenauth&segment=" . urlencode('browserName==FF')
            ),

            array( // test garbage string does not modify date
                'salkdfjsdfl',
                1,
                $dateRange,
                'week',
                'browserName==FF',
                "http://base/?module=API&method=API.get&idSite=1&period=week&date=$dateRange&format=php&token_auth=tokenauth&segment=" . urlencode('browserName==FF')
            ),

            array( // test creation_time uses creation time of segment
                'creation_time',
                1,
                $dateRange,
                'week',
                'browserName==FF',
                'http://base/?module=API&method=API.get&idSite=1&period=week&date=2014-01-01,2015-03-01&format=php&token_auth=tokenauth&segment=' . urlencode('browserName==FF')
            ),

            array( // test creation_time uses earliest time of segment if multiple match (multiple for site)
                'creation_time',
                1,
                $dateRange,
                'week',
                'countryCode==us',
                'http://base/?module=API&method=API.get&idSite=1&period=week&date=2012-01-01,2015-03-01&format=php&token_auth=tokenauth&segment=' . urlencode('countryCode==us')
            ),

            array( // test creation_time uses earliest time of segment if multiple match (multiple for site + one for all)
                'creation_time',
                2,
                $dateRange,
                'week',
                'countryCode==ca',
                'http://base/?module=API&method=API.get&idSite=2&period=week&date=2011-01-01,2015-03-01&format=php&token_auth=tokenauth&segment=' . urlencode('countryCode==ca')
            ),

            array( // test 'now' is used if no site matches (testing w/o any segments)
                'creation_time',
                1,
                $dateRange,
                'week',
                'pageTitle==abc',
                'http://base/?module=API&method=API.get&idSite=1&period=week&date=2015-03-01,2015-03-01&format=php&token_auth=tokenauth&segment=' . urlencode('pageTitle==abc')
            ),

            array( // test 'now' is used if no site matches (testing w/ segment for another site)
                'creation_time',
                3,
                $dateRange,
                'week',
                'countryCode==us',
                'http://base/?module=API&method=API.get&idSite=3&period=week&date=2015-03-01,2015-03-01&format=php&token_auth=tokenauth&segment=' . urlencode('countryCode==us')
            ),

            array( // test lastN rewinds created date by N days
                'last10',
                1,
                $dateRange,
                'week',
                'countryCode==us',
                'http://base/?module=API&method=API.get&idSite=1&period=week&date=2011-12-22,2015-03-01&format=php&token_auth=tokenauth&segment=' . urlencode('countryCode==us')
            ),

            array( // test lastN rewinds now by N days (testing w/ no found segment)
                'last10',
                3,
                $dateRange,
                'week',
                'countryCode==us',
                'http://base/?module=API&method=API.get&idSite=3&period=week&date=2015-02-19,2015-03-01&format=php&token_auth=tokenauth&segment=' . urlencode('countryCode==us')
            ),
        );
    }

    private function createUrlProviderToTest($processNewSegmentsFrom)
    {
        $mockSegmentEditorModel = $this->getMock('Piwik\Plugins\SegmentEditor\Model', array('getAllSegmentsAndIgnoreVisibility'));
        $mockSegmentEditorModel->expects($this->any())->method('getAllSegmentsAndIgnoreVisibility')->will($this->returnValue($this->mockSegmentEntries));

        return new SegmentArchivingRequestUrlProvider(self::BASE_URL, self::TOKEN_AUTH, $processNewSegmentsFrom,
            $mockSegmentEditorModel, null, Date::factory(self::TEST_NOW));
    }
}