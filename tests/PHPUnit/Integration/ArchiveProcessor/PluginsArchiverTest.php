<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration\Archive;

use Piwik\ArchiveProcessor\PluginsArchiver;
use Piwik\Config;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Db;
use Piwik\ArchiveProcessor\Parameters;
use Exception;
use Piwik\Plugin\Archiver;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Tracker\Db\DbException;

class CustomArchiver extends Archiver
{

    public function aggregateDayReport()
    {
        throw new DbException('Failed query foo bar', 42);
    }

    public function aggregateMultipleReports()
    {
        throw new DbException('Failed query foo bar baz', 43);
    }

}

class CustomPluginsArchiver extends PluginsArchiver
{
    protected function getPluginArchivers()
    {
        return array(
            'MyPluginName' => 'Piwik\Tests\Integration\Archive\CustomArchiver'
        );
    }

}

/**
 * @group PluginsArchiver
 * @group PluginsArchiverTest
 * @group Core
 */
class PluginsArchiverTest extends IntegrationTestCase
{

    /**
     * @var PluginsArchiver
     */
    private $pluginsArchiver;

    public function setUp()
    {
        parent::setUp();

        Fixture::createWebsite('2015-01-01 00:00:00');

        $this->pluginsArchiver = new CustomPluginsArchiver($this->createArchiveProcessorParamaters(), $isTemporary = false);
    }

    private function createArchiveProcessorParamaters()
    {
        $oPeriod = PeriodFactory::makePeriodFromQueryParams('UTC', 'day', '2015-01-01');

        $segment = new Segment(false, array(1));
        $params  = new Parameters(new Site(1), $oPeriod, $segment);

        return $params;
    }

    /**
     * @expectedException \Piwik\Tracker\Db\DbException
     * @expectedExceptionMessage Failed query foo bar - caused by plugin MyPluginName
     * @expectedExceptionCode 42
     */
    public function test_purgeOutdatedArchives_PurgesCorrectTemporaryArchives_WhileKeepingNewerTemporaryArchives_WithBrowserTriggeringEnabled()
    {
        $this->pluginsArchiver->callAggregateAllPlugins(1, 1);
    }

}