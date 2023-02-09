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
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Actions\API as ActionsApi;

/**
 * @group PartialArchiveTest
 * @group Core
 */
class PartialArchiveTest extends IntegrationTestCase
{
    private static $chrome = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36';
    private static $dateTime = '2020-04-07 10:03:04';

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        self::createWebsite();
        self::trackVisit();
    }

    public function test_rangeArchiving_onlyArchivesSingleRecord()
    {
        // first trigger all plugins archiving
        $_GET['trigger'] = 'archivephp';
        ActionsApi::getInstance()->getPageUrls(1, 'day', '2020-04-07'); // day first
        $data = ActionsApi::getInstance()->getPageUrls(1, 'range', '2020-04-06,2020-04-09', false, false, false, false, true);
        $this->assertEquals([
            '/path',
        ], $data->getColumn('label'));

        // check archive is all plugins archive as expected
        [$idArchives, $archiveInfo] = $this->getArchiveInfo('2020_04', 5, false);
        $this->assertEquals([
            ['idsite' => 1, 'date1' => '2020-04-06', 'date2' => '2020-04-09', 'period' => 5, 'name' => 'done', 'value' => 1, 'blob_count' => 55],
        ], $archiveInfo);

        $maxIdArchive = $this->getMaxIdArchive('2020_04');

        self::trackAnotherVisit();

        // trigger browser archiving for range
        ActionsApi::getInstance()->getPageUrls(1, 'day', '2020-04-08', false, false, false, false, true); // first day
        unset($_GET['trigger']);
        StaticContainer::get(ArchiveInvalidator::class)->markArchivesAsInvalidated([1], ['2020-04-06,2020-04-09'], 'range');
        $data = ActionsApi::getInstance()->getPageUrls(1, 'range', '2020-04-06,2020-04-09', false, false, false, false, true);
        $this->assertEquals([
            '/path',
            '/path2',
        ], $data->getColumn('label'));

        [$idArchives, $archiveInfo] = $this->getArchiveInfo('2020_04', 5, false, $maxIdArchive);

        $this->assertEquals([
            // expect only one blob for new range partial archive
            ['idsite' => 1, 'date1' => '2020-04-06', 'date2' => '2020-04-09', 'period' => 5, 'name' => 'done.Actions', 'value' => 5, 'blob_count' => 1],
        ], $archiveInfo);

        $archiveNames = $this->getArchiveNames('2020_04', $idArchives[0]);
        $this->assertEquals(['Actions_actions_url'], $archiveNames);
    }

    public function test_customSegmentArchiving_onlyArchivesSingleRecord_whenRequestIsForNonDayPeriod()
    {
        $segment = new Segment('browserCode==ff', [1]);
        $segmentHash = $segment->getHash();

        self::trackNonFirefoxVisit();

        // first trigger all plugins archiving
        $_GET['trigger'] = 'archivephp';
        $data = ActionsApi::getInstance()->getPageUrls(1, 'week', '2020-04-07', 'browserCode==ff');
        $this->assertEquals([
            '/path',
        ], $data->getColumn('label'));

        // check archive is all plugins archive as expected
        [$idArchives, $archiveInfo] = $this->getArchiveInfo('2020_04', 2, $segmentHash);
        $this->assertEquals([
            ['idsite' => 1, 'date1' => '2020-04-06', 'date2' => '2020-04-12', 'period' => 2, 'name' => 'donedc3974063a947330c5c32505708acea5.VisitsSummary', 'value' => 1, 'blob_count' => 0],
            ['idsite' => 1, 'date1' => '2020-04-06', 'date2' => '2020-04-12', 'period' => 2, 'name' => 'donedc3974063a947330c5c32505708acea5.Actions', 'value' => 1, 'blob_count' => 6],
        ], $archiveInfo);

        $maxIdArchive = $this->getMaxIdArchive('2020_04');

        self::trackAnotherVisit();

        // trigger browser archiving for range
        ActionsApi::getInstance()->getPageUrls(1, 'day', '2020-04-08', 'browserCode==ff', false, false, false, true); // first day
        unset($_GET['trigger']);
        StaticContainer::get(ArchiveInvalidator::class)->markArchivesAsInvalidated([1], ['2020-04-06,2020-04-09'], 'range', $segment);
        $data = ActionsApi::getInstance()->getPageUrls(1, 'week', '2020-04-07', 'browserCode==ff');
        $this->assertEquals([
            '/path',
            '/path2',
        ], $data->getColumn('label'));

        [$idArchives, $archiveInfo] = $this->getArchiveInfo('2020_04', 2, $segmentHash, $maxIdArchive);

        $this->assertEquals([
            ['idsite' => 1, 'date1' => '2020-04-06', 'date2' => '2020-04-12', 'period' => 2, 'name' => 'donedc3974063a947330c5c32505708acea5.Actions', 'value' => 5, 'blob_count' => 1],
        ], $archiveInfo);

        $archiveNames = $this->getArchiveNames('2020_04', $idArchives[0]);
        $this->assertEquals(['Actions_actions_url'], $archiveNames);
    }

    private static function createWebsite()
    {
        Fixture::createWebsite('2018-05-05 09:00:00');
    }

    private static function trackVisit()
    {
        $t = Fixture::getTracker(1, self::$dateTime);
        $t->setUrl('http://site.com/path');
        Fixture::checkResponse($t->doTrackPageView('page title'));
    }

    private static function trackNonFirefoxVisit()
    {
        $t = Fixture::getTracker(1, self::$dateTime);
        $t->setUserAgent(self::$chrome);
        $t->setUrl('http://site.com/path3');
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

    private function getArchiveNames($yearMonth, $idArchive)
    {
        $sql = 'SELECT DISTINCT name FROM ' . Common::prefixTable('archive_blob_' . $yearMonth)
            . ' WHERE idarchive = ?';
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