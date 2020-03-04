<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\ArchiveProcessor;


use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Loader;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period\Factory;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class LoaderTest extends IntegrationTestCase
{
    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createWebsite('2012-02-03 00:00:00');
    }

    public function test_loadExistingArchiveIdFromDb_returnsFalsesIfNoArchiveFound()
    {
        $params = new Parameters(new Site(1), Factory::build('day', '2015-03-03'), new Segment('', [1]));
        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        $this->assertEquals([false, false, false, false], $archiveInfo);
    }

    /**
     * @dataProvider getTestDataForLoadExistingArchiveIdFromDbDebugConfig
     */
    public function test_loadExistingArchiveIdFromDb_returnsFalsesPeriodIsForcedToArchive($periodType, $configSetting)
    {
        $date = $periodType == 'range' ? '2015-03-03,2015-03-04' : '2015-03-03';
        $params = new Parameters(new Site(1), Factory::build($periodType, $date), new Segment('', [1]));
        $this->insertArchive($params);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();
        $this->assertNotEquals([false, false, false, false], $archiveInfo);

        Config::getInstance()->Debug[$configSetting] = 1;

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();
        $this->assertEquals([false, false, false, false], $archiveInfo);
    }

    public function getTestDataForLoadExistingArchiveIdFromDbDebugConfig()
    {
        return [
            ['day', 'always_archive_data_day'],
            ['week', 'always_archive_data_period'],
            ['month', 'always_archive_data_period'],
            ['year', 'always_archive_data_period'],
            ['range', 'always_archive_data_range'],
        ];
    }

    public function test_loadExistingArchiveIdFromDb_returnsArchiveIfArchiveInThePast()
    {
        $params = new Parameters(new Site(1), Factory::build('month', '2015-03-03'), new Segment('', [1]));
        $this->insertArchive($params);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();
        $this->assertEquals(['1', '10', '0', true], $archiveInfo);
    }

    public function test_loadExistingArchiveIdFromDb_returnsArchiveIfForACurrentPeriod_AndNewEnough()
    {
        $params = new Parameters(new Site(1), Factory::build('day', 'now'), new Segment('', [1]));
        $this->insertArchive($params, $tsArchived = time() - 1);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();
        $this->assertEquals(['1', '10', '0', true], $archiveInfo);
    }

    public function test_loadExistingArchiveIdFromDb_returnsNoArchiveIfForACurrentPeriod_AndNoneAreNewEnough()
    {
        $params = new Parameters(new Site(1), Factory::build('month', 'now'), new Segment('', [1]));
        $this->insertArchive($params, $tsArchived = time() - 3 * 3600);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();
        $this->assertEquals([false, '10', '0', true], $archiveInfo); // visits are still returned as this was the original behavior
    }

    private function insertArchive(Parameters $params, $tsArchived = null, $visits = 10)
    {
        $archiveWriter = new ArchiveWriter($params);
        $archiveWriter->initNewArchive();
        $archiveWriter->insertRecord('nb_visits', $visits);
        $archiveWriter->finalizeArchive();

        if ($tsArchived) {
            Db::query("UPDATE " . ArchiveTableCreator::getNumericTable($params->getPeriod()->getDateStart()) . " SET ts_archived = ?",
                [Date::factory($tsArchived)->getDatetime()]);
        }
    }
}