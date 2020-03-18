<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\DataAccess;


use Piwik\ArchiveProcessor\Parameters;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period\Factory;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ArchiveSelectorTest extends IntegrationTestCase
{
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    /**
     * @dataProvider getTestDataForGetArchiveIdAndVisits
     */
    public function test_getArchiveIdAndVisits_returnsCorrectResult($archiveRows, $segment, $minDateProcessed, $includeInvalidated, $expected)
    {
        Fixture::createWebsite('2010-02-02 00:00:00');

        $this->insertArchiveData($archiveRows);

        $params = new Parameters(new Site(1), Factory::build('day', '2019-10-05'), new Segment($segment, [1]));
        $result = ArchiveSelector::getArchiveIdAndVisits($params, $minDateProcessed, $includeInvalidated);
        $this->assertEquals($expected, $result);
    }

    private function insertArchiveData($archiveRows)
    {
        $table = ArchiveTableCreator::getNumericTable(Date::factory('2019-10-01 12:13:14'));
        foreach ($archiveRows as $row) {
            $tsArchived = isset($row['ts_archived']) ? $row['ts_archived'] : Date::now()->getDatetime();
            Db::query("INSERT INTO `$table` (idarchive, idsite, period, date1, date2, `name`, `value`, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$row['idarchive'], $row['idsite'], $row['period'], $row['date1'], $row['date2'], $row['name'], $row['value'], $tsArchived]);
        }
    }

    public function getTestDataForGetArchiveIdAndVisits()
    {
        $minDateProcessed = Date::factory('2020-03-04 00:00:00')->subSeconds(900)->getDatetime();
        return [
            // no archive data found
            [ // nothing in the db
                [],
                '',
                $minDateProcessed,
                true,
                [false, false, false, false],
            ],
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-06', 'date2' => '2019-10-06', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 3, 'idsite' => 2, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1],
                ],
                '',
                $minDateProcessed,
                true,
                [false, false, false, false],
            ],

            // value is not valid
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 4],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 2],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 3],
                    ['idarchive' => 4, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 99],
                ],
                '',
                $minDateProcessed,
                false,
                [false, false, false, true],
            ],
            [
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
                [false, 0, 0, true],
            ],
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done.VisitsSummary', 'value' => 4],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 20],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 40],
                ],
                '',
                $minDateProcessed,
                false,
                [false, 20, 40, true],
            ],
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done.VisitsSummary', 'value' => 1],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 30],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 50],
                ],
                '',
                $minDateProcessed,
                false,
                [false, 30, 50, true],
            ],
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done.VisitsSummary', 'value' => 1],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 30],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 50],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done.VisitsSummary', 'value' => 4],
                ],
                '',
                $minDateProcessed,
                false,
                [false, 0, 0, true],
            ],

            // archive is too old
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1,
                        'ts_archived' => Date::factory($minDateProcessed)->subSeconds(1)->getDatetime()],
                ],
                '',
                $minDateProcessed,
                false,
                [false, false, false, true],
            ],
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1,
                        'ts_archived' => Date::factory($minDateProcessed)->subSeconds(1)->getDatetime()],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 1,
                        'ts_archived' => Date::factory($minDateProcessed)->subSeconds(1)->getDatetime()],
                ],
                '',
                $minDateProcessed,
                false,
                [false, 1, false, true],
            ],

            // no archive done flags, but metric
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 10],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 1],
                ],
                '',
                $minDateProcessed,
                false,
                [false, false, false, true],
            ],
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 10],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 3],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 5],
                ],
                '',
                $minDateProcessed,
                false,
                [false, false, false, true],
            ],

            // archive exists and is usable
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1],
                ],
                '',
                $minDateProcessed,
                false,
                [1, false, false, true],
            ],
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits', 'value' => 5],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2019-10-05', 'date2' => '2019-10-05', 'name' => 'nb_visits_converted', 'value' => 10],
                ],
                '',
                $minDateProcessed,
                false,
                [1, 5, 10, true],
            ],
        ];
    }
}