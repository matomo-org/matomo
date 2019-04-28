<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\DataAccess;


use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ArchiveSelectorTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        Fixture::createWebsite('2012-01-01 00:00:00');
    }

    /**
     * @dataProvider getTestDataForGetLatestArchiveStartTimestampForToday
     */
    public function test_getLatestArchiveStartTimestampForToday_SelectsCorrectTsArchived($idSite, $archiveRows, $expectedValue)
    {
        Date::$now = strtotime('2012-03-04 06:42:34');

        foreach ($archiveRows as $row) {
            list($idArchive, $date1, $date2, $period, $name, $value, $tsArchived) = $row;
            $this->insertArchiveRow($idArchive, $idSite, $date1, $date2, $period, $name, $value, $tsArchived);
        }

        $timestamp = ArchiveSelector::getLatestArchiveStartTimestampForToday($idSite);
        $this->assertEquals($expectedValue, $timestamp);
    }

    public function getTestDataForGetLatestArchiveStartTimestampForToday()
    {
        return [
            [1, [], false],

            [
                1,
                [
                    [1, '2012-03-04', '2012-03-04', Piwik::$idPeriods['day'], 'start', 1, '2012-03-04 03:45:56'],
                    [1, '2012-03-04', '2012-03-04', Piwik::$idPeriods['day'], 'done', 1, '2012-03-04 06:45:56'],
                    [1, '2012-03-04', '2012-03-04', Piwik::$idPeriods['day'], 'nb_visits', 1, '2012-03-04 03:45:56'],
                ],
                '2012-03-04 03:45:56',
            ],

            // no archives for the day
            [
                1,
                [
                    [1, '2012-03-05', '2012-03-05', Piwik::$idPeriods['day'], 'start', 1, '2012-03-04 03:45:56'],
                    [1, '2012-03-05', '2012-03-05', Piwik::$idPeriods['day'], 'done', 1, '2012-03-04 06:45:56'],
                    [1, '2012-03-05', '2012-03-05', Piwik::$idPeriods['day'], 'nb_visits', 1, '2012-03-04 03:45:56'],

                    [2, '2012-03-05', '2012-03-05', Piwik::$idPeriods['range'], 'start', 1, '2012-03-04 03:45:56'],
                    [2, '2012-03-05', '2012-03-05', Piwik::$idPeriods['range'], 'done', 1, '2012-03-04 06:45:56'],

                    [3, '2012-03-01', '2012-03-31', Piwik::$idPeriods['month'], 'start', 1, '2012-03-04 03:45:56'],
                    [3, '2012-03-01', '2012-03-31', Piwik::$idPeriods['month'], 'done', 1, '2012-03-04 06:45:56'],
                ],
                false,
            ],

            // no start archive
            [
                1,
                [
                    [1, '2012-03-04', '2012-03-04', Piwik::$idPeriods['day'], 'done', 1, '2012-03-04 06:45:56'],
                    [1, '2012-03-04', '2012-03-04', Piwik::$idPeriods['day'], 'nb_visits', 1, '2012-03-04 03:45:56'],
                ],
                false,
            ],
        ];
    }

    private function insertArchiveRow($idArchive, $idSite, $date1, $date2, $period, $name, $value, $tsArchived)
    {
        $table = ArchiveTableCreator::getNumericTable(Date::factory($date1));
        $sql = "INSERT INTO $table (idarchive, idsite, date1, date2, period, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $bind = [$idArchive, $idSite, $date1, $date2, $period, $name, $value, $tsArchived];

        Db::query($sql, $bind);
    }
}