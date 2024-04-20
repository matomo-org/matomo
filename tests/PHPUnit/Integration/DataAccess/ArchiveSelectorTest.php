<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\Archive\Chunk;
use Piwik\ArchiveProcessor\Rules;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period\Factory;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group Integration
 * @group ArchiveSelectorTest
 */
class ArchiveSelectorTest extends IntegrationTestCase
{
    public const TEST_SEGMENT = 'operatingSystemCode==WIN';

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    public function test_getArchiveIds_handlesCutOffGroupConcat()
    {
        Db::get()->query('SET SESSION group_concat_max_len=' . 20);

        $archiveRows =                 [
            ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done', 'value' => 1],
            ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 4, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 5, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 6, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 7, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 8, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 9, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 10, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 11, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 12, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 13, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 14, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 15, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
            ['idarchive' => 16, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5],
        ];

        $this->insertArchiveData($archiveRows);

        [$archiveIds, $archiveStates] = ArchiveSelector::getArchiveIdsAndStates(
            [1],
            [Factory::build('day', '2020-03-01')],
            new Segment('', [1]),
            ['Funnels'],
            true,
            true
        );

        $expectedArchiveIds = [
            'done.Funnels' => [
                '2020-03-01,2020-03-01' => ['16'],
            ],
        ];

        $expectedArchiveStates = [
            1 => [
                'done.Funnels' => [
                    '2020-03-01,2020-03-01' => [
                        16 => 5,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedArchiveIds, $archiveIds);
        $this->assertEquals($expectedArchiveStates, $archiveStates);
    }

    /**
     * @dataProvider getTestDataForGetArchiveIds
     */
    public function test_getArchiveIds_returnsCorrectResult(
        $archiveRows,
        $siteIds,
        $periods,
        $segment,
        $plugins,
        $expectedArchiveIds,
        $expectedArchiveStates
    ) {
        Fixture::createWebsite('2010-02-02 00:00:00');
        Fixture::createWebsite('2010-02-02 00:00:00');

        foreach ($periods as $index => [$periodStr, $dateStr]) {
            $periods[$index] = Factory::build($periodStr, $dateStr);
        }

        $this->insertArchiveData($archiveRows);

        [$archiveIds, $archiveStates] = ArchiveSelector::getArchiveIdsAndStates(
            $siteIds,
            $periods,
            new Segment($segment, $siteIds),
            $plugins
        );

        $this->assertEquals($expectedArchiveIds, $archiveIds);
        $this->assertEquals($expectedArchiveStates, $archiveStates);

        $archiveIds = ArchiveSelector::getArchiveIds(
            $siteIds,
            $periods,
            new Segment($segment, $siteIds),
            $plugins
        );

        $this->assertEquals($expectedArchiveIds, $archiveIds);
    }

    public function getTestDataForGetArchiveIds()
    {
        return [
            // normal single site
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done', 'value' => 4],

                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done', 'value' => 2],
                    ['idarchive' => 4, 'idsite' => 1, 'period' => 2, 'date1' => '2020-02-24', 'date2' => '2020-03-01', 'name' => 'done', 'value' => 2],

                    ['idarchive' => 5, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-03', 'date2' => '2020-03-03', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 6, 'idsite' => 2, 'period' => 2, 'date1' => '2020-02-24', 'date2' => '2020-03-01', 'name' => 'done', 'value' => 1],
                ],
                [1],
                [
                    ['day', '2020-03-01'],
                    ['week', '2020-03-02'],
                ],
                '',
                [],
                [
                    'done' => [
                        '2020-03-01,2020-03-01' => ['1'],
                        '2020-03-02,2020-03-08' => ['2'],
                    ],
                ],
                [
                    1 => [
                        'done' => [
                            '2020-03-01,2020-03-01' => [
                                1 => 1,
                            ],
                            '2020-03-02,2020-03-08' => [
                                2 => 4,
                            ],
                        ],
                    ],
                ],
            ],

            // two sites with results
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 2, 'idsite' => 2, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done', 'value' => 1],
                ],
                [1, 2],
                [
                    ['day', '2020-03-01'],
                    ['week', '2020-03-02'],
                ],
                '',
                [],
                [
                    'done' => [
                        '2020-03-01,2020-03-01' => ['1', '2'],
                    ],
                ],
                [
                    1 => [
                        'done' => [
                            '2020-03-01,2020-03-01' => [
                                1 => 1,
                            ],
                        ],
                    ],
                    2 => [
                        'done' => [
                            '2020-03-01,2020-03-01' => [
                                2 => 1,
                            ],
                        ],
                    ],
                ],
            ],

            // multiple partials for specific reports + normal + one malformed partial
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5, 'ts_archived' => '2020-03-03 01:00:00'],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5, 'ts_archived' => '2020-03-03 01:00:00'],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5, 'ts_archived' => '2020-03-03 01:00:00'],

                    ['idarchive' => 4, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done', 'value' => 1, 'ts_archived' => '2020-03-04 00:00:00'],

                    ['idarchive' => 5, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5, 'ts_archived' => '2020-03-04 01:00:00'],
                    ['idarchive' => 6, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5, 'ts_archived' => '2020-03-04 01:05:00'],
                    ['idarchive' => 7, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.Funnels', 'value' => 5, 'ts_archived' => '2020-03-04 01:07:00'],
                    ['idarchive' => 8, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.AnotherPlugin', 'value' => 5, 'ts_archived' => '2020-03-04 01:05:00'],
                    ['idarchive' => 9, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done.AnotherPlugin', 'value' => 5, 'ts_archived' => '2020-03-04 01:07:00'],
                    ['idarchive' => 10, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-01', 'name' => 'done', 'value' => 5, 'ts_archived' => '2020-03-04 01:07:00'],
                ],
                [1],
                [
                    ['day', '2020-03-01'],
                ],
                '',
                ['Funnels'],
                [
                    'done' => [
                        '2020-03-01,2020-03-01' => ['10', '4'],
                    ],
                    'done.Funnels' => [
                        '2020-03-01,2020-03-01' => ['7', '6', '5'],
                    ],
                ],
                [
                    1 => [
                        'done' => [
                            '2020-03-01,2020-03-01' => [
                                10 => '5',
                                4 => '1',
                            ],
                        ],
                        'done.Funnels' => [
                            '2020-03-01,2020-03-01' => [
                                7 => 5,
                                6 => 5,
                                5 => 5,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function insertArchiveData($archiveRows)
    {
        foreach ($archiveRows as $row) {
            if (!empty($row['is_blob_data'])) {
                $row['value'] = gzcompress($row['value']);
            }

            $d = Date::factory($row['date1']);
            $table = !empty($row['is_blob_data']) ? ArchiveTableCreator::getBlobTable($d) : ArchiveTableCreator::getNumericTable($d);
            $tsArchived = isset($row['ts_archived']) ? $row['ts_archived'] : Date::now()->getDatetime();
            Db::query(
                "INSERT INTO `$table` (idarchive, idsite, period, date1, date2, `name`, `value`, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$row['idarchive'], $row['idsite'], $row['period'], $row['date1'], $row['date2'], $row['name'], $row['value'], $tsArchived]
            );
        }
    }

    /**
     * @dataProvider getTestDataForGetArchiveIdAndVisits
     */
    public function test_getArchiveIdAndVisits_returnsCorrectResult($period, $date, $archiveRows, $segment, $minDateProcessed, $includeInvalidated, $expected)
    {
        Fixture::createWebsite('2010-02-02 00:00:00');

        Rules::setBrowserTriggerArchiving(false);
        API::getInstance()->add('test segment', self::TEST_SEGMENT, 0, 0); // processed in real time

        $this->insertArchiveData($archiveRows);

        $params = new \Piwik\ArchiveProcessor\Parameters(new Site(1), Factory::build($period, $date), new Segment($segment, [1]));
        $result = ArchiveSelector::getArchiveIdAndVisits($params, $minDateProcessed, $includeInvalidated);

        if ($result['tsArchived'] !== false) {
            Date::factory($result['tsArchived']);
        }

        unset($result['tsArchived']);

        // remove BC indexed values
        unset($result[0]);
        unset($result[1]);
        unset($result[2]);
        unset($result[3]);
        unset($result[4]);
        unset($result[5]);

        if (isset($result['existingRecords'])) {
            sort($result['existingRecords']);
        }
        if (isset($expected['existingRecords'])) {
            sort($expected['existingRecords']);
        }

        $this->assertEquals($expected, $result);
    }

    public function getTestDataForGetArchiveIdAndVisits()
    {
        $segment = urlencode(self::TEST_SEGMENT);
        $segmentHash = md5(self::TEST_SEGMENT);

        $minDateProcessed = Date::factory('2020-03-04 00:00:00')->subSeconds(900)->getDatetime();
        return [
            // no archive data found
            [ // nothing in the db
                'day',
                '2019-10-05',
                [],
                '',
                $minDateProcessed,
                true,
                ['idArchives' => false, 'visits' => false, 'visitsConverted' => false, 'archiveExists' => false, 'doneFlagValue' => false, 'existingRecords' => null],
            ],
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-06', 'date2' => '2019-10-06', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 3, 'idsite' => 2, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1],
                ],
                '',
                $minDateProcessed,
                true,
                ['idArchives' => false, 'visits' => false, 'visitsConverted' => false, 'archiveExists' => false, 'doneFlagValue' => false, 'existingRecords' => null],
            ],

            // value is not valid
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 4],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 2],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 3],
                    ['idarchive' => 4, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 99],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => false, 'visits' => false, 'visitsConverted' => false, 'archiveExists' => true, 'doneFlagValue' => '99', 'existingRecords' => null],
            ],
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 4],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 20],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 40],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 2],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 3],
                    ['idarchive' => 4, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 99],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => false, 'visits' => 0, 'visitsConverted' => 0, 'archiveExists' => true, 'doneFlagValue' => '99', 'existingRecords' => null],
            ],
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done.VisitsSummary', 'value' => 4],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 20],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 40],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => false, 'visits' => 20, 'visitsConverted' => 40, 'archiveExists' => true, 'doneFlagValue' => false, 'existingRecords' => null],
            ],
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done.VisitsSummary', 'value' => 1],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 30],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 50],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => false, 'visits' => 30, 'visitsConverted' => 50, 'archiveExists' => true, 'doneFlagValue' => false, 'existingRecords' => null],
            ],
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done.VisitsSummary', 'value' => 1],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 30],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 50],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done.VisitsSummary', 'value' => 4],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => false, 'visits' => false, 'visitsConverted' => false, 'archiveExists' => true, 'doneFlagValue' => false, 'existingRecords' => null],
            ],

            // archive is too old
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1,
                        'ts_archived' => Date::factory($minDateProcessed)->subSeconds(1)->getDatetime()],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => false, 'visits' => false, 'visitsConverted' => false, 'archiveExists' => true, 'doneFlagValue' => '1', 'existingRecords' => null],
            ],
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1,
                        'ts_archived' => Date::factory($minDateProcessed)->subSeconds(1)->getDatetime()],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 1,
                        'ts_archived' => Date::factory($minDateProcessed)->subSeconds(1)->getDatetime()],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => false, 'visits' => 1, 'visitsConverted' => false, 'archiveExists' => true, 'doneFlagValue' => '1', 'existingRecords' => null],
            ],

            // no archive done flags, but metric
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 10],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 1],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => false, 'visits' => false, 'visitsConverted' => false, 'archiveExists' => false, 'doneFlagValue' => false, 'existingRecords' => null],
            ],
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 10],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 3],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 5],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => false, 'visits' => false, 'visitsConverted' => false, 'archiveExists' => false, 'doneFlagValue' => false, 'existingRecords' => null],
            ],

            // archive exists and is usable
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => [1], 'visits' => 0, 'visitsConverted' => 0, 'archiveExists' => true, 'doneFlagValue' => '1', 'existingRecords' => null],
            ],
            [
                'day',
                '2019-10-05',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 10],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => [1], 'visits' => 5, 'visitsConverted' => 10, 'archiveExists' => true, 'doneFlagValue' => '1', 'existingRecords' => null],
            ],

            // range archive, valid
            [
                'day',
                '2019-10-05,2019-10-09',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2019-10-05', 'date2' => '2019-10-09', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2019-10-05', 'date2' => '2019-10-09', 'name' => 'nb_visits', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2019-10-05', 'date2' => '2019-10-09', 'name' => 'nb_visits_converted', 'value' => 10],
                ],
                '',
                $minDateProcessed,
                false,
                ['idArchives' => [1], 'visits' => 5, 'visitsConverted' => 10, 'archiveExists' => true, 'doneFlagValue' => '1', 'existingRecords' => null],
            ],

            // range archive, invalid
            [
                'day',
                '2019-10-05,2019-10-09',
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2019-10-05', 'date2' => '2019-10-09', 'name' => 'done', 'value' => 4],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2019-10-05', 'date2' => '2019-10-09', 'name' => 'nb_visits', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2019-10-05', 'date2' => '2019-10-09', 'name' => 'nb_visits_converted', 'value' => 10],
                ],
                '',
                $minDateProcessed,
                false,
                // forcing archiving since invalid + browser archiving of ranges allowed
                ['idArchives' => false, 'visits' => 5, 'visitsConverted' => 10, 'archiveExists' => true, 'doneFlagValue' => '4', 'existingRecords' => null],
            ],
        ];
    }

    /**
     * @dataProvider getTestDataForGetArchiveIdAndVisitsWithOnlyPartialArchives
     */
    public function test_getArchiveIdAndVisits_whenThereAreOnlyPartialArchives($archiveRows, $requestedReports, $expected, $minDatetimeArchiveProcessedUTC = false)
    {
        Fixture::createWebsite('2010-02-02 00:00:00');

        Rules::setBrowserTriggerArchiving(false);
        API::getInstance()->add('test segment', self::TEST_SEGMENT, 0, 0); // processed in real time

        $this->insertArchiveData($archiveRows);

        $params = new \Piwik\ArchiveProcessor\Parameters(new Site(1), Factory::build('range', '2020-03-04,2020-03-08'), new Segment('', [1]));
        $params->setRequestedPlugin('TestPlugin');
        $params->setArchiveOnlyReport($requestedReports);

        $result = ArchiveSelector::getArchiveIdAndVisits($params, $minDatetimeArchiveProcessedUTC);

        if ($result['tsArchived'] !== false) {
            Date::factory($result['tsArchived']);
        }

        unset($result['tsArchived']);

        // remove BC indexed values
        unset($result[0]);
        unset($result[1]);
        unset($result[2]);
        unset($result[3]);
        unset($result[4]);
        unset($result[5]);

        if (isset($result['existingRecords'])) {
            sort($result['existingRecords']);
        }
        if (isset($expected['existingRecords'])) {
            sort($expected['existingRecords']);
        }

        $this->assertEquals($expected, $result);
    }

    public function getTestDataForGetArchiveIdAndVisitsWithOnlyPartialArchives()
    {
        // $archiveRows, $plugin, $requestedReports, $expected
        return [
            // only partial archives, no requested reports
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf', 'is_blob_data' => true],

                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf2', 'is_blob_data' => true],

                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf3', 'is_blob_data' => true],
                ],
                null,
                [
                    'idArchives' => [1],
                    'visits' => false,
                    'visitsConverted' => false,
                    'archiveExists' => true,
                    'doneFlagValue' => false,
                    'existingRecords' => null,
                ],
            ],

            // only partial archives, requested reports, no existing reports
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf', 'is_blob_data' => true],

                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf2', 'is_blob_data' => true],

                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf3', 'is_blob_data' => true],
                ],
                'TestPlugin_otherMetric',
                [
                    'idArchives' => false,
                    'visits' => false,
                    'visitsConverted' => false,
                    'archiveExists' => true,
                    'doneFlagValue' => false,
                    'existingRecords' => null,
                ],
            ],

            // only partial archives, requested reports, some existing reports (both numeric and blob)
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'done.TestPlugin', 'value' => 5, 'ts_archived' => '2020-03-08 03:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_metric', 'value' => 5, 'ts_archived' => '2020-03-08 03:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf 1', 'is_blob_data' => true, 'ts_archived' => '2020-03-08 03:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_metric2', 'value' => 5, 'ts_archived' => '2020-03-08 03:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_blob2', 'value' => 'slkdjf 2', 'is_blob_data' => true, 'ts_archived' => '2020-03-08 03:00:00'],

                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf 3', 'is_blob_data' => true],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric2', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob2', 'value' => 'slkdjf 4', 'is_blob_data' => true],

                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf 5', 'is_blob_data' => true],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric2', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob2', 'value' => 'slkdjf 6', 'is_blob_data' => true],
                ],
                ['TestPlugin_metric', 'TestPlugin_blob'],
                [
                    'idArchives' => [1],
                    'visits' => false,
                    'visitsConverted' => false,
                    'archiveExists' => true,
                    'doneFlagValue' => false,
                    'existingRecords' => ['TestPlugin_metric', 'TestPlugin_blob'],
                ],
                '2020-03-08 00:00:00',
            ],

            // only partial archives, requested reports, some existing reports (both numeric and blob), but archive is too old
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'done.TestPlugin', 'value' => 5, 'ts_archived' => '2020-03-08 03:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_metric', 'value' => 5, 'ts_archived' => '2020-03-08 03:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf 1', 'is_blob_data' => true, 'ts_archived' => '2020-03-08 03:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_metric2', 'value' => 5, 'ts_archived' => '2020-03-08 03:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_blob2', 'value' => 'slkdjf 2', 'is_blob_data' => true, 'ts_archived' => '2020-03-08 03:00:00'],

                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf 3', 'is_blob_data' => true],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric2', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob2', 'value' => 'slkdjf 4', 'is_blob_data' => true],

                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf 5', 'is_blob_data' => true],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric2', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob2', 'value' => 'slkdjf 6', 'is_blob_data' => true],
                ],
                ['TestPlugin_metric', 'TestPlugin_blob'],
                [
                    'idArchives' => false,
                    'visits' => false,
                    'visitsConverted' => false,
                    'archiveExists' => true,
                    'doneFlagValue' => false,
                    'existingRecords' => null,
                ],
                '2020-03-08 09:00:00',
            ],

            // only partial archives, requested reports, all existing reports (both numeric and blob)
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf', 'is_blob_data' => true],

                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf2', 'is_blob_data' => true],

                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf3', 'is_blob_data' => true],
                ],
                ['TestPlugin_metric', 'TestPlugin_blob'],
                [
                    'idArchives' => [1],
                    'visits' => false,
                    'visitsConverted' => false,
                    'archiveExists' => true,
                    'doneFlagValue' => false,
                    'existingRecords' => ['TestPlugin_metric', 'TestPlugin_blob'],
                ],
            ],

            // only partial archives, requested reports, some existing reports (both numeric and blob) across multiple partial archives
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_metric', 'value' => 5],

                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf 1', 'is_blob_data' => true],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_metric2', 'value' => 5],

                    ['idarchive' => 3, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-08', 'name' => 'TestPlugin_blob2', 'value' => 'slkdjf 2', 'is_blob_data' => true],

                    ['idarchive' => 4, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 4, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric', 'value' => 5],

                    ['idarchive' => 5, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 5, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf 3', 'is_blob_data' => true],

                    ['idarchive' => 6, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 6, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric2', 'value' => 5],
                    ['idarchive' => 6, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob2', 'value' => 'slkdjf 4', 'is_blob_data' => true],

                    ['idarchive' => 7, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 7, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_metric', 'value' => 5],
                    ['idarchive' => 7, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'TestPlugin_blob', 'value' => 'slkdjf 5', 'is_blob_data' => true],

                    ['idarchive' => 8, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 8, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_metric2', 'value' => 5],

                    ['idarchive' => 9, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'done.TestPlugin', 'value' => 5],
                    ['idarchive' => 9, 'idsite' => 1, 'period' => 5, 'date1' => '2020-03-03', 'date2' => '2020-03-09', 'name' => 'TestPlugin_blob2', 'value' => 'slkdjf 6', 'is_blob_data' => true],
                ],
                ['TestPlugin_metric', 'TestPlugin_blob', 'TestPlugin_blob5'],
                [
                    'idArchives' => [3, 2, 1],
                    'visits' => false,
                    'visitsConverted' => false,
                    'archiveExists' => true,
                    'doneFlagValue' => false,
                    'existingRecords' => ['TestPlugin_metric', 'TestPlugin_blob'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getTestDataForGetArchiveData
     */
    public function test_getArchiveData_returnsCorrectData(
        $archiveRows,
        $dataType,
        $idArchives,
        $recordNames,
        $idSubtable,
        $expectedData
    ) {
        Fixture::createWebsite('2010-02-02 00:00:00');

        $this->insertArchiveData($archiveRows);

        $data = ArchiveSelector::getArchiveData($idArchives, $recordNames, $dataType, $idSubtable);

        $this->assertEquals($expectedData, $data);
    }

    public function getTestDataForGetArchiveData()
    {
        // $blobArray1
        $blobArray1 = [
            1 => 'blobvalue1',
            2 => 'blobvalue2',
            3 => 'blobvalue3',
        ];
        $blobArray2 = [
            1 => 'blobvalue4',
            2 => 'blobvalue5',
            3 => 'blobvalue6',
        ];
        $blobArray3 = [
            1 => 'blobvalue7',
            2 => 'blobvalue8',
            4 => 'blobvalue9',
        ];
        $blobArray4 = [
            1 => 'blobvalue10',
            2 => 'blobvalue11',
            3 => 'blobvalue12',
        ];

        return [
            // numeric data
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 5, 'ts_archived' => '2020-06-13 09:04:56'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 10, 'ts_archived' => '2020-06-12 02:04:56'],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 15, 'ts_archived' => '2020-06-13 04:04:56'],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 20, 'ts_archived' => '2020-06-13 04:04:56'],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 30, 'ts_archived' => '2020-06-13 04:04:56'],
                ],
                'numeric',
                [
                    '2019-10-05,2019-10-05' => [1,2,3],
                ],
                ['nb_visits', 'nb_visits_converted'],
                null,
                array (
                    array (
                        'value' => '10',
                        'name' => 'nb_visits_converted',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-12 02:04:56',
                    ),
                    array (
                        'value' => '15',
                        'name' => 'nb_visits',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 04:04:56',
                    ),
                    array (
                        'value' => '20',
                        'name' => 'nb_visits_converted',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 04:04:56',
                    ),
                    array (
                        'value' => '30',
                        'name' => 'nb_visits',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 04:04:56',
                    ),
                    array (
                        'value' => '5',
                        'name' => 'nb_visits',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 09:04:56',
                    ),
                ),
            ],

            // blob data
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'blob1', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'blob2', 'value' => 'klm', 'ts_archived' => '2020-06-12 02:04:56', 'is_blob_data' => true],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'blob1', 'value' => 'hij', 'ts_archived' => '2020-06-13 04:04:56', 'is_blob_data' => true],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'blob2', 'value' => 'ghi', 'ts_archived' => '2020-06-13 04:04:56', 'is_blob_data' => true],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'blob2', 'value' => 'abcd', 'ts_archived' => '2020-06-13 04:04:56', 'is_blob_data' => true],
                    ['idarchive' => 4, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'blob2', 'value' => 'abcd', 'ts_archived' => '2020-08-13 04:04:56', 'is_blob_data' => true],
                ],
                'blob',
                [
                    '2019-10-05,2019-10-05' => [1,2,3],
                ],
                ['blob1', 'blob2'],
                null,
                array (
                    array (
                        'value' => 'klm',
                        'name' => 'blob2',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-12 02:04:56',
                    ),
                    array (
                        'value' => 'hij',
                        'name' => 'blob1',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 04:04:56',
                    ),
                    array (
                        'value' => 'ghi',
                        'name' => 'blob2',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 04:04:56',
                    ),
                    array (
                        'value' => 'abcd',
                        'name' => 'blob2',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 04:04:56',
                    ),
                    array (
                        'value' => 'nop',
                        'name' => 'blob1',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 09:04:56',
                    ),
                ),
            ],

            // blub data w/ subtable
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'blob1_chunk_0_99', 'value' => serialize($blobArray1), 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'blob2_chunk_0_99', 'value' => serialize($blobArray2), 'ts_archived' => '2020-06-12 02:04:56', 'is_blob_data' => true],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'blob1_chunk_0_99', 'value' => serialize($blobArray3), 'ts_archived' => '2020-06-13 04:04:56', 'is_blob_data' => true],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'blob2_chunk_0_99', 'value' => serialize($blobArray4), 'ts_archived' => '2020-06-13 04:04:56', 'is_blob_data' => true],
                ],
                'blob',
                [
                    '2019-10-05,2019-10-05' => [1,2,3],
                ],
                ['blob1', 'blob2'],
                2,
                array (
                    array (
                        'value' => 'blobvalue5',
                        'name' => 'blob2_2',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-12 02:04:56',
                    ),
                    array (
                        'value' => 'blobvalue8',
                        'name' => 'blob1_2',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 04:04:56',
                    ),
                    array (
                        'value' => 'blobvalue11',
                        'name' => 'blob2_2',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 04:04:56',
                    ),
                    array (
                        'value' => 'blobvalue2',
                        'name' => 'blob1_2',
                        'idsite' => '1',
                        'date1' => '2019-10-05',
                        'date2' => '2019-10-05',
                        'ts_archived' => '2020-06-13 09:04:56',
                    ),
                ),
            ],
        ];
    }

    /**
     * @dataProvider getTestDataForGetExtractIdSubtableFromBlobNameSql
     */
    public function test_getExtractIdSubtableFromBlobNameSql_correctlyExtractsStartSubtableIdFromBlobNames($archiveRows, $blobName, $expectedRows)
    {
        $this->insertArchiveData($archiveRows);

        $sql = 'SELECT ' . ArchiveSelector::getExtractIdSubtableFromBlobNameSql(new Chunk(), $blobName) . ' AS idsubtable, name'
            . ' FROM ' . ArchiveTableCreator::getBlobTable(Date::factory($archiveRows[0]['date1']))
            . ' WHERE name = ? OR name LIKE ? ORDER BY idsubtable ASC';
        $rows = Db::fetchAll($sql, [$blobName, $blobName . '%']);

        $this->assertEquals($expectedRows, $rows);
    }

    public function getTestDataForGetExtractIdSubtableFromBlobNameSql()
    {
        return [
            // just root table blob
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Events_action_name', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                ],
                'Events_action_name',
                [
                    ['idsubtable' => -1, 'name' => 'Events_action_name'],
                ],
            ],

            // root table w/ chunked subtables
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_chunk_125_200', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_chunk_100_124', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_chunk_0_99', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                ],
                'Actions_actions_url',
                [
                    ['idsubtable' => -1, 'name' => 'Actions_actions_url'],
                    ['idsubtable' => 0, 'name' => 'Actions_actions_url_chunk_0_99'],
                    ['idsubtable' => 100, 'name' => 'Actions_actions_url_chunk_100_124'],
                    ['idsubtable' => 125, 'name' => 'Actions_actions_url_chunk_125_200'],
                ],
            ],

            // root table w/ normal subtables
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_1', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_5', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_3', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_002', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                ],
                'Actions_actions_url',
                [
                    ['idsubtable' => -1, 'name' => 'Actions_actions_url'],
                    ['idsubtable' => 1, 'name' => 'Actions_actions_url_1'],
                    ['idsubtable' => 2, 'name' => 'Actions_actions_url_002'],
                    ['idsubtable' => 3, 'name' => 'Actions_actions_url_3'],
                    ['idsubtable' => 5, 'name' => 'Actions_actions_url_5'],
                ],
            ],

            // root table w/ chunked subtables and normal subtables
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_chunk_52_100', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_chunk_03_50', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_2', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_51', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url_chunk_0_1', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'Actions_actions_url', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                ],
                'Actions_actions_url',
                [
                    ['idsubtable' => -1, 'name' => 'Actions_actions_url'],
                    ['idsubtable' => 0, 'name' => 'Actions_actions_url_chunk_0_1'],
                    ['idsubtable' => 2, 'name' => 'Actions_actions_url_2'],
                    ['idsubtable' => 3, 'name' => 'Actions_actions_url_chunk_03_50'],
                    ['idsubtable' => 51, 'name' => 'Actions_actions_url_51'],
                    ['idsubtable' => 52, 'name' => 'Actions_actions_url_chunk_52_100'],
                ],
            ],

            // entity root table w/ chunked subtables and normal subtables
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'MyPlugin_myReport_1_chunk_52_100', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'MyPlugin_myReport_1_chunk_03_50', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'MyPlugin_myReport_1_2', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'MyPlugin_myReport_1_51', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'MyPlugin_myReport_1_chunk_0_1', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'MyPlugin_myReport_1', 'value' => 'nop', 'ts_archived' => '2020-06-13 09:04:56', 'is_blob_data' => true],
                ],
                'MyPlugin_myReport_1',
                [
                    ['idsubtable' => -1, 'name' => 'MyPlugin_myReport_1'],
                    ['idsubtable' => 0, 'name' => 'MyPlugin_myReport_1_chunk_0_1'],
                    ['idsubtable' => 2, 'name' => 'MyPlugin_myReport_1_2'],
                    ['idsubtable' => 3, 'name' => 'MyPlugin_myReport_1_chunk_03_50'],
                    ['idsubtable' => 51, 'name' => 'MyPlugin_myReport_1_51'],
                    ['idsubtable' => 52, 'name' => 'MyPlugin_myReport_1_chunk_52_100'],
                ],
            ],
        ];
    }
}
