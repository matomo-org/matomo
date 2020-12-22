<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\CronArchive;

use Piwik\Config;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\CronArchive\SegmentArchiving;
use Piwik\Option;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class SegmentArchivingTest extends IntegrationTestCase
{
    const TEST_NOW = '2015-03-01';

    private $mockSegmentEntries;

    public function setUp(): void
    {
        parent::setUp();

        Config::getInstance()->General['enabled_periods_API'] = 'day,week,month,year,range';

        Site::setSites([
            1 => [
                'idsite' => 1,
                'ts_created' => '2013-03-03 00:00:00',
            ],
        ]);

        $this->mockSegmentEntries = array(
            array(
                'ts_created' => '2014-01-01',
                'definition' => 'browserName==FF',
                'enable_only_idsite' => 1,
                'ts_last_edit' => '2014-05-05 00:22:33',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2014-01-01',
                'definition' => 'countryCode==us',
                'enable_only_idsite' => 1,
                'ts_last_edit' => '2014-02-02 00:33:44',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2012-01-01',
                'definition' => 'countryCode==us',
                'enable_only_idsite' => 1,
                'ts_last_edit' => '2014-02-03',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2014-01-01',
                'definition' => 'countryCode==ca',
                'enable_only_idsite' => 2,
                'ts_last_edit' => '2013-01-01',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2012-01-01',
                'definition' => 'countryCode==ca',
                'enable_only_idsite' => 2,
                'ts_last_edit' => '2011-01-01',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2012-01-01',
                'definition' => 'countryCode==br',
                'enable_only_idsite' => 2,
                'ts_last_edit' => '2011-01-01',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2011-01-01',
                'definition' => 'countryCode==ca',
                'enable_only_idsite' => 0,
                'ts_last_edit' => null,
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2015-03-01',
                'definition' => 'pageUrl==a',
                'enable_only_idsite' => 1,
                'ts_last_edit' => '2014-01-01',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2015-02-01',
                'definition' => 'pageUrl==b',
                'enable_only_idsite' => 1,
                'ts_last_edit' => null,
                'auto_archive' => 1,
            ),
        );

        Date::$now = strtotime('2020-01-30 00:00:00');
    }

    /**
     * @dataProvider getTestDataForGetReArchiveSegmentStartDate
     */
    public function test_getReArchiveSegmentStartDate()
    {
        // TODO
    }

    private function getTestDataForGetReArchiveSegmentStartDate()
    {
        return [
            // TODO
        ];
    }

    private function createUrlProviderToTest($processNewSegmentsFrom, $mockData = null)
    {
        $mockSegmentEditorModel = $this->createPartialMock('Piwik\Plugins\SegmentEditor\Model', array('getAllSegmentsAndIgnoreVisibility'));
        $mockSegmentEditorModel->expects($this->any())->method('getAllSegmentsAndIgnoreVisibility')->will($this->returnValue($mockData ?: $this->mockSegmentEntries));

        return new SegmentArchiving($processNewSegmentsFrom, $beginningOfTimeLastN = 7, $mockSegmentEditorModel, null, Date::factory(self::TEST_NOW));
    }

    private function getStringDates(array &$entries)
    {
        foreach ($entries as &$entry) {
            $entry['date'] = $entry['date']->getDatetime();
        }
    }
}