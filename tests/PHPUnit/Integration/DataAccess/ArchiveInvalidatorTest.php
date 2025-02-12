<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\CronArchive\ReArchiveList;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Segment;
use Piwik\Log\NullLogger;

/**
 * @group Archiver
 * @group ArchiveInvalidator
 * @group DataAccess
 */
class ArchiveInvalidatorTest extends IntegrationTestCase
{
    public const TEST_SEGMENT_1 = 'browserCode==FF';
    public const TEST_SEGMENT_2 = 'countryCode==uk';

    /**
     * @var ArchiveInvalidator
     */
    private $invalidator;

    /**
     * @var Segment
     */
    private static $segment1;

    /**
     * @var Segment
     */
    private static $segment2;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // these are static because it takes a long time to create new Segment instances (for some reason)
        self::$segment1 = new Segment(self::TEST_SEGMENT_1, array());
        self::$segment2 = new Segment(self::TEST_SEGMENT_2, array());
    }

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        for ($i = 0; $i != 10; ++$i) {
            Fixture::createWebsite('2012-03-04');
        }

        self::addVisitToEachSite();

        Option::deleteLike('%report_to_invalidate_%'); // test w/ a blank slate
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->invalidator = new ArchiveInvalidator(new Model(), new NullLogger());
    }

    public function testMarkArchivesAsInvalidatedSkipsParentArchivesIfTheyAreDisabled()
    {
        $this->insertArchiveRow(1, '2020-03-13', 'day', $doneValue = ArchiveWriter::DONE_OK, false, $varyArchiveTypes = false);
        $this->insertArchiveRow(1, '2020-03-13', 'week', $doneValue = ArchiveWriter::DONE_OK, false, $varyArchiveTypes = false);
        $this->insertArchiveRow(1, '2020-03-13', 'month', $doneValue = ArchiveWriter::DONE_OK, false, $varyArchiveTypes = false);
        $this->insertArchiveRow(1, '2020-03-13', 'year', $doneValue = ArchiveWriter::DONE_OK, false, $varyArchiveTypes = false);

        Config::getInstance()->General['enabled_periods_UI'] = 'day,week,year,range';
        Config::getInstance()->General['enabled_periods_API'] = 'day,week,year,range';

        $this->invalidator->markArchivesAsInvalidated([1], ['2020-03-13'], 'day');

        $expectedInvalidatedArchives = [
            '2020_03' => [
                '1.2020-03-13.2020-03-13.1.done',
                '1.2020-03-09.2020-03-15.2.done',
            ],
        ];

        $invalidatedArchives = $this->getInvalidatedArchives();
        $this->assertEquals($expectedInvalidatedArchives, $invalidatedArchives);

        $expectedInvalidations = [
            [
                'idarchive' => '1',
                'idsite' => '1',
                'period' => '1',
                'name' => 'done',
                'date1' => '2020-03-13',
                'date2' => '2020-03-13',
                'report' => null,
            ],
            [
                'idarchive' => '2',
                'idsite' => '1',
                'period' => '2',
                'name' => 'done',
                'date1' => '2020-03-09',
                'date2' => '2020-03-15',
                'report' => null,
            ],
        ];

        $actualInvalidations = $this->getInvalidatedArchiveTableEntries();
        $this->assertEquals($expectedInvalidations, $actualInvalidations);
    }

    public function testMarkArchivesAsInvalidatedDoesNotInvalidatePartialArchives()
    {
        $this->insertArchiveRow(1, '2020-03-03', 'day', $doneValue = ArchiveWriter::DONE_PARTIAL, 'ExamplePlugin');
        $this->insertArchiveRow(1, '2020-03-03', 'week', $doneValue = ArchiveWriter::DONE_PARTIAL, 'ExamplePlugin');
        $this->insertArchiveRow(1, '2020-03-03', 'month', $doneValue = ArchiveWriter::DONE_PARTIAL, 'ExamplePlugin');
        $this->insertArchiveRow(1, '2020-03-03', 'year', $doneValue = ArchiveWriter::DONE_PARTIAL, 'ExamplePlugin');

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');

        $archiveInvalidator->markArchivesAsInvalidated(
            [1],
            ['2020-03-03'],
            'day',
            null,
            $cascadeDown = true,
            false
        );

        $invalidatedArchives = $this->getInvalidatedArchives();
        $this->assertEmpty($invalidatedArchives);

        $expectedInvalidations = [
            [
                'idarchive' => null,
                'idsite' => '1',
                'period' => '1',
                'name' => 'done',
                'date1' => '2020-03-03',
                'date2' => '2020-03-03',
                'report' => null,
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'period' => '2',
                'name' => 'done',
                'date1' => '2020-03-02',
                'date2' => '2020-03-08',
                'report' => null,
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'period' => '3',
                'name' => 'done',
                'date1' => '2020-03-01',
                'date2' => '2020-03-31',
                'report' => null,
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'period' => '4',
                'name' => 'done',
                'date1' => '2020-01-01',
                'date2' => '2020-12-31',
                'report' => null,
            ],
        ];

        $actualInvalidations = $this->getInvalidatedArchiveTableEntries();
        $this->assertEquals($expectedInvalidations, $actualInvalidations);
    }

    public function testMarkArchivesAsInvalidatedDoesHandleInProgressArchivesCorrectly()
    {
        // Insert an archive/invalidation that is currently in progress
        $this->insertArchiveRow(1, '2020-03-03', 'day', $doneValue = ArchiveWriter::DONE_ERROR, '', false);
        $this->insertInvalidations([
            ['name' => 'done', 'idsite' => 1, 'date1' => '2020-03-03', 'date2' => '2020-03-03', 'period' => 1, 'report' => null, 'ts_started' => Date::now()->getDatetime(), 'status' => 1]
        ]);

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');

        $archiveInvalidator->markArchivesAsInvalidated(
            [1],
            ['2020-03-03'],
            'day',
            null,
            $cascadeDown = true,
            false
        );

        $invalidatedArchives = $this->getAvailableArchives();
        $expectedArchives = [
            '2020_03' => [
                ['idsite' => 1, 'date1' => '2020-03-03', 'date2' => '2020-03-03', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_ERROR_INVALIDATED]
            ],
        ];

        $this->assertEquals($expectedArchives, $invalidatedArchives);

        $expectedInvalidations = [
            [
                'idarchive' => null,
                'idsite' => '1',
                'period' => '1',
                'name' => 'done',
                'date1' => '2020-03-03',
                'date2' => '2020-03-03',
                'report' => null,
                'status' => '1'
            ],
            [
                'idarchive' => '1',
                'idsite' => '1',
                'period' => '1',
                'name' => 'done',
                'date1' => '2020-03-03',
                'date2' => '2020-03-03',
                'report' => null,
                'status' => '0'
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'period' => '2',
                'name' => 'done',
                'date1' => '2020-03-02',
                'date2' => '2020-03-08',
                'report' => null,
                'status' => '0'
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'period' => '3',
                'name' => 'done',
                'date1' => '2020-03-01',
                'date2' => '2020-03-31',
                'report' => null,
                'status' => '0'
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'period' => '4',
                'name' => 'done',
                'date1' => '2020-01-01',
                'date2' => '2020-12-31',
                'report' => null,
                'status' => '0'
            ],
        ];

        $actualInvalidations = $this->getInvalidatedArchiveTableEntries(true);
        $this->assertEquals($expectedInvalidations, $actualInvalidations);
    }

    public function testReArchiveReportDoesNothingIfIniSettingSetToZero()
    {
        Date::$now = strtotime('2020-06-16 12:00:00');

        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = 'last0';

        $this->invalidator->reArchiveReport([1], 'VisitsSummary', 'some.Report');

        $expectedInvalidations = [];
        $actualInvalidations = $this->getInvalidatedArchiveTableEntriesSummary();

        $this->assertEquals($expectedInvalidations, $actualInvalidations);

        // different format
        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = '0';

        $this->invalidator->reArchiveReport([1], 'VisitsSummary', 'some.Report');

        $expectedInvalidations = [];
        $actualInvalidations = $this->getInvalidatedArchiveTableEntriesSummary();

        $this->assertEquals($expectedInvalidations, $actualInvalidations);
    }

    public function testRemoveInvalidationsFromDistributedListRemovesEntriesFromListWhenNoPluginSpecified()
    {
        $this->invalidator->scheduleReArchiving([1,2,3], 'ExamplePlugin');
        $this->invalidator->scheduleReArchiving([1,4,5], 'MyOtherPlugin');

        $list = new ReArchiveList();
        $list->add('badjson');

        $this->invalidator->removeInvalidationsFromDistributedList([2,3]);

        $items = $list->getAll();

        $expected = [
            '{"idSites":[1],"pluginName":"ExamplePlugin","report":null,"startDate":null,"segment":null}',
            '{"idSites":[1,4,5],"pluginName":"MyOtherPlugin","report":null,"startDate":null,"segment":null}',
        ];

        $this->assertEquals($expected, $items);
    }

    public function testRemoveInvalidationsFromDistributedListRemovesEntriesFromListWhenPluginNameIsSpecified()
    {
        $this->invalidator->scheduleReArchiving([1,2,3], 'ExamplePlugin');
        $this->invalidator->scheduleReArchiving([1,4,5], 'MyOtherPlugin');

        $this->invalidator->removeInvalidationsFromDistributedList([1,2,3], 'ExamplePlugin');

        $list = new ReArchiveList();
        $items = $list->getAll();

        $expected = [
            '{"idSites":[1,4,5],"pluginName":"MyOtherPlugin","report":null,"startDate":null,"segment":null}',
        ];

        $this->assertEquals($expected, $items);
    }

    public function testRemoveInvalidationsFromDistributedListRemovesEntriesFromListWhenIntSiteIdSpecified()
    {
        $this->invalidator->scheduleReArchiving([1,2,3], 'ExamplePlugin');
        $this->invalidator->scheduleReArchiving([1,4,5], 'MyOtherPlugin');

        $this->invalidator->removeInvalidationsFromDistributedList(3, 'ExamplePlugin');

        $list = new ReArchiveList();
        $items = $list->getAll();

        $expected = [
            '{"idSites":[1,2],"pluginName":"ExamplePlugin","report":null,"startDate":null,"segment":null}',
            '{"idSites":[1,4,5],"pluginName":"MyOtherPlugin","report":null,"startDate":null,"segment":null}',
        ];

        $this->assertEquals($expected, $items);
    }

    /**
     * @dataProvider getRemoveInvalidationsFromDistributedListDifferentIdSiteValues
     * @param $idSites
     * @return void
     */
    public function testRemoveInvalidationsFromDistributedListDifferentIdSiteValues($idSites, $expectedArray)
    {
        $this->invalidator->scheduleReArchiving([1, 2, 3], 'ExamplePlugin');
        $this->invalidator->scheduleReArchiving([1, 4, 5], 'ExamplePlugin');
        $this->invalidator->scheduleReArchiving('all', 'ExamplePlugin');

        $this->invalidator->removeInvalidationsFromDistributedList($idSites, 'ExamplePlugin');

        $list = new ReArchiveList();
        $items = $list->getAll();

        $this->assertEquals($expectedArray, $items);
    }

    public function getRemoveInvalidationsFromDistributedListDifferentIdSiteValues(): array
    {
        $idSites = [1,2,3,4,5,6,7,8,9,10];
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            for ($i = 0; $i < count($idSites); $i++) {
                $idSites[$i] = '"' . $idSites[$i] . '"';
            }
        }
        $allEntry = '{"idSites":[' . implode(',', $idSites) . '],"pluginName":"ExamplePlugin","report":null,"startDate":null,"segment":null}';
        $expected = [
            '{"idSites":[1,2,3],"pluginName":"ExamplePlugin","report":null,"startDate":null,"segment":null}',
            '{"idSites":[1,4,5],"pluginName":"ExamplePlugin","report":null,"startDate":null,"segment":null}',
            $allEntry,
        ];

        return [
            [null, $expected],
            [0, $expected],
            ['0', $expected],
            ['unexpectedString', $expected],
            [2.2, $expected],
            [123.45, $expected],
            [false, $expected],
            [true, $expected],
            ['false', $expected],
            ['true', $expected],
            ['all', []],
        ];
    }

    public function testRemoveInvalidationsFromDistributedListRemovesEntriesFromListWhenPluginNameAndReportIsSpecified()
    {
        $this->invalidator->scheduleReArchiving([1,4,5], 'ExamplePlugin');
        $this->invalidator->scheduleReArchiving([1,4,5], 'ExamplePlugin', 'myReport');
        $this->invalidator->scheduleReArchiving([1,4,5], 'ExamplePlugin', 'myOtherReport');

        $this->invalidator->removeInvalidationsFromDistributedList([1,4,5], 'ExamplePlugin', 'myReport');

        $list = new ReArchiveList();
        $items = $list->getAll();

        $expected = [
            '{"idSites":[1,4,5],"pluginName":"ExamplePlugin","report":null,"startDate":null,"segment":null}',
            '{"idSites":[1,4,5],"pluginName":"ExamplePlugin","report":"myOtherReport","startDate":null,"segment":null}',
        ];

        $this->assertEquals($expected, $items);
    }

    public function testRemoveInvalidationsRemovesAllIfAllSitesSpecified()
    {
        $this->insertInvalidations([
            ['name' => 'done.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => 'myReport'],
            ['name' => 'done.MyPlugin', 'idsite' => 2, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => null],
            ['name' => 'done.MyOtherPlugin', 'idsite' => 3, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'report' => ''],
            ['name' => 'done', 'idsite' => 4, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'report' => ''],
            ['name' => 'done.MyPlugin', 'idsite' => 5, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => 'myOtherReport'],
        ]);

        $this->invalidator->removeInvalidations($idSite = 'all', 'MyPlugin');

        $invalidations = $this->getInvalidatedArchiveTableEntries();
        $expectedInvalidations = [
            ['idarchive' => null, 'idsite' => 3, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'name' => 'done.MyOtherPlugin', 'report' => null],
            ['idarchive' => null, 'idsite' => 4, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'name' => 'done', 'report' => null],
        ];

        $this->assertEquals($expectedInvalidations, $invalidations);
    }

    public function testRemoveInvalidationsRemovesAllForMultipleSites()
    {
        $this->insertInvalidations([
            ['name' => 'done.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => 'myReport'],
            ['name' => 'done.MyPlugin', 'idsite' => 2, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => null],
            ['name' => 'done.MyOtherPlugin', 'idsite' => 3, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'report' => ''],
            ['name' => 'done', 'idsite' => 4, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'report' => ''],
            ['name' => 'done.MyPlugin', 'idsite' => 5, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => 'myOtherReport'],
        ]);

        $this->invalidator->removeInvalidations([1,2,3], 'MyPlugin');

        $invalidations = $this->getInvalidatedArchiveTableEntries();
        $expectedInvalidations = [
            ['idarchive' => null, 'idsite' => 3, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'name' => 'done.MyOtherPlugin', 'report' => null],
            ['idarchive' => null, 'idsite' => 4, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'name' => 'done', 'report' => null],
            ['idarchive' => null, 'idsite' => 5, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'name' => 'done.MyPlugin', 'report' => 'myOtherReport'],
        ];

        $this->assertEquals($expectedInvalidations, $invalidations);
    }

    public function testRemoveInvalidationsRemovesAllForPlugin()
    {
        $this->insertInvalidations([
            ['name' => 'done.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => 'myReport'],
            ['name' => 'done.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => null],
            ['name' => 'doneSEGMENTHASH.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => null],
            ['name' => 'done.MyOtherPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'report' => ''],
            ['name' => 'doneSEGMENTHASH.MyOtherPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'report' => ''],
            ['name' => 'done', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'report' => ''],
            ['name' => 'done.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => 'myOtherReport'],
        ]);

        $this->invalidator->removeInvalidations($idSite = 1, 'MyPlugin');

        $invalidations = $this->getInvalidatedArchiveTableEntries();
        $expectedInvalidations = [
            ['idarchive' => null, 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'name' => 'done.MyOtherPlugin', 'report' => null],
            ['idarchive' => null, 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'name' => 'doneSEGMENTHASH.MyOtherPlugin', 'report' => null],
            ['idarchive' => null, 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'name' => 'done', 'report' => null],
        ];

        $this->assertEquals($expectedInvalidations, $invalidations);
    }

    public function testRemoveInvalidationsRemovesAllForSingleReport()
    {
        $this->insertInvalidations([
            ['name' => 'done.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => 'myReport'],
            ['name' => 'done.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => null],
            ['name' => 'doneSEGMENTHASH.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => null],
            ['name' => 'doneSEGMENTHASH.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => 'myReport'],
            ['name' => 'done.MyOtherPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'report' => ''],
            ['name' => 'done', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-05-05', 'period' => 1, 'report' => ''],
            ['name' => 'done.MyPlugin', 'idsite' => 1, 'date1' => '2012-03-04', 'date2' => '2015-03-04', 'period' => 1, 'report' => 'myOtherReport'],
        ]);

        $this->invalidator->removeInvalidations($idSite = 1, 'MyPlugin', 'myReport');

        $invalidations = $this->getInvalidatedArchiveTableEntries();
        $expectedInvalidations = [
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2012-03-04',
                'date2' => '2015-03-04',
                'period' => '1',
                'name' => 'done.MyPlugin',
                'report' => null,
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2012-03-04',
                'date2' => '2015-03-04',
                'period' => '1',
                'name' => 'doneSEGMENTHASH.MyPlugin',
                'report' => null,
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2012-03-04',
                'date2' => '2015-05-05',
                'period' => '1',
                'name' => 'done.MyOtherPlugin',
                'report' => '',
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2012-03-04',
                'date2' => '2015-05-05',
                'period' => '1',
                'name' => 'done',
                'report' => '',
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2012-03-04',
                'date2' => '2015-03-04',
                'period' => '1',
                'name' => 'done.MyPlugin',
                'report' => 'myOtherReport',
            ],
        ];

        $this->assertEquals($expectedInvalidations, $invalidations);
    }

    public function testRememberToInvalidateArchivedReportsLaterShouldCreateAnEntryInCaseThereIsNoneYet()
    {
        //Updated for change to allow for multiple transactions to invalidate the same report without deadlock.
        $key = 'report_to_invalidate_2_2014-04-05' . '_' . getmypid();
        $this->assertEmpty(Option::getLike('%' . $key . '%'));

        $keyStored = $this->rememberReport(2, '2014-04-05');

        $this->assertStringEndsWith($key, $keyStored);
        $this->assertSame('1', Option::get($keyStored));
    }

    public function testRememberToInvalidateArchivedReportsLaterShouldNotCreateEntryTwice()
    {
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05');

        $this->assertCount(1, Option::getLike('%report_to_invalidate%'));
    }

    public function testGetRememberedArchivedReportsThatShouldBeInvalidatedShouldNotReturnEntriesInCaseNoneAreRemembered()
    {
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->assertSame(array(), $reports);
    }

    public function testGetRememberedArchivedReportsThatShouldBeInvalidatedShouldGroupEntriesByDate()
    {
        $this->rememberReportsForManySitesAndDates();

        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->assertSameReports($this->getRememberedReportsByDate(), $reports);
    }

    public function testGetRememberedArchivedReportsThatShouldBeInvalidatedBySite(): void
    {
        $this->rememberReportsForManySitesAndDates();

        $idSite = 2;
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated($idSite);
        $allReports = $this->getRememberedReportsByDate();

        foreach ($allReports as $day => $idSites) {
            if (!in_array($idSite, $idSites)) {
                self::assertArrayNotHasKey($day, $reports);
            } else {
                self::assertSame([$idSite], $reports[$day]);
            }
        }
    }

    public function testGetDaysWithRememberedInvalidationsForSite(): void
    {
        $this->rememberReportsForManySitesAndDates();

        $idSite = 2;
        $days = $this->invalidator->getDaysWithRememberedInvalidationsForSite($idSite);
        $allReports = $this->getRememberedReportsByDate();

        foreach ($allReports as $day => $idSites) {
            if (!in_array($idSite, $idSites)) {
                self::assertNotContains($day, $days);
            } else {
                self::assertContains($day, $days);
            }
        }
    }

    private function assertSameReports($expected, $actual)
    {
        $keys1 = array_keys($expected);
        $keys2 = array_keys($actual);
        sort($keys1);
        sort($keys2);

        $this->assertSame($keys1, $keys2);
        foreach ($expected as $index => $values) {
            sort($values);
            sort($actual[$index]);
            $this->assertSame($values, $actual[$index]);
        }
    }

    public function testForgetRememberedArchivedReportsToInvalidateForSiteShouldNotDeleteAnythingInCaseNoReportForThatSite()
    {
        $this->rememberReportsForManySitesAndDates();

        $this->invalidator->forgetRememberedArchivedReportsToInvalidateForSite(10);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->assertSameReports($this->getRememberedReportsByDate(), $reports);
    }

    public function testForgetRememberedArchivedReportsToInvalidateForSiteShouldOnlyDeleteReportsBelongingToThatSite()
    {
        $this->rememberReportsForManySitesAndDates();

        $this->invalidator->forgetRememberedArchivedReportsToInvalidateForSite(7);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $expected = array(
            '2014-04-05' => array(1, 2, 4),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3)
        );
        $this->assertSameReports($expected, $reports);
    }

    public function testForgetRememberedArchivedReportsToInvalidateShouldNotForgetAnythingIfThereIsNoMatch()
    {
        $this->rememberReportsForManySitesAndDates();

        // site does not match
        $hasDeleted = $this->invalidator->forgetRememberedArchivedReportsToInvalidate(10, Date::factory('2014-04-05'));
        $this->assertFalse($hasDeleted);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSameReports($this->getRememberedReportsByDate(), $reports);

        // date does not match
        $hasDeleted = $this->invalidator->forgetRememberedArchivedReportsToInvalidate(7, Date::factory('2012-04-05'));
        $this->assertFalse($hasDeleted);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSameReports($this->getRememberedReportsByDate(), $reports);
    }

    public function testForgetRememberedArchivedReportsToInvalidateShouldOnlyDeleteReportBelongingToThatSiteAndDate()
    {
        $this->rememberReportsForManySitesAndDates();

        $hasDeleted = $this->invalidator->forgetRememberedArchivedReportsToInvalidate(2, Date::factory('2014-04-05'));
        $this->assertTrue($hasDeleted);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $expected = array(
            '2014-04-05' => array(1, 4, 7),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3),
            '2014-04-08' => array(7),
            '2014-05-08' => array(7),
        );
        $this->assertSameReports($expected, $reports);

        unset($expected['2014-05-08']);

        $hasDeleted = $this->invalidator->forgetRememberedArchivedReportsToInvalidate(7, Date::factory('2014-05-08'));
        $this->assertTrue($hasDeleted);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSameReports($expected, $reports);
    }

    public function testMarkArchivesAsInvalidatedShouldForgetInvalidatedSitesAndDatesIfPeriodIsDay()
    {
        $this->rememberReportsForManySitesAndDates();

        $idSites = array(2, 10, 7, 5);
        $dates = array(
            Date::factory('2014-04-05'),
            Date::factory('2014-04-08'),
            Date::factory('2010-10-10'),
        );

        $this->invalidator->markArchivesAsInvalidated($idSites, $dates, 'day');
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $expected = array(
            '2014-04-05' => array(1, 4),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3),
            '2014-05-08' => array(7),
        );
        $this->assertSameReports($expected, $reports);
    }

    private function rememberReport($idSite, $date)
    {
        $date = Date::factory($date);
        return $this->invalidator->rememberToInvalidateArchivedReportsLater($idSite, $date);
    }

    private function getRememberedReportsByDate()
    {
        return array(
            '2014-04-06' => array(3),
            '2014-04-05' => array(4, 7, 2, 1),
            '2014-05-05' => array(5, 2),
            '2014-04-08' => array(7),
            '2014-05-08' => array(7),
        );
    }

    private function rememberReportsForManySitesAndDates()
    {
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05'); // should appear only once for this site and date
        $this->rememberReport(3, '2014-04-06');
        $this->rememberReport(1, '2014-04-05');
        $this->rememberReport(2, '2014-05-05');
        $this->rememberReport(5, '2014-05-05');
        $this->rememberReport(4, '2014-04-05');
        $this->rememberReport(7, '2014-04-05');
        $this->rememberReport(7, '2014-05-08');
        $this->rememberReport(7, '2014-04-08');
    }

    public function testMarkArchivesAsInvalidatedInvalidatesPastPurgeThresholdIfFlagToIgnoreIsProvided()
    {
        PrivacyManager::savePurgeDataSettings(array(
            'delete_logs_enable' => 1,
            'delete_logs_older_than' => 180,
        ));

        $dateBeforeThreshold = Date::factory('today')->subDay(190);
        $thresholdDate = Date::factory('today')->subDay(180);

        $this->insertArchiveRow(1, $dateBeforeThreshold, 'day');

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');
        $result = $archiveInvalidator->markArchivesAsInvalidated(
            array(1),
            array($dateBeforeThreshold),
            'day',
            null,
            false,
            false,
            null,
            true
        );

        $this->assertEquals($thresholdDate->toString(), $result->minimumDateWithLogs);

        $expectedProcessedDates = array($dateBeforeThreshold->toString());
        $this->assertEquals($expectedProcessedDates, $result->processedDates);

        $this->assertEmpty($result->warningDates);

        $invalidatedArchives = $this->getInvalidatedIdArchives();

        $countInvalidatedArchives = 0;
        foreach ($invalidatedArchives as $idarchives) {
            $countInvalidatedArchives += count($idarchives);
        }

        // the day, day w/ a segment, week, month & year are invalidated
        $this->assertEquals(1, $countInvalidatedArchives);

        $invalidatedArchiveTableEntries = $this->getInvalidatedArchiveTableEntries();
        $this->assertCount(4, $invalidatedArchiveTableEntries);
    }

    public function testMarkArchivesAsInvalidatedDoesNotInvalidateDatesBeforePurgeThreshold()
    {
        PrivacyManager::savePurgeDataSettings(array(
            'delete_logs_enable' => 1,
            'delete_logs_older_than' => 180,
        ));

        $dateBeforeThreshold = Date::factory('today')->subDay(190);
        $thresholdDate = Date::factory('today')->subDay(180);
        $dateAfterThreshold = Date::factory('today')->subDay(170);

        // can't test more than day since today will change, causing the test to fail w/ other periods randomly
        $this->insertArchiveRow(1, $dateBeforeThreshold, 'day');
        $this->insertArchiveRow(1, $dateAfterThreshold, 'day');

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');
        $result = $archiveInvalidator->markArchivesAsInvalidated(array(1), array($dateBeforeThreshold, $dateAfterThreshold), 'day');

        $this->assertEquals($thresholdDate->toString(), $result->minimumDateWithLogs);

        $expectedProcessedDates = array($dateAfterThreshold->toString());
        $this->assertEquals($expectedProcessedDates, $result->processedDates);

        $expectedWarningDates = array($dateBeforeThreshold->toString());
        $this->assertEquals($expectedWarningDates, $result->warningDates);

        $invalidatedArchives = $this->getInvalidatedIdArchives();

        $countInvalidatedArchives = 0;
        foreach ($invalidatedArchives as $idarchives) {
            $countInvalidatedArchives += count($idarchives);
        }

        // the day, day w/ a segment, week, month & year are invalidated
        $this->assertEquals(1, $countInvalidatedArchives);

        $invalidatedArchiveTableEntries = $this->getInvalidatedArchiveTableEntries();
        $this->assertCount(4, $invalidatedArchiveTableEntries);
    }

    public function testMarkArchivesAsInvalidatedInvalidatesCorrectlyWhenNoArchiveTablesExist()
    {
        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');
        $result = $archiveInvalidator->markArchivesAsInvalidated([1], [Date::factory('2016-03-04')], false, null, false);

        $this->assertEquals([
            '2016-03-04',
        ], $result->processedDates);

        $expectedIdArchives = [];

        $idArchives = $this->getInvalidatedArchives();

        // Remove empty values (some new empty entries may be added each month)
        $idArchives = array_filter($idArchives);
        $expectedIdArchives = array_filter($expectedIdArchives);

        $this->assertEqualsSorted($expectedIdArchives, $idArchives);

        $expectedEntries = [
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2016-01-01',
                'date2' => '2016-12-31',
                'period' => '4',
                'name' => 'done',
                'report' => null,
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2016-02-29',
                'date2' => '2016-03-06',
                'period' => '2',
                'name' => 'done',
                'report' => null,
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2016-03-01',
                'date2' => '2016-03-31',
                'period' => '3',
                'name' => 'done',
                'report' => null,
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2016-03-04',
                'date2' => '2016-03-04',
                'period' => '1',
                'name' => 'done',
                'report' => null,
            ],
        ];

        $invalidatedArchiveTableEntries = $this->getInvalidatedArchiveTableEntries();
        $this->assertEqualsSorted($expectedEntries, $invalidatedArchiveTableEntries);
    }

    public function testMarkArchivesAsInvalidatedAddsInvalidationEntriesButDoesNotMarkArchivesAsInvalidatedIfArchiveIsPartial()
    {
        // insert some partial archives
        $this->insertArchiveRow(1, '2020-03-04', 'day', ArchiveWriter::DONE_OK, false, false);
        $this->insertArchiveRow(1, '2020-03-04', 'day', ArchiveWriter::DONE_OK, 'ExamplePlugin', false);
        $this->insertArchiveRow(1, '2020-03-04', 'day', ArchiveWriter::DONE_PARTIAL, 'ExamplePlugin', false);
        $this->insertArchiveRow(1, '2020-03-04', 'day', ArchiveWriter::DONE_PARTIAL, 'ExamplePlugin', false);
        $this->insertArchiveRow(1, '2020-03-04', 'day', ArchiveWriter::DONE_PARTIAL, 'ExamplePlugin', false);

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');

        $result = $archiveInvalidator->markArchivesAsInvalidated([1], ['2020-03-04'], 'day', null, false, false, 'ExamplePlugin.someData');

        $this->assertEquals([Date::factory('2020-03-04')], $result->processedDates);

        $idArchives = $this->getInvalidatedArchives();
        $this->assertEquals([], $idArchives);

        $expectedInvalidatedArchives = [
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-01-01',
                'date2' => '2020-12-31',
                'period' => '4',
                'name' => 'done.ExamplePlugin',
                'report' => 'someData',
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-01',
                'date2' => '2020-03-31',
                'period' => '3',
                'name' => 'done.ExamplePlugin',
                'report' => 'someData',
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-02',
                'date2' => '2020-03-08',
                'period' => '2',
                'name' => 'done.ExamplePlugin',
                'report' => 'someData',
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-04',
                'date2' => '2020-03-04',
                'period' => '1',
                'name' => 'done.ExamplePlugin',
                'report' => 'someData',
            ],
        ];

        $invalidations = $this->getInvalidatedArchiveTableEntries();
        $this->assertEqualsSorted($expectedInvalidatedArchives, $invalidations);
    }

    /**
     * @dataProvider getTestDataForMarkArchivesAsInvalidated
     */
    public function testMarkArchivesAsInvalidatedMarksCorrectArchivesAsInvalidated(
        $idSites,
        $dates,
        $period,
        $segment,
        $cascadeDown,
        $expectedIdArchives,
        $expectedInvalidatedArchives,
        $name = null,
        $addStoredSegments = false
    ) {
        $this->insertArchiveRowsForTest();

        Rules::setBrowserTriggerArchiving(false);
        if ($addStoredSegments) {
            API::getInstance()->add('test segment 1', self::TEST_SEGMENT_1, false, true);
            API::getInstance()->add('test segment 2', self::TEST_SEGMENT_2, false, true);
        }

        if (null !== $segment) {
            $segment = new Segment($segment, $idSites);
        }

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');

        $result = $archiveInvalidator->markArchivesAsInvalidated($idSites, $dates, $period, $segment, $cascadeDown, false, $name);

        $this->assertEquals($dates, $result->processedDates);

        $idArchives = $this->getInvalidatedArchives();

        // Remove empty values (some new empty entries may be added each month)
        $idArchives = array_filter($idArchives);

        $this->assertEquals($expectedIdArchives, $idArchives);

        $invalidatedIdArchives = $this->getInvalidatedArchiveTableEntries();

        $this->assertEqualsSorted($expectedInvalidatedArchives, $invalidatedIdArchives);

        $uniqueArchives = array_map('json_encode', $invalidatedIdArchives);
        $uniqueArchives = array_unique($uniqueArchives);
        $this->assertTrue(count($uniqueArchives) == count($invalidatedIdArchives), "duplicates inserted");
    }

    public function getTestDataForMarkArchivesAsInvalidated(): iterable
    {
        yield 'day period, multiple sites, multiple dates across tables, cascade = true' => [
            [1, 2],
            ['2015-01-01', '2015-02-05', '2015-04-30'],
            'day',
            null,
            true,
            [
                '2015_04' => [
                    '1.2015-04-30.2015-04-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '2.2015-04-30.2015-04-30.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-04-27.2015-05-03.2.done',
                    '2.2015-04-27.2015-05-03.2.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-04-01.2015-04-30.3.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '2.2015-04-01.2015-04-30.3.done5447835b0a861475918e79e932abdfd8',
                ],
                '2015_01' => [
                    '1.2015-01-01.2015-01-01.1.done3736b708e4d20cfc10610e816a1b2341',
                    '2.2015-01-01.2015-01-01.1.done.VisitsSummary',
                    '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                    '2.2015-01-01.2015-01-31.3.done.VisitsSummary',
                    '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                    '2.2015-01-01.2015-12-31.4.done',
                    '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                ],
                '2015_02' => [
                    '1.2015-02-05.2015-02-05.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '2.2015-02-05.2015-02-05.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-02-02.2015-02-08.2.done',
                    '2.2015-02-02.2015-02-08.2.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-02-01.2015-02-28.3.done.VisitsSummary',
                    '2.2015-02-01.2015-02-28.3.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                ],
                '2014_12' => [
                    '1.2014-12-29.2015-01-04.2.done3736b708e4d20cfc10610e816a1b2341',
                    '2.2014-12-29.2015-01-04.2.done.VisitsSummary',
                ],
            ],
            [
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => '85', 'idsite' => '1', 'date1' => '2015-02-02', 'date2' => '2015-02-08', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-02-05', 'date2' => '2015-02-05', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-04-01', 'date2' => '2015-04-30', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => '100', 'idsite' => '1', 'date1' => '2015-04-27', 'date2' => '2015-05-03', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-04-30', 'date2' => '2015-04-30', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => '110', 'idsite' => '2', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-02-02', 'date2' => '2015-02-08', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-02-05', 'date2' => '2015-02-05', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-04-01', 'date2' => '2015-04-30', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-04-27', 'date2' => '2015-05-03', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-04-30', 'date2' => '2015-04-30', 'period' => '1', 'name' => 'done', 'report' => null],
            ],
        ];

        yield 'month period, one site, one date, cascade = false' => [
            [1],
            ['2015-01-01'],
            'month',
            null,
            false,
            [
                '2015_01' => [
                    '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                ],
            ],
            [
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done', 'report' => null],
            ],
        ];

        yield 'month period, one site, one date, cascade = true' => [
            [1],
            ['2015-01-15'],
            'month',
            null,
            true,
            [
                '2014_12' => [
                    '1.2014-12-29.2015-01-04.2.done3736b708e4d20cfc10610e816a1b2341',
                ],
                '2015_01' => [
                    '1.2015-01-01.2015-01-01.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-02.2015-01-02.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-03.2015-01-03.1.done.VisitsSummary',
                    '1.2015-01-04.2015-01-04.1.done',
                    '1.2015-01-05.2015-01-05.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-06.2015-01-06.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-07.2015-01-07.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-08.2015-01-08.1.done.VisitsSummary',
                    '1.2015-01-09.2015-01-09.1.done',
                    '1.2015-01-10.2015-01-10.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-11.2015-01-11.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-12.2015-01-12.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-13.2015-01-13.1.done.VisitsSummary',
                    '1.2015-01-14.2015-01-14.1.done',
                    '1.2015-01-15.2015-01-15.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-16.2015-01-16.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-17.2015-01-17.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-18.2015-01-18.1.done.VisitsSummary',
                    '1.2015-01-19.2015-01-19.1.done',
                    '1.2015-01-20.2015-01-20.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-21.2015-01-21.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-22.2015-01-22.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-23.2015-01-23.1.done.VisitsSummary',
                    '1.2015-01-24.2015-01-24.1.done',
                    '1.2015-01-25.2015-01-25.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-26.2015-01-26.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-27.2015-01-27.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-28.2015-01-28.1.done.VisitsSummary',
                    '1.2015-01-29.2015-01-29.1.done',
                    '1.2015-01-30.2015-01-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-31.2015-01-31.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-05.2015-01-11.2.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-12.2015-01-18.2.done.VisitsSummary',
                    '1.2015-01-19.2015-01-25.2.done',
                    '1.2015-01-26.2015-02-01.2.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                ],
            ],
            [
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-02', 'date2' => '2015-01-02', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-03', 'date2' => '2015-01-03', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => '10', 'idsite' => '1', 'date1' => '2015-01-04', 'date2' => '2015-01-04', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-05', 'date2' => '2015-01-05', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-05', 'date2' => '2015-01-11', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-06', 'date2' => '2015-01-06', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-07', 'date2' => '2015-01-07', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-08', 'date2' => '2015-01-08', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => '25', 'idsite' => '1', 'date1' => '2015-01-09', 'date2' => '2015-01-09', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-10', 'date2' => '2015-01-10', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-11', 'date2' => '2015-01-11', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-12', 'date2' => '2015-01-12', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-12', 'date2' => '2015-01-18', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-13', 'date2' => '2015-01-13', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => '40', 'idsite' => '1', 'date1' => '2015-01-14', 'date2' => '2015-01-14', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-15', 'date2' => '2015-01-15', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-16', 'date2' => '2015-01-16', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-17', 'date2' => '2015-01-17', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-18', 'date2' => '2015-01-18', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => '55', 'idsite' => '1', 'date1' => '2015-01-19', 'date2' => '2015-01-19', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => '100', 'idsite' => '1', 'date1' => '2015-01-19', 'date2' => '2015-01-25', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-20', 'date2' => '2015-01-20', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-21', 'date2' => '2015-01-21', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-22', 'date2' => '2015-01-22', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-23', 'date2' => '2015-01-23', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => '70', 'idsite' => '1', 'date1' => '2015-01-24', 'date2' => '2015-01-24', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-25', 'date2' => '2015-01-25', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-01-26', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-02-01', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-27', 'date2' => '2015-01-27', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-28', 'date2' => '2015-01-28', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => '85', 'idsite' => '1', 'date1' => '2015-01-29', 'date2' => '2015-01-29', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-30', 'date2' => '2015-01-30', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-31', 'date2' => '2015-01-31', 'period' => '1', 'name' => 'done', 'report' => null],
            ],
        ];

        yield 'week period, one site, multiple dates w/ redundant dates & periods, cascade = true' => [
            [1],
            ['2015-01-02', '2015-01-03', '2015-01-31'],
            'week',
            null,
            true,
            [
                '2014_12' => [
                    '1.2014-12-29.2014-12-29.1.done',
                    '1.2014-12-30.2014-12-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2014-12-31.2014-12-31.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2014-12-29.2015-01-04.2.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2014-12-01.2014-12-31.3.done5447835b0a861475918e79e932abdfd8',
                    '1.2014-12-05.2015-01-01.5.done.VisitsSummary',
                ],
                '2015_01' => [
                    '1.2015-01-01.2015-01-01.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-02.2015-01-02.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-03.2015-01-03.1.done.VisitsSummary',
                    '1.2015-01-04.2015-01-04.1.done',
                    '1.2015-01-26.2015-01-26.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-27.2015-01-27.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-28.2015-01-28.1.done.VisitsSummary',
                    '1.2015-01-29.2015-01-29.1.done',
                    '1.2015-01-30.2015-01-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-31.2015-01-31.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-26.2015-02-01.2.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                ],
                '2015_02' => [
                    '1.2015-02-01.2015-02-01.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-02-01.2015-02-28.3.done.VisitsSummary',
                ],
                '2014_01' => [
                    '1.2014-01-01.2014-12-31.4.done3736b708e4d20cfc10610e816a1b2341',
                ],
            ],
            [
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2014-01-01', 'date2' => '2014-12-31', 'period' => '4', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2014-12-01', 'date2' => '2014-12-31', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => '85', 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2014-12-29', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2014-12-30', 'date2' => '2014-12-30', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2014-12-31', 'date2' => '2014-12-31', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-02', 'date2' => '2015-01-02', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-03', 'date2' => '2015-01-03', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => '10', 'idsite' => '1', 'date1' => '2015-01-04', 'date2' => '2015-01-04', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-01-26', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-02-01', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-27', 'date2' => '2015-01-27', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-28', 'date2' => '2015-01-28', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => '85', 'idsite' => '1', 'date1' => '2015-01-29', 'date2' => '2015-01-29', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-30', 'date2' => '2015-01-30', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-31', 'date2' => '2015-01-31', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-02-01', 'date2' => '2015-02-01', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => '3', 'name' => 'done', 'report' => null],
            ],
        ];

        yield 'range period, exact match, cascade = true' => [
            [1],
            ['2015-01-01', '2015-01-10'],
            'range',
            null,
            true,
            [
                '2015_01' => [
                    '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                ],
            ],
            [
                // empty
            ],
        ];

        yield 'range period, overlapping a range in the DB' => [
            [1],
            ['2015-01-02', '2015-03-05'],
            'range',
            null,
            true,
            [
                '2015_01' => [
                    '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                ],
                '2015_03' => [
                    '1.2015-03-04.2015-03-05.5.done.VisitsSummary',
                    '1.2015-03-05.2015-03-10.5.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                ],
            ],
            [
                // empty
            ],
        ];

        yield 'week period, one site, cascade = true, segment' => [
            [1],
            ['2015-01-05'],
            'month',
            self::TEST_SEGMENT_1,
            true,
            [
                '2014_12' => [
                    '1.2014-12-29.2015-01-04.2.done3736b708e4d20cfc10610e816a1b2341',
                ],
                '2015_01' => [
                    '1.2015-01-01.2015-01-01.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-05.2015-01-05.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-06.2015-01-06.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-10.2015-01-10.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-11.2015-01-11.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-15.2015-01-15.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-16.2015-01-16.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-20.2015-01-20.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-21.2015-01-21.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-25.2015-01-25.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-26.2015-01-26.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-30.2015-01-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-31.2015-01-31.1.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-01-26.2015-02-01.2.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                ],
            ],
            [
                ['idarchive' => '106', 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => '1', 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-02', 'date2' => '2015-01-02', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-03', 'date2' => '2015-01-03', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-04', 'date2' => '2015-01-04', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-05', 'date2' => '2015-01-11', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-05', 'date2' => '2015-01-05', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => '16', 'idsite' => '1', 'date1' => '2015-01-06', 'date2' => '2015-01-06', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-07', 'date2' => '2015-01-07', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-08', 'date2' => '2015-01-08', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-09', 'date2' => '2015-01-09', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-10', 'date2' => '2015-01-10', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => '31', 'idsite' => '1', 'date1' => '2015-01-11', 'date2' => '2015-01-11', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-12', 'date2' => '2015-01-18', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-12', 'date2' => '2015-01-12', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-13', 'date2' => '2015-01-13', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-14', 'date2' => '2015-01-14', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-15', 'date2' => '2015-01-15', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => '46', 'idsite' => '1', 'date1' => '2015-01-16', 'date2' => '2015-01-16', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-17', 'date2' => '2015-01-17', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-18', 'date2' => '2015-01-18', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-19', 'date2' => '2015-01-25', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-19', 'date2' => '2015-01-19', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-20', 'date2' => '2015-01-20', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => '61', 'idsite' => '1', 'date1' => '2015-01-21', 'date2' => '2015-01-21', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-22', 'date2' => '2015-01-22', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-23', 'date2' => '2015-01-23', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-24', 'date2' => '2015-01-24', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-25', 'date2' => '2015-01-25', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => '76', 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-01-26', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-02-01', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-27', 'date2' => '2015-01-27', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-28', 'date2' => '2015-01-28', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-29', 'date2' => '2015-01-29', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-30', 'date2' => '2015-01-30', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                // archive id 106 occurs a second time as it comes from a different archive table
                ['idarchive' => '106', 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
                ['idarchive' => '91', 'idsite' => '1', 'date1' => '2015-01-31', 'date2' => '2015-01-31', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null],
            ],
        ];

        yield 'removing all periods' => [
            [1],
            ['2015-05-05'],
            null,
            null,
            false,
            [
                '2015_01' => [
                    '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                ],
                '2015_05' => [
                    '1.2015-05-05.2015-05-05.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '1.2015-05-04.2015-05-10.2.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-05-01.2015-05-31.3.done3736b708e4d20cfc10610e816a1b2341',
                ],
            ],
            [
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-05-05', 'date2' => '2015-05-05', 'period' => '1', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-05-04', 'date2' => '2015-05-10', 'period' => '2', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-05-01', 'date2' => '2015-05-31', 'period' => '3', 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done', 'report' => null],
            ],
        ];

        yield 'removing all periods, all visits only' => [
            [1],
            ['2015-02-04'],
            null,
            '',
            false,
            [
                '2015_02' => [
                    '1.2015-02-04.2015-02-04.1.done',
                    '1.2015-02-02.2015-02-08.2.done',
                    '1.2015-02-01.2015-02-28.3.done.VisitsSummary'
                ],
            ],
            [
                ['idarchive' => 10, 'idsite' => 1, 'date1' => '2015-02-04', 'date2' => '2015-02-04', 'period' => 1, 'name' => 'done', 'report' => null,],
                ['idarchive' => 85, 'idsite' => 1, 'date1' => '2015-02-02', 'date2' => '2015-02-08', 'period' => 2, 'name' => 'done', 'report' => null,],
                ['idarchive' => null, 'idsite' => 1, 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => 4, 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => 1, 'date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => 3, 'name' => 'done', 'report' => null,],
            ],
        ];

        yield 'period before site creation date' => [
            [1],
            ['2012-03-02'],
            '',
            null,
            false,
            [
                // empty
            ],
            [
                // month week and year exist, but not day since it is before the site was created
                ['idarchive' => null, 'idsite' => 1, 'date1' => '2012-03-01', 'date2' => '2012-03-31', 'period' => 3, 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => 1, 'date1' => '2012-02-27', 'date2' => '2012-03-04', 'period' => 2, 'name' => 'done', 'report' => null],
                ['idarchive' => null, 'idsite' => 1, 'date1' => '2012-01-01', 'date2' => '2012-12-31', 'period' => 4, 'name' => 'done', 'report' => null],
            ],
        ];

        yield 'day period, multiple sites, multiple dates across tables, stored segments added' => [
            [1, 2],
            ['2015-01-01', '2015-02-05', '2015-04-30'],
            'day',
            null,
            false,
            [
                '2015_04' => [
                    '1.2015-04-30.2015-04-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '2.2015-04-30.2015-04-30.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-04-27.2015-05-03.2.done',
                    '2.2015-04-27.2015-05-03.2.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-04-01.2015-04-30.3.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '2.2015-04-01.2015-04-30.3.done5447835b0a861475918e79e932abdfd8',
                ],
                '2015_01' => [
                    '1.2015-01-01.2015-01-01.1.done3736b708e4d20cfc10610e816a1b2341',
                    '2.2015-01-01.2015-01-01.1.done.VisitsSummary',
                    '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                    '2.2015-01-01.2015-01-31.3.done.VisitsSummary',
                    '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                    '2.2015-01-01.2015-12-31.4.done',
                    '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                ],
                '2015_02' => [
                    '1.2015-02-05.2015-02-05.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    '2.2015-02-05.2015-02-05.1.done5447835b0a861475918e79e932abdfd8',
                    '1.2015-02-02.2015-02-08.2.done',
                    '2.2015-02-02.2015-02-08.2.done3736b708e4d20cfc10610e816a1b2341',
                    '1.2015-02-01.2015-02-28.3.done.VisitsSummary',
                    '2.2015-02-01.2015-02-28.3.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                ],
                '2014_12' => [
                    '1.2014-12-29.2015-01-04.2.done3736b708e4d20cfc10610e816a1b2341',
                    '2.2014-12-29.2015-01-04.2.done.VisitsSummary',
                ],
            ],
            [
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done', 'report' => null,],
                ['idarchive' => '1', 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null,],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done', 'report' => null,],
                ['idarchive' => '106', 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null,],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done', 'report' => null,],
                ['idarchive' => '109', 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done5447835b0a861475918e79e932abdfd8', 'report' => null,],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done', 'report' => null,],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done', 'report' => null,],
                ['idarchive' => '110', 'idsite' => '2', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done', 'report' => null,],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done', 'report' => null,],
                ['idarchive' => '106', 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null,],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done', 'report' => null,],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-02-05', 'date2' => '2015-02-05', 'period' => '1', 'name' => 'done', 'report' => null,],
                ['idarchive' => '85', 'idsite' => '1', 'date1' => '2015-02-02', 'date2' => '2015-02-08', 'period' => '2', 'name' => 'done', 'report' => null,],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => '3', 'name' => 'done', 'report' => null,],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-02-05', 'date2' => '2015-02-05', 'period' => '1', 'name' => 'done', 'report' => null,],
                ['idarchive' => '14', 'idsite' => '2', 'date1' => '2015-02-05', 'date2' => '2015-02-05', 'period' => '1', 'name' => 'done5447835b0a861475918e79e932abdfd8', 'report' => null,],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-02-02', 'date2' => '2015-02-08', 'period' => '2', 'name' => 'done', 'report' => null,],
                ['idarchive' => '86', 'idsite' => '2', 'date1' => '2015-02-02', 'date2' => '2015-02-08', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null,],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => '3', 'name' => 'done', 'report' => null,],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-04-30', 'date2' => '2015-04-30', 'period' => '1', 'name' => 'done', 'report' => null,],
                ['idarchive' => '100', 'idsite' => '1', 'date1' => '2015-04-27', 'date2' => '2015-05-03', 'period' => '2', 'name' => 'done', 'report' => null,],
                ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-04-01', 'date2' => '2015-04-30', 'period' => '3', 'name' => 'done', 'report' => null,],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-04-30', 'date2' => '2015-04-30', 'period' => '1', 'name' => 'done', 'report' => null,],
                ['idarchive' => '89', 'idsite' => '2', 'date1' => '2015-04-30', 'date2' => '2015-04-30', 'period' => '1', 'name' => 'done5447835b0a861475918e79e932abdfd8', 'report' => null,],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-04-27', 'date2' => '2015-05-03', 'period' => '2', 'name' => 'done', 'report' => null,],
                ['idarchive' => '101', 'idsite' => '2', 'date1' => '2015-04-27', 'date2' => '2015-05-03', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341', 'report' => null,],
                ['idarchive' => null, 'idsite' => '2', 'date1' => '2015-04-01', 'date2' => '2015-04-30', 'period' => '3', 'name' => 'done', 'report' => null,],
                ['idarchive' => '104', 'idsite' => '2', 'date1' => '2015-04-01', 'date2' => '2015-04-30', 'period' => '3', 'name' => 'done5447835b0a861475918e79e932abdfd8', 'report' => null,],
            ],
            null, // report name
            true, // add stored segments
        ];
    }

    /**
     * @dataProvider getTestDataForMarkArchiveRangesAsInvalidated
     */
    public function testMarkArchivesAsInvalidatedMarksAllSubrangesOfRange($idSites, $dates, $segment, $expectedIdArchives)
    {
        $dates = array_map(array('Piwik\Date', 'factory'), $dates);

        $this->insertArchiveRowsForTest();

        if (!empty($segment)) {
            $segment = new Segment($segment, $idSites);
        }

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');
        $result = $archiveInvalidator->markArchivesOverlappingRangeAsInvalidated($idSites, array($dates), $segment);

        $this->assertEquals(array($dates[0]), $result->processedDates);

        $idArchives = $this->getInvalidatedArchives();

        // Remove empty values (some new empty entries may be added each month)
        $idArchives = array_filter($idArchives);
        $expectedIdArchives = array_filter($expectedIdArchives);

        $this->assertEquals($expectedIdArchives, $idArchives);
    }

    public function getTestDataForMarkArchiveRangesAsInvalidated()
    {
        yield 'range period, has an exact match, also a match where DB end date = reference start date' => [
            [1],
            ['2015-01-01', '2015-01-10'],
            null,
            [
                '2014_12' => [
                    '1.2014-12-05.2015-01-01.5.done.VisitsSummary',
                ],
                '2015_01' => [
                    '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                ],
            ],
            [
                // empty
            ],
        ];

        yield 'range period, overlapping range = a match' => [
            [1],
            ['2015-01-02', '2015-03-05'],
            null,
            [
                '2015_01' => [
                    '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                ],
                '2015_03' => [
                    '1.2015-03-04.2015-03-05.5.done.VisitsSummary',
                    '1.2015-03-05.2015-03-10.5.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                ],
            ],
            [
                // empty
            ],
        ];

        yield 'range period, small range within the 2014-12-05 to 2015-01-01 range should cause it to be invalidated' => [
            [1],
            ['2014-12-18', '2014-12-20'],
            null,
            [
                '2014_12' => [
                    '1.2014-12-05.2015-01-01.5.done.VisitsSummary',
                ],
            ],
            [
                // empty
            ],
        ];

        yield 'range period, range that overlaps start of archived range' => [
            [1],
            ['2014-12-01', '2014-12-05'],
            null,
            [
                '2014_12' => [
                    '1.2014-12-05.2015-01-01.5.done.VisitsSummary',
                ],
            ],
            [
                // empty
            ],
        ];

        yield 'range period, large range that includes the smallest archived range (3 to 4 March)' => [
            [1],
            ['2015-01-11', '2015-03-30'],
            null,
            [
                '2015_03' => [
                    '1.2015-03-04.2015-03-05.5.done.VisitsSummary',
                    '1.2015-03-05.2015-03-10.5.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                ],
            ],
            [
                // empty
            ],
        ];

        yield 'range period, doesn\'t match any archived ranges' => [
            [1],
            ['2014-12-01', '2014-12-04'],
            null,
            [
            ],
            [
                // empty
            ],
        ];

        yield 'three-month range period, there\'s a range archive for the middle month' => [
            [1],
            ['2014-09-01', '2014-11-08'],
            null,
            [
                '2014_10' => [
                    '1.2014-10-15.2014-10-20.5.done3736b708e4d20cfc10610e816a1b2341',
                ],
            ],
            [
                // empty
            ],
        ];
    }

    public function testMarkArchivesAsInvalidatedForceInvalidatesNonExistantRangesWhenRequired()
    {
        $archives = $this->getInvalidatedArchives();
        $this->assertEmpty($archives);

        $this->invalidator->markArchivesAsInvalidated([1], ['2015-03-04,2015-03-06', '2016-04-03,2016-05-12'], 'range', null, false);

        $archives = $this->getInvalidatedArchives();
        $this->assertEmpty($archives);

        $this->invalidator->markArchivesAsInvalidated([1], ['2015-03-04,2015-03-06', '2016-04-03,2016-05-12'], 'range', null, false, true);

        $archives = $this->getInvalidatedArchives();
        $this->assertEquals([], $archives);

        $expectedInvalidatedTableEntries = [
            ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-03-04', 'date2' => '2015-03-06', 'period' => '5', 'name' => 'done', 'report' => null],
            ['idarchive' => null, 'idsite' => '1', 'date1' => '2016-04-03', 'date2' => '2016-05-12', 'period' => '5', 'name' => 'done', 'report' => null],
        ];

        $invalidatedTableEntries = $this->getInvalidatedArchiveTableEntries();
        $this->assertEquals($expectedInvalidatedTableEntries, $invalidatedTableEntries);
    }

    public function testMarkArchivesAsInvalidatedInvalidatesIndividualPluginNames()
    {
        $idSites = [1];
        $dates = ['2015-01-11'];
        $period = 'day';
        $segment = new Segment('', [1]);
        $cascadeDown = false;
        $expectedIdArchives = [];
        $expectedInvalidatedArchives = [
            ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done.ExamplePlugin', 'report' => null],
            ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done.ExamplePlugin', 'report' => null],
            ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-05', 'date2' => '2015-01-11', 'period' => '2', 'name' => 'done.ExamplePlugin', 'report' => null],
            ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-11', 'date2' => '2015-01-11', 'period' => '1', 'name' => 'done.ExamplePlugin', 'report' => null],
        ];
        $plugin = 'ExamplePlugin';

        $this->testMarkArchivesAsInvalidatedMarksCorrectArchivesAsInvalidated(
            $idSites,
            $dates,
            $period,
            $segment,
            $cascadeDown,
            $expectedIdArchives,
            $expectedInvalidatedArchives,
            $plugin
        );
    }

    public function testMarkArchivesAsInvalidatedInvalidatesIndividualReports()
    {
        $idSites = [1];
        $dates = ['2015-01-11'];
        $period = 'day';
        $segment = new Segment('', [1]);
        $cascadeDown = false;
        $expectedIdArchives = [];
        $expectedInvalidatedArchives = [
            ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done.ExamplePlugin', 'report' => 'someReport'],
            ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done.ExamplePlugin', 'report' => 'someReport'],
            ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-05', 'date2' => '2015-01-11', 'period' => '2', 'name' => 'done.ExamplePlugin', 'report' => 'someReport'],
            ['idarchive' => null, 'idsite' => '1', 'date1' => '2015-01-11', 'date2' => '2015-01-11', 'period' => '1', 'name' => 'done.ExamplePlugin', 'report' => 'someReport'],
        ];
        $report = 'ExamplePlugin.someReport';

        $this->testMarkArchivesAsInvalidatedMarksCorrectArchivesAsInvalidated(
            $idSites,
            $dates,
            $period,
            $segment,
            $cascadeDown,
            $expectedIdArchives,
            $expectedInvalidatedArchives,
            $report
        );
    }

    public function testMarkArchivesAsInvalidatedDoesNotInsertDuplicateInvalidations()
    {
        $this->insertArchiveRowsForTest();

        $segment = 'browserCode==IE';
        $segment = new Segment($segment, [1]);

        $segmentHash = $segment->getHash();

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');

        $existingInvalidations = [
            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => 2, 'report' => null],

            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-05-04', 'date2' => '2020-05-04', 'period' => 1, 'report' => null],
            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-05-05', 'date2' => '2020-05-05', 'period' => 1, 'report' => null],
            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-05-06', 'date2' => '2020-05-06', 'period' => 1, 'report' => null],
            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-05-07', 'date2' => '2020-05-07', 'period' => 1, 'report' => null],
            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-05-08', 'date2' => '2020-05-08', 'period' => 1, 'report' => null],
            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-05-09', 'date2' => '2020-05-09', 'period' => 1, 'report' => null],
            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-05-10', 'date2' => '2020-05-10', 'period' => 1, 'report' => null],

            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-05-01', 'date2' => '2020-05-31', 'period' => 3, 'report' => null],

            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-05-01', 'date2' => '2020-05-31', 'period' => 4, 'report' => 'aReport'],
            ['name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2020-05-01', 'date2' => '2020-05-31', 'period' => 4, 'report' => 'anotherReport'],
        ];

        $this->insertInvalidations($existingInvalidations);

        $archiveInvalidator->markArchivesAsInvalidated(
            [1],
            ['2020-03-04', '2020-05-06'],
            'week',
            $segment,
            $cascadeDown = true,
            false
        );
        $archiveInvalidator->markArchivesAsInvalidated(
            [1],
            ['2020-05-01'],
            'year',
            $segment,
            $cascadeDown = false,
            'aReport'
        );

        $expectedInvalidations = [
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-02',
                'date2' => '2020-03-08',
                'period' => '2',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-04',
                'date2' => '2020-05-04',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-05',
                'date2' => '2020-05-05',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-06',
                'date2' => '2020-05-06',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-07',
                'date2' => '2020-05-07',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-08',
                'date2' => '2020-05-08',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-09',
                'date2' => '2020-05-09',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-10',
                'date2' => '2020-05-10',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-01',
                'date2' => '2020-05-31',
                'period' => '3',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-01',
                'date2' => '2020-05-31',
                'period' => '4',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => 'aReport',
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-01',
                'date2' => '2020-05-31',
                'period' => '4',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => 'anotherReport',
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-02',
                'date2' => '2020-03-02',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-01',
                'date2' => '2020-03-31',
                'period' => '3',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-03',
                'date2' => '2020-03-03',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-04',
                'date2' => '2020-03-04',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-05',
                'date2' => '2020-03-05',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-06',
                'date2' => '2020-03-06',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-07',
                'date2' => '2020-03-07',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-03-08',
                'date2' => '2020-03-08',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-01-01',
                'date2' => '2020-12-31',
                'period' => '4',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
            array (
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2020-05-04',
                'date2' => '2020-05-10',
                'period' => '2',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                'report' => null,
            ),
        ];

        $actualInvalidations = $this->getInvalidatedArchiveTableEntries();

        $this->assertEquals($expectedInvalidations, $actualInvalidations);
    }

    public function testReArchiveReportCreatesCorrectInvalidationEntriesForAllSitesIfAllSpecified()
    {
        Date::$now = strtotime('2020-06-16 12:00:00');

        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = 'last1';

        $this->invalidator->scheduleReArchiving('all', 'VisitsSummary');
        $this->invalidator->applyScheduledReArchiving();

        $countInvalidations = $this->getNumInvalidations();

        $invalidationSites = Db::fetchAll("SELECT DISTINCT idsite FROM " . Common::prefixTable('archive_invalidations'));
        $invalidationSites = array_column($invalidationSites, 'idsite');

        $this->assertEquals(570, $countInvalidations);
        $this->assertEquals([1,2,3,4,5,6,7,8,9,10], $invalidationSites);
    }

    public function testReArchiveReportCreatesCorrectInvalidationEntriesIfReArchivingSegments()
    {
        Date::$now = strtotime('2020-06-16 12:00:00');

        Rules::setBrowserTriggerArchiving(false);
        API::getInstance()->add('autoArchiveSegment', 'browserCode==IE', false, true);
        API::getInstance()->add('secondArchiveSegment', 'browserCode==FF', false, true);
        Rules::setBrowserTriggerArchiving(true);

        $reArchiveList = new ReArchiveList();
        $reArchiveList->setAll([]); // clear list since adding segments will add to it

        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = '1';
        Config::getInstance()->General['rearchive_reports_in_past_exclude_segments'] = 0;

        $this->invalidator->scheduleReArchiving(1);
        $this->invalidator->applyScheduledReArchiving();

        $invalidationNames = Db::fetchAll("SELECT `name` FROM " . Common::prefixTable('archive_invalidations'));
        $invalidationNames = array_column($invalidationNames, 'name');

        $expectedCount = 171;
        $this->assertCount($expectedCount, $invalidationNames);

        $invalidationNames = array_unique($invalidationNames);
        $invalidationNames = array_values($invalidationNames);

        $expectedInvalidationNames = [
            'done',
            'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
            'done3736b708e4d20cfc10610e816a1b2341',
        ];
        $this->assertEquals($expectedInvalidationNames, $invalidationNames);
    }

    public function testReArchiveReportCreatesCorrectInvalidationEntriesIfNotReArchivingSegments()
    {
        Date::$now = strtotime('2020-06-16 12:00:00');

        Rules::setBrowserTriggerArchiving(false);
        API::getInstance()->add('autoArchiveSegment', 'browserCode==IE', false, true);
        API::getInstance()->add('secondArchiveSegment', 'browserCode==FF', false, true);
        Rules::setBrowserTriggerArchiving(true);

        $reArchiveList = new ReArchiveList();
        $reArchiveList->setAll([]); // clear list since adding segments will add to it

        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = 1;
        Config::getInstance()->General['rearchive_reports_in_past_exclude_segments'] = 1;

        $this->invalidator->scheduleReArchiving(1);
        $this->invalidator->applyScheduledReArchiving();

        $invalidationNames = Db::fetchAll("SELECT `name` FROM " . Common::prefixTable('archive_invalidations'));
        $invalidationNames = array_column($invalidationNames, 'name');

        $expectedCount = 57;
        $this->assertCount($expectedCount, $invalidationNames);

        $invalidationNames = array_unique($invalidationNames);
        $invalidationNames = array_values($invalidationNames);

        $expectedInvalidationNames = [
            'done',
        ];
        $this->assertEquals($expectedInvalidationNames, $invalidationNames);
    }

    private function getNumInvalidations()
    {
        return Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('archive_invalidations'));
    }

    public function testScheduleReArchivingCleanupWhenReportGiven()
    {
        $this->invalidator->scheduleReArchiving([1, 2, 3], 'ExamplePlugin', '5');
        $this->invalidator->applyScheduledReArchiving();
        $numInvalidations = $this->getNumInvalidations();
        $this->assertGreaterThanOrEqual(600, $numInvalidations);

        $this->invalidator->scheduleReArchiving([1, 2, 3], 'ExamplePlugin', '5');
        $this->invalidator->applyScheduledReArchiving();
        // should not end up having twice the amount of invalidations but delete existing
        $this->assertEquals($numInvalidations, $this->getNumInvalidations());
    }
    public function testReArchiveReportCreatesCorrectInvalidationEntriesIfNoReportSpecified()
    {
        Date::$now = strtotime('2020-06-16 12:00:00');

        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = 'last1';

        $this->invalidator->reArchiveReport([1], 'VisitsSummary');

        $expectedInvalidations = [
            array (
                'idsite' => '1',
                'period' => '1',
                'name' => 'done.VisitsSummary',
                'report' => null,
                'dates' => '2020-05-01,2020-05-01|2020-05-02,2020-05-02|2020-05-03,2020-05-03|2020-05-04,2020-05-04|2020-05-05,2020-05-05|2020-05-06,2020-05-06'
                    . '|2020-05-07,2020-05-07|2020-05-08,2020-05-08|2020-05-09,2020-05-09|2020-05-10,2020-05-10|2020-05-11,2020-05-11|2020-05-12,2020-05-12'
                    . '|2020-05-13,2020-05-13|2020-05-14,2020-05-14|2020-05-15,2020-05-15|2020-05-16,2020-05-16|2020-05-17,2020-05-17|2020-05-18,2020-05-18'
                    . '|2020-05-19,2020-05-19|2020-05-20,2020-05-20|2020-05-21,2020-05-21|2020-05-22,2020-05-22|2020-05-23,2020-05-23|2020-05-24,2020-05-24'
                    . '|2020-05-25,2020-05-25|2020-05-26,2020-05-26|2020-05-27,2020-05-27|2020-05-28,2020-05-28|2020-05-29,2020-05-29|2020-05-30,2020-05-30'
                    . '|2020-05-31,2020-05-31|2020-06-01,2020-06-01|2020-06-02,2020-06-02|2020-06-03,2020-06-03|2020-06-04,2020-06-04|2020-06-05,2020-06-05'
                    . '|2020-06-06,2020-06-06|2020-06-07,2020-06-07|2020-06-08,2020-06-08|2020-06-09,2020-06-09|2020-06-10,2020-06-10|2020-06-11,2020-06-11'
                    . '|2020-06-12,2020-06-12|2020-06-13,2020-06-13|2020-06-14,2020-06-14|2020-06-15,2020-06-15',
                'count' => '46',
            ),
            array (
                'idsite' => '1',
                'period' => '2',
                'name' => 'done.VisitsSummary',
                'report' => null,
                'dates' => '2020-05-04,2020-05-10|2020-05-11,2020-05-17|2020-05-18,2020-05-24|2020-05-25,2020-05-31|2020-04-27,2020-05-03|2020-06-01,2020-06-07'
                    . '|2020-06-08,2020-06-14|2020-06-15,2020-06-21',
                'count' => '8',
            ),
            array (
                'idsite' => '1',
                'period' => '3',
                'name' => 'done.VisitsSummary',
                'report' => null,
                'dates' => '2020-05-01,2020-05-31|2020-06-01,2020-06-30',
                'count' => '2',
            ),
            array (
                'idsite' => '1',
                'period' => '4',
                'name' => 'done.VisitsSummary',
                'report' => null,
                'dates' => '2020-01-01,2020-12-31',
                'count' => '1',
            ),
        ];

        $actualInvalidations = $this->getInvalidatedArchiveTableEntriesSummary();

        $this->assertEquals($expectedInvalidations, $actualInvalidations);
    }

    public function testReArchiveReportCreatesCorrectInvalidationEntriesIfReportSpecified()
    {
        Date::$now = strtotime('2020-06-16 12:00:00');

        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = 'last1';

        $this->invalidator->reArchiveReport([1], 'VisitsSummary', 'some.Report');

        $expectedInvalidations = [
            array (
                'idsite' => '1',
                'period' => '1',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-05-01,2020-05-01|2020-05-02,2020-05-02|2020-05-03,2020-05-03|2020-05-04,2020-05-04|2020-05-05,2020-05-05'
                    . '|2020-05-06,2020-05-06|2020-05-07,2020-05-07|2020-05-08,2020-05-08|2020-05-09,2020-05-09|2020-05-10,2020-05-10'
                    . '|2020-05-11,2020-05-11|2020-05-12,2020-05-12|2020-05-13,2020-05-13|2020-05-14,2020-05-14|2020-05-15,2020-05-15'
                    . '|2020-05-16,2020-05-16|2020-05-17,2020-05-17|2020-05-18,2020-05-18|2020-05-19,2020-05-19|2020-05-20,2020-05-20'
                    . '|2020-05-21,2020-05-21|2020-05-22,2020-05-22|2020-05-23,2020-05-23|2020-05-24,2020-05-24|2020-05-25,2020-05-25'
                    . '|2020-05-26,2020-05-26|2020-05-27,2020-05-27|2020-05-28,2020-05-28|2020-05-29,2020-05-29|2020-05-30,2020-05-30'
                    . '|2020-05-31,2020-05-31|2020-06-01,2020-06-01|2020-06-02,2020-06-02|2020-06-03,2020-06-03|2020-06-04,2020-06-04'
                    . '|2020-06-05,2020-06-05|2020-06-06,2020-06-06|2020-06-07,2020-06-07|2020-06-08,2020-06-08|2020-06-09,2020-06-09'
                    . '|2020-06-10,2020-06-10|2020-06-11,2020-06-11|2020-06-12,2020-06-12|2020-06-13,2020-06-13|2020-06-14,2020-06-14'
                    . '|2020-06-15,2020-06-15',
                'count' => '46',
            ),
            array (
                'idsite' => '1',
                'period' => '2',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-05-04,2020-05-10|2020-05-11,2020-05-17|2020-05-18,2020-05-24|2020-05-25,2020-05-31|2020-04-27,2020-05-03'
                    . '|2020-06-01,2020-06-07|2020-06-08,2020-06-14|2020-06-15,2020-06-21',
                'count' => '8',
            ),
            array (
                'idsite' => '1',
                'period' => '3',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-05-01,2020-05-31|2020-06-01,2020-06-30',
                'count' => '2',
            ),
            array (
                'idsite' => '1',
                'period' => '4',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-01-01,2020-12-31',
                'count' => '1',
            ),
        ];

        $actualInvalidations = $this->getInvalidatedArchiveTableEntriesSummary();

        $this->assertEquals($expectedInvalidations, $actualInvalidations);
    }

    public function testReArchiveAcceptsCustomStartDate()
    {
        Date::$now = strtotime('2020-06-16 12:00:00');

        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = 'last3';

        $customStartDate = Date::yesterday()->subMonth(1)->setDay(1);
        $this->invalidator->reArchiveReport([1], 'VisitsSummary', 'some.Report', $customStartDate);

        $expectedInvalidations = [
            array (
                'idsite' => '1',
                'period' => '1',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-05-01,2020-05-01|2020-05-02,2020-05-02|2020-05-03,2020-05-03|2020-05-04,2020-05-04|2020-05-05,2020-05-05'
                    . '|2020-05-06,2020-05-06|2020-05-07,2020-05-07|2020-05-08,2020-05-08|2020-05-09,2020-05-09|2020-05-10,2020-05-10'
                    . '|2020-05-11,2020-05-11|2020-05-12,2020-05-12|2020-05-13,2020-05-13|2020-05-14,2020-05-14|2020-05-15,2020-05-15'
                    . '|2020-05-16,2020-05-16|2020-05-17,2020-05-17|2020-05-18,2020-05-18|2020-05-19,2020-05-19|2020-05-20,2020-05-20'
                    . '|2020-05-21,2020-05-21|2020-05-22,2020-05-22|2020-05-23,2020-05-23|2020-05-24,2020-05-24|2020-05-25,2020-05-25'
                    . '|2020-05-26,2020-05-26|2020-05-27,2020-05-27|2020-05-28,2020-05-28|2020-05-29,2020-05-29|2020-05-30,2020-05-30'
                    . '|2020-05-31,2020-05-31|2020-06-01,2020-06-01|2020-06-02,2020-06-02|2020-06-03,2020-06-03|2020-06-04,2020-06-04'
                    . '|2020-06-05,2020-06-05|2020-06-06,2020-06-06|2020-06-07,2020-06-07|2020-06-08,2020-06-08|2020-06-09,2020-06-09'
                    . '|2020-06-10,2020-06-10|2020-06-11,2020-06-11|2020-06-12,2020-06-12|2020-06-13,2020-06-13|2020-06-14,2020-06-14'
                    . '|2020-06-15,2020-06-15',
                'count' => '46',
            ),
            array (
                'idsite' => '1',
                'period' => '2',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-05-04,2020-05-10|2020-05-11,2020-05-17|2020-05-18,2020-05-24|2020-05-25,2020-05-31|2020-04-27,2020-05-03'
                    . '|2020-06-01,2020-06-07|2020-06-08,2020-06-14|2020-06-15,2020-06-21',
                'count' => '8',
            ),
            array (
                'idsite' => '1',
                'period' => '3',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-05-01,2020-05-31|2020-06-01,2020-06-30',
                'count' => '2',
            ),
            array (
                'idsite' => '1',
                'period' => '4',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-01-01,2020-12-31',
                'count' => '1',
            ),
        ];

        $actualInvalidations = $this->getInvalidatedArchiveTableEntriesSummary();

        $this->assertEquals($expectedInvalidations, $actualInvalidations);
    }

    public function testReArchiveAlsoInvalidatesSegments()
    {
        Date::$now = strtotime('2020-06-16 12:00:00');

        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = 'last2';
        Config::getInstance()->General['process_new_segments_from'] = 'beginning_of_time';

        $idSite = Fixture::createWebsite(Date::today()->subMonth(1)->getDatetime());

        $t = Fixture::getTracker($idSite, '2020-05-04 03:45:45');
        $t->setUrl('http://test.com/test');
        Fixture::checkResponse($t->doTrackPageView('test page'));

        Rules::setBrowserTriggerArchiving(false);
        API::getInstance()->add('autoArchiveSegment', 'browserCode==IE', false, true);
        API::getInstance()->add('browserArchiveSegment', 'browserCode==IE', false, false);
        Rules::setBrowserTriggerArchiving(true);

        $this->invalidator->reArchiveReport([$idSite], 'VisitsSummary', 'some.Report');

        $expectedInvalidations = [
            array (
                'idsite' => '11',
                'period' => '1',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-04-30,2020-04-30|2020-05-01,2020-05-01|2020-05-02,2020-05-02|2020-05-03,2020-05-03|2020-05-04,2020-05-04|2020-05-05,2020-05-05|2020-05-06,2020-05-06|2020-05-07,2020-05-07|2020-05-08,2020-05-08|2020-05-09,2020-05-09|2020-05-10,2020-05-10|2020-05-11,2020-05-11|2020-05-12,2020-05-12|2020-05-13,2020-05-13|2020-05-14,2020-05-14|2020-05-15,2020-05-15|2020-05-16,2020-05-16|2020-05-17,2020-05-17|2020-05-18,2020-05-18|2020-05-19,2020-05-19|2020-05-20,2020-05-20|2020-05-21,2020-05-21|2020-05-22,2020-05-22|2020-05-23,2020-05-23|2020-05-24,2020-05-24|2020-05-25,2020-05-25|2020-05-26,2020-05-26|2020-05-27,2020-05-27|2020-05-28,2020-05-28|2020-05-29,2020-05-29|2020-05-30,2020-05-30|2020-05-31,2020-05-31|2020-06-01,2020-06-01|2020-06-02,2020-06-02|2020-06-03,2020-06-03|2020-06-04,2020-06-04|2020-06-05,2020-06-05|2020-06-06,2020-06-06|2020-06-07,2020-06-07|2020-06-08,2020-06-08|2020-06-09,2020-06-09|2020-06-10,2020-06-10|2020-06-11,2020-06-11|2020-06-12,2020-06-12|2020-06-13,2020-06-13|2020-06-14,2020-06-14|2020-06-15,2020-06-15',
                'count' => '47',
            ),
            array (
                'idsite' => '11',
                'period' => '1',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-04-30,2020-04-30|2020-05-01,2020-05-01|2020-05-02,2020-05-02|2020-05-03,2020-05-03|2020-05-04,2020-05-04|2020-05-05,2020-05-05|2020-05-06,2020-05-06|2020-05-07,2020-05-07|2020-05-08,2020-05-08|2020-05-09,2020-05-09|2020-05-10,2020-05-10|2020-05-11,2020-05-11|2020-05-12,2020-05-12|2020-05-13,2020-05-13|2020-05-14,2020-05-14|2020-05-15,2020-05-15|2020-05-16,2020-05-16|2020-05-17,2020-05-17|2020-05-18,2020-05-18|2020-05-19,2020-05-19|2020-05-20,2020-05-20|2020-05-21,2020-05-21|2020-05-22,2020-05-22|2020-05-23,2020-05-23|2020-05-24,2020-05-24|2020-05-25,2020-05-25|2020-05-26,2020-05-26|2020-05-27,2020-05-27|2020-05-28,2020-05-28|2020-05-29,2020-05-29|2020-05-30,2020-05-30|2020-05-31,2020-05-31|2020-06-01,2020-06-01|2020-06-02,2020-06-02|2020-06-03,2020-06-03|2020-06-04,2020-06-04|2020-06-05,2020-06-05|2020-06-06,2020-06-06|2020-06-07,2020-06-07|2020-06-08,2020-06-08|2020-06-09,2020-06-09|2020-06-10,2020-06-10|2020-06-11,2020-06-11|2020-06-12,2020-06-12|2020-06-13,2020-06-13|2020-06-14,2020-06-14|2020-06-15,2020-06-15',
                'count' => '47',
            ),
            array (
                'idsite' => '11',
                'period' => '2',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-04-27,2020-05-03|2020-05-04,2020-05-10|2020-05-11,2020-05-17|2020-05-18,2020-05-24|2020-05-25,2020-05-31|2020-06-01,2020-06-07|2020-06-08,2020-06-14|2020-06-15,2020-06-21',
                'count' => '8',
            ),
            array (
                'idsite' => '11',
                'period' => '2',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-04-27,2020-05-03|2020-05-04,2020-05-10|2020-05-11,2020-05-17|2020-05-18,2020-05-24|2020-05-25,2020-05-31|2020-06-01,2020-06-07|2020-06-08,2020-06-14|2020-06-15,2020-06-21',
                'count' => '8',
            ),
            array (
                'idsite' => '11',
                'period' => '3',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-04-01,2020-04-30|2020-05-01,2020-05-31|2020-06-01,2020-06-30',
                'count' => '3',
            ),
            array (
                'idsite' => '11',
                'period' => '3',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-04-01,2020-04-30|2020-05-01,2020-05-31|2020-06-01,2020-06-30',
                'count' => '3',
            ),
            array (
                'idsite' => '11',
                'period' => '4',
                'name' => 'done.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-01-01,2020-12-31',
                'count' => '1',
            ),
            array (
                'idsite' => '11',
                'period' => '4',
                'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.VisitsSummary',
                'report' => 'some.Report',
                'dates' => '2020-01-01,2020-12-31',
                'count' => '1',
            ),
        ];

        $actualInvalidations = $this->getInvalidatedArchiveTableEntriesSummary();

        $this->assertEquals($expectedInvalidations, $actualInvalidations);
    }

    private function getInvalidatedIdArchives()
    {
        $result = array();
        foreach (ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE) as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);

            $idArchives = Db::query("SELECT idarchive FROM $table WHERE name LIKE 'done%' AND value = ?", array(ArchiveWriter::DONE_INVALIDATED))->fetchAll(\Zend_Db::FETCH_COLUMN);

            $result[$date] = $idArchives;
        }
        return $result;
    }

    private function getAvailableArchives()
    {
        $result = [];
        foreach (ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE) as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);

            $sql = "SELECT idsite, date1, date2, period, name, value FROM $table WHERE name LIKE 'done%'";

            $archiveSpecs = Db::query($sql)->fetchAll();
            if (empty($archiveSpecs)) {
                continue;
            }

            $result[$date] = $archiveSpecs;
        }
        return $result;
    }

    private function getInvalidatedArchives($anyTsArchived = true)
    {
        $result = array();
        foreach (ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE) as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);

            $sql = "SELECT CONCAT(idsite, '.', date1, '.', date2, '.', period, '.', name) FROM $table WHERE name LIKE 'done%' AND value = ?";
            if (!$anyTsArchived) {
                $sql .= " AND ts_archived IS NOT NULL";
            }

            $archiveSpecs = Db::query($sql, array(ArchiveWriter::DONE_INVALIDATED))->fetchAll(\Zend_Db::FETCH_COLUMN);
            if (empty($archiveSpecs)) {
                continue;
            }

            $result[$date] = $archiveSpecs;
        }
        return $result;
    }

    private function insertArchiveRowsForTest()
    {
        $periods = array('day', 'week', 'month', 'year');
        $sites = array(1,2,3);

        $startDate = Date::factory('2014-12-01');
        $endDate = Date::factory('2015-05-31');

        foreach ($periods as $periodLabel) {
            $nextEndDate = $endDate->addPeriod(1, $periodLabel);
            for ($date = $startDate; $date->isEarlier($nextEndDate); $date = $date->addPeriod(1, $periodLabel)) {
                foreach ($sites as $idSite) {
                    $this->insertArchiveRow($idSite, $date->toString(), $periodLabel);
                }
            }
        }

        $rangePeriods = array(
            '2015-03-04,2015-03-05',
            '2014-12-05,2015-01-01',
            '2015-03-05,2015-03-10',
            '2015-01-01,2015-01-10',
            '2014-10-15,2014-10-20'
        );
        foreach ($rangePeriods as $dateRange) {
            $this->insertArchiveRow($idSite = 1, $dateRange, 'range');
        }
    }

    private function insertArchiveRow($idSite, $date, $periodLabel, $doneValue = ArchiveWriter::DONE_OK, $plugin = false, $varyArchiveTypes = true)
    {
        $periodObject = \Piwik\Period\Factory::build($periodLabel, $date);
        $dateStart = $periodObject->getDateStart();
        $dateEnd = $periodObject->getDateEnd();

        $table = ArchiveTableCreator::getNumericTable($dateStart);

        $model = new Model();
        $idArchive = $model->allocateNewArchiveId($table);

        $periodId = Piwik::$idPeriods[$periodLabel];

        if ($varyArchiveTypes) {
            $doneFlag = 'done';
            if ($idArchive % 5 == 1) {
                $doneFlag = Rules::getDoneFlagArchiveContainsAllPlugins(self::$segment1);
            } elseif ($idArchive % 5 == 2) {
                $doneFlag .= '.VisitsSummary';
            } elseif ($idArchive % 5 == 3) {
                $doneFlag = Rules::getDoneFlagArchiveContainsOnePlugin(self::$segment1, 'UserCountry');
            } elseif ($idArchive % 5 == 4) {
                $doneFlag = Rules::getDoneFlagArchiveContainsAllPlugins(self::$segment2);
            }
        } else {
            $doneFlag = $plugin ? 'done.' . $plugin : 'done';
        }

        $sql = "INSERT INTO $table (idarchive, name, value, idsite, date1, date2, period, ts_archived)
                     VALUES ($idArchive, 'nb_visits', 1, $idSite, '$dateStart', '$dateEnd', $periodId, NOW()),
                            ($idArchive, '$doneFlag', $doneValue, $idSite, '$dateStart', '$dateEnd', $periodId, NOW())";
        Db::query($sql);
    }

    private function getInvalidatedArchiveTableEntries($includeStatus = false)
    {
        return Db::fetchAll("SELECT idarchive, idsite, date1, date2, period, name, report" . ($includeStatus ? ', status' : '') . " FROM " . Common::prefixTable('archive_invalidations') . " ORDER BY idinvalidation");
    }

    private function assertEqualsSorted(array $expectedEntries, array $invalidatedArchiveTableEntries)
    {
        $this->sortArray($expectedEntries);
        $this->sortArray($invalidatedArchiveTableEntries);

        $this->assertEquals($expectedEntries, $invalidatedArchiveTableEntries);
    }

    private function sortArray(array &$expectedEntries)
    {
        usort($expectedEntries, function ($lhs, $rhs) {
            return strcmp(json_encode($lhs), json_encode($rhs));
        });
    }

    private function getInvalidatedArchiveTableEntriesSummary()
    {
        Db::get()->query('SET SESSION group_concat_max_len=' . (128 * 1024));

        $table = Common::prefixTable('archive_invalidations');
        return Db::fetchAll("SELECT idsite, period, name, report, GROUP_CONCAT(CONCAT(date1, ',', date2) SEPARATOR '|') as dates, COUNT(*) as count FROM $table GROUP BY idsite, period, name, report ORDER BY idsite, period, name, report");
    }

    private static function addVisitToEachSite()
    {
        $t = Fixture::getTracker(1, '2012-04-05 00:00:00');
        $t->enableBulkTracking();
        for ($i = 0; $i < 10; ++$i) {
            $t->setIdSite($i + 1);
            $t->setUrl('http://test.com');
            self::assertTrue($t->doTrackPageView('test page'));
        }
        Fixture::checkBulkTrackingResponse($t->doBulkTrack());
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    private function insertInvalidations(array $invalidations)
    {
        $table = Common::prefixTable('archive_invalidations');
        foreach ($invalidations as $invalidation) {
            $sql = "INSERT INTO $table (name, idsite, date1, date2, period, ts_invalidated, report, ts_started, status) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
            Db::query($sql, [
                $invalidation['name'],
                $invalidation['idsite'],
                $invalidation['date1'],
                $invalidation['date2'],
                $invalidation['period'],
                $invalidation['report'],
                $invalidation['ts_started'] ?? null,
                $invalidation['status'] ?? 0,
            ]);
        }
    }
}
