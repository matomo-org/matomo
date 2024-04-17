<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Archive;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Metrics;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Goals\API as GoalsApi;

/**
 * @group PartialArchiveTest
 * @group Core
 */
class PartialArchiveTest extends IntegrationTestCase
{
    private static $dateTime = '2020-04-07 10:03:04';
    private $idGoal = 1;

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        self::createWebsite();
        self::trackVisit();
    }

    public function test_rangeArchiving_onlyArchivesSingleRecord_whenQueryingNumerics()
    {
        // first trigger all plugins archiving
        $_GET['trigger'] = 'archivephp';
        GoalsApi::getInstance()->get(1, 'day', '2020-04-07', false, $this->idGoal); // day first
        $data = GoalsApi::getInstance()->get(1, 'range', '2020-04-06,2020-04-09', false, $this->idGoal);
        $this->assertEquals([
            'nb_conversions' => 1,
            'nb_visits_converted' => 1,
            'revenue' => 0,
            'conversion_rate' => 'Intl_NumberFormatPercent',
            'nb_conversions_new_visit' => 1,
            'nb_visits_converted_new_visit' => 1,
            'revenue_new_visit' => 0,
            'conversion_rate_new_visit' => 'Intl_NumberFormatPercent',
            'nb_conversions_returning_visit' => 0,
            'nb_visits_converted_returning_visit' => 0,
            'revenue_returning_visit' => 0,
            'conversion_rate_returning_visit' => 'Intl_NumberFormatPercent',
        ], $data->getFirstRow()->getColumns());

        // check archive is all plugins archive as expected
        [$idArchives, $archiveInfo] = $this->getArchiveInfo('2020_04', 5, false);
        $this->assertEquals([
            ['idsite' => 1, 'date1' => '2020-04-06', 'date2' => '2020-04-09', 'period' => 5, 'name' => 'done', 'value' => 1, 'blob_count' => 58],
        ], $archiveInfo);

        $maxIdArchive = $this->getMaxIdArchive('2020_04');

        self::trackAnotherVisit();

        // trigger browser archiving for range
        GoalsApi::getInstance()->get(1, 'day', '2020-04-08', false, $this->idGoal); // day first
        unset($_GET['trigger']);
        StaticContainer::get(ArchiveInvalidator::class)->markArchivesAsInvalidated([1], ['2020-04-06,2020-04-09'], 'range');
        $data = GoalsApi::getInstance()->get(1, 'range', '2020-04-06,2020-04-09', false, $this->idGoal);
        $this->assertEquals([
            'nb_conversions' => 2,
            'nb_visits_converted' => 2,
            'revenue' => 0,
            'conversion_rate' => 'Intl_NumberFormatPercent',
            'nb_conversions_new_visit' => 2,
            'nb_visits_converted_new_visit' => 2,
            'revenue_new_visit' => 0,
            'conversion_rate_new_visit' => 'Intl_NumberFormatPercent',
            'nb_conversions_returning_visit' => 0,
            'nb_visits_converted_returning_visit' => 0,
            'revenue_returning_visit' => 0,
            'conversion_rate_returning_visit' => 'Intl_NumberFormatPercent',
        ], $data->getFirstRow()->getColumns());

        [$idArchives, $archiveInfo] = $this->getArchiveInfo('2020_04', 5, false, $maxIdArchive);

        $archiveNames = $this->getArchiveNames('2020_04', $idArchives[0], 'numeric');
        $this->assertEquals([
            'Goal_1_nb_conversions',
            'Goal_1_nb_visits_converted',
        ], $archiveNames);

        $blobArchiveNames = $this->getArchiveNames('2020_04', $idArchives[0]);
        $this->assertEquals([], $blobArchiveNames);

        $this->assertEquals([
            // expect only one blob for new range partial archive
            ['idsite' => 1, 'date1' => '2020-04-06', 'date2' => '2020-04-09', 'period' => 5, 'name' => 'done.Goals', 'value' => 5, 'blob_count' => 0],
            ['idsite' => 1, 'date1' => '2020-04-06', 'date2' => '2020-04-09', 'period' => 5, 'name' => 'done.VisitsSummary', 'value' => 1, 'blob_count' => 0],
        ], $archiveInfo);
    }

    public function test_rangeArchiving_onlyArchivesSingleRecord_whenQueryingBlobs()
    {
        // first trigger all plugins archiving
        $_GET['trigger'] = 'archivephp';
        GoalsApi::getInstance()->getDaysToConversion(1, 'day', '2020-04-07', false, $this->idGoal); // day first
        $data = GoalsApi::getInstance()->getDaysToConversion(1, 'range', '2020-04-06,2020-04-09', false, $this->idGoal);
        $this->assertEquals(['label' => '0-0', Metrics::INDEX_NB_CONVERSIONS => 1], $data->getFirstRow()->getColumns());

        // check archive is all plugins archive as expected
        [$idArchives, $archiveInfo] = $this->getArchiveInfo('2020_04', 5, false);
        $this->assertEquals([
            ['idsite' => 1, 'date1' => '2020-04-06', 'date2' => '2020-04-09', 'period' => 5, 'name' => 'done', 'value' => 1, 'blob_count' => 58],
        ], $archiveInfo);

        $maxIdArchive = $this->getMaxIdArchive('2020_04');

        self::trackAnotherVisit();

        // trigger browser archiving for range
        GoalsApi::getInstance()->getDaysToConversion(1, 'day', '2020-04-08', false, $this->idGoal); // first day
        unset($_GET['trigger']);
        StaticContainer::get(ArchiveInvalidator::class)->markArchivesAsInvalidated([1], ['2020-04-06,2020-04-09'], 'range');
        $data = GoalsApi::getInstance()->getDaysToConversion(1, 'range', '2020-04-06,2020-04-09', false, $this->idGoal);
        $this->assertEquals(['label' => '0-0', Metrics::INDEX_NB_CONVERSIONS => 2], $data->getFirstRow()->getColumns());

        [$idArchives, $archiveInfo] = $this->getArchiveInfo('2020_04', 5, false, $maxIdArchive);

        $archiveNames = $this->getArchiveNames('2020_04', $idArchives[0]);
        $this->assertEquals(['Goal_1_days_until_conv'], $archiveNames);

        $this->assertEquals([
            // expect only one blob for new range partial archive
            ['idsite' => 1, 'date1' => '2020-04-06', 'date2' => '2020-04-09', 'period' => 5, 'name' => 'done.Goals', 'value' => 5, 'blob_count' => 1],
        ], $archiveInfo);
    }

    private static function createWebsite()
    {
        Fixture::createWebsite('2018-05-05 09:00:00');
        GoalsApi::getInstance()->addGoal(1, 'test goal', 'url', 'http', 'contains');
    }

    private static function trackVisit()
    {
        $t = Fixture::getTracker(1, self::$dateTime);
        $t->setUrl('http://site.com/path');
        Fixture::checkResponse($t->doTrackPageView('page title'));
    }

    private static function trackAnotherVisit()
    {
        $t = Fixture::getTracker(1, Date::factory(self::$dateTime)->addDay(1)->getDatetime());
        $t->setUrl('http://site.com/path2');
        Fixture::checkResponse($t->doTrackPageView('page title 2'));
    }

    private function getArchiveInfo($yearMonth, $period, $segmentHash = false, $idArchiveGreaterThan = 0)
    {
        $sql = 'SELECT idarchive, idsite, date1, date2, period, name, value FROM '
            . Common::prefixTable('archive_numeric_' . $yearMonth)
            . ' WHERE (name = \'done' . $segmentHash . '\' OR name LIKE \'done' . $segmentHash . '.%\') AND period = ? AND idarchive > ?';
        $archiveNumericInfo = Db::fetchAll($sql, [$period, $idArchiveGreaterThan]);

        $sql = 'SELECT idarchive, COUNT(DISTINCT name) AS blob_count FROM ' . Common::prefixTable('archive_blob_' . $yearMonth)
            . ' GROUP BY idarchive';
        $archiveBlobInfo = Db::fetchAll($sql);
        $archiveBlobInfo = array_column($archiveBlobInfo, 'blob_count', 'idarchive');

        $idArchives = [];
        foreach ($archiveNumericInfo as &$row) {
            $row['blob_count'] = $archiveBlobInfo[$row['idarchive']] ?? 0;

            // archives can randomly be created out of order despite not using core:archive, so we don't check their
            // value. we still need to use it, though, so we return the values.
            $idArchives[] = $row['idarchive'];
            unset($row['idarchive']);
        }

        return [$idArchives, $archiveNumericInfo];
    }

    private function getArchiveNames($yearMonth, $idArchive, $type = 'blob')
    {
        $sql = 'SELECT DISTINCT name FROM ' . Common::prefixTable('archive_' . $type . '_' . $yearMonth)
            . ' WHERE idarchive = ? AND name NOT LIKE \'done%\'';
        $rows = Db::fetchAll($sql, [$idArchive]);
        $rows = array_column($rows, 'name');
        return $rows;
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    private function getMaxIdArchive($yearMonth)
    {
        return Db::fetchOne('SELECT MAX(idarchive) FROM ' . Common::prefixTable('archive_numeric_' . $yearMonth));
    }
}
