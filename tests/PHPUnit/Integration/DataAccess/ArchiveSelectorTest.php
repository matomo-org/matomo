<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\DataAccess;


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
 */
class ArchiveSelectorTest extends IntegrationTestCase
{
    const TEST_SEGMENT = 'operatingSystemCode==WIN';

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

        $archiveIds = ArchiveSelector::getArchiveIds([1], [Factory::build('day', '2020-03-01')], new Segment('', [1]), ['Funnels'],
            true, true);

        $expected = [
            'done.Funnels' => [
                '2020-03-01,2020-03-01' => [
                    '16',
                ],
            ],
        ];
        $this->assertEquals($expected, $archiveIds);
    }

    /**
     * @dataProvider getTestDataForGetArchiveIds
     */
    public function test_getArchiveIds_returnsCorrectResult($archiveRows, $siteIds, $periods, $segment, $plugins, $expected)
    {
        Fixture::createWebsite('2010-02-02 00:00:00');
        Fixture::createWebsite('2010-02-02 00:00:00');

        foreach ($periods as $index => [$periodStr, $dateStr]) {
            $periods[$index] = Factory::build($periodStr, $dateStr);
        }

        $this->insertArchiveData($archiveRows);

        $archiveIds = ArchiveSelector::getArchiveIds($siteIds, $periods, new Segment($segment, $siteIds), $plugins);

        $this->assertEquals($expected, $archiveIds);
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
                        '2020-03-01,2020-03-01' => [1],
                        '2020-03-02,2020-03-08' => [2],
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
                        '2020-03-01,2020-03-01' => [10,4],
                    ],
                    'done.Funnels' => [
                        '2020-03-01,2020-03-01' => [7,6,5],
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
            Db::query("INSERT INTO `$table` (idarchive, idsite, period, date1, date2, `name`, `value`, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$row['idarchive'], $row['idsite'], $row['period'], $row['date1'], $row['date2'], $row['name'], $row['value'], $tsArchived]);
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

        if ($result[4] !== false) {
            Date::factory($result[4]);
        }

        unset($result[4]);
        $result = array_values($result);

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
                [false, false, false, false, false],
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
                [false, false, false, false, false],
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
                [false, false, false, true, '99'],
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
                [false, 0, 0, true, '99'],
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
                [false, 20, 40, true, false],
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
                [false, 30, 50, true, false],
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
                [false, false, false, true, false],
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
                [false, false, false, true, '1'],
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
                [false, 1, false, true, '1'],
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
                [false, false, false, false, false],
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
                [false, false, false, false, false],
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
                [[1], 0, 0, true, '1'],
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
                [[1], 5, 10, true, '1'],
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
                [[1], 5, 10, true, '1'],
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
                [false, 5, 10, true, '4'], // forcing archiving since invalid + browser archiving of ranges allowed
            ],
        ];
    }

    /**
     * @dataProvider getTestDataForGetArchiveData
     */
    public function test_getArchiveData_returnsCorrectData($archiveRows, $dataType, $idArchives, $recordNames, $idSubtable,
                                                           $expectedData)
    {
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
}