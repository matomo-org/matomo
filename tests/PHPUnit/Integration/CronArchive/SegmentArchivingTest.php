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
     * @dataProvider getTestDataForGetSegmentArchivesToInvalidateForNewSegments
     */
    public function test_getSegmentArchivesToInvalidateForNewSegments_returnsAllSegments_IfInvalidationHasNotRun($processFrom, $idSite, $expected)
    {
        $archiving = $this->createUrlProviderToTest($processFrom);
        $segments = $archiving->getSegmentArchivesToInvalidateForNewSegments($idSite);
        $this->getStringDates($segments);
        $this->assertEquals($expected, $segments);
    }

    public function getTestDataForGetSegmentArchivesToInvalidateForNewSegments()
    {
        return [
            [
                'beginning_of_time',
                1,
                [
                    [
                        'date' => '2013-03-03 00:00:00',
                        'segment' => 'browserName==FF',
                    ],
                    [
                        'date' => '2013-03-03 00:00:00',
                        'segment' => 'countryCode==us',
                    ],
                    [
                        'date' => '2013-03-03 00:00:00',
                        'segment' => 'countryCode==ca',
                    ],
                    [
                        'date' => '2013-03-03 00:00:00',
                        'segment' => 'pageUrl==a',
                    ],
                    [
                        'date' => '2013-03-03 00:00:00',
                        'segment' => 'pageUrl==b',
                    ],
                ],
            ],

            [
                'segment_creation_time',
                1,
                [
                    [
                        'date' => '2014-01-01 00:00:00',
                        'segment' => 'browserName==FF',
                    ],
                    [
                        'date' => '2014-01-01 00:00:00',
                        'segment' => 'countryCode==us',
                    ],
                    [
                        'date' => '2011-01-01 00:00:00',
                        'segment' => 'countryCode==ca',
                    ],
                    [
                        'date' => '2015-03-01 00:00:00',
                        'segment' => 'pageUrl==a',
                    ],
                    [
                        'date' => '2015-02-01 00:00:00',
                        'segment' => 'pageUrl==b',
                    ],
                ],
            ],

            [
                'segment_last_edit_time',
                1,
                [
                    [
                        'date' => '2014-05-05 00:22:33',
                        'segment' => 'browserName==FF',
                    ],
                    [
                        'date' => '2014-02-02 00:33:44',
                        'segment' => 'countryCode==us',
                    ],
                    [
                        'date' => '2011-01-01 00:00:00',
                        'segment' => 'countryCode==ca',
                    ],
                    [
                        'date' => '2015-03-01 00:00:00',
                        'segment' => 'pageUrl==a',
                    ],
                    [
                        'date' => '2015-02-01 00:00:00',
                        'segment' => 'pageUrl==b',
                    ],
                ],
            ],

            [
                'segment_last_edit_time',
                2,
                [
                    [
                        'date' => '2014-01-01 00:00:00',
                        'segment' => 'countryCode==ca',
                    ],
                    [
                        'date' => '2012-01-01 00:00:00',
                        'segment' => 'countryCode==br',
                    ],
                ],
            ],
        ];
    }

    public function test_getSegmentArchivesToInvalidateForNewSegments_returnsSegmentsRecentlyCreated_IfInvalidationHasRun()
    {
        Option::set(CronArchive::CRON_INVALIDATION_TIME_OPTION_NAME, strtotime('2013-12-30 00:00:00'));

        $archiving = $this->createUrlProviderToTest('beginning_of_time');
        $segments = $archiving->getSegmentArchivesToInvalidateForNewSegments(1);
        $this->getStringDates($segments);

        $expected = [
            [
                'segment' => 'browserName==FF',
                'date' => '2013-03-03 00:00:00',
            ],
            [
                'segment' => 'countryCode==us',
                'date' => '2013-03-03 00:00:00',
            ],
            [
                'segment' => 'pageUrl==a',
                'date' => '2013-03-03 00:00:00',
            ],
            [
                'segment' => 'pageUrl==b',
                'date' => '2013-03-03 00:00:00',
            ],
        ];
        $this->assertEquals($expected, $segments);
    }

    public function test_getSegmentArchivesToInvalidateForNewSegments_returnsNoSegments_IfInvalidationHasRunAndAllSegmentsCreatedBefore()
    {
        Option::set(CronArchive::CRON_INVALIDATION_TIME_OPTION_NAME, strtotime('2019-12-30 00:00:00'));

        $archiving = $this->createUrlProviderToTest('beginning_of_time');
        $segments = $archiving->getSegmentArchivesToInvalidateForNewSegments(1);
        $this->getStringDates($segments);

        $expected = [];
        $this->assertEquals($expected, $segments);
    }

    public function test_getSegmentArchivesToInvalidateForNewSegments_usesLastArchiveFinishTimeIfInvalidationTimeMissing()
    {
        Option::set(CronArchive::OPTION_ARCHIVING_FINISHED_TS, strtotime('2013-12-30 00:00:00'));

        $archiving = $this->createUrlProviderToTest('beginning_of_time');
        $segments = $archiving->getSegmentArchivesToInvalidateForNewSegments(1);
        $this->getStringDates($segments);

        $expected = [
            [
                'segment' => 'browserName==FF',
                'date' => '2013-03-03 00:00:00',
            ],
            [
                'segment' => 'countryCode==us',
                'date' => '2013-03-03 00:00:00',
            ],
            [
                'segment' => 'pageUrl==a',
                'date' => '2013-03-03 00:00:00',
            ],
            [
                'segment' => 'pageUrl==b',
                'date' => '2013-03-03 00:00:00',
            ],
        ];
        $this->assertEquals($expected, $segments);
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