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

        $this->assertEquals([false, false, false], $archiveInfo);
    }

    public function test_loadExistingArchiveIdFromDb_returnsFalsesPeriodIsForcedToArchive()
    {
        // TODO
    }

    public function test_loadExistingArchiveIdFromDb_returnsArchiveIfArchiveInThePast()
    {
        // TODO
    }

    public function test_loadExistingArchiveIdFromDb_returnsArchiveIfForACurrentPeriod_AndOldEnough()
    {
        // TODO
    }

    public function test_loadExistingArchiveIdFromDb_returnsNoArchiveIfForACurrentPeriod_AndNoneAreOldEnough()
    {
        // TODO
    }
}