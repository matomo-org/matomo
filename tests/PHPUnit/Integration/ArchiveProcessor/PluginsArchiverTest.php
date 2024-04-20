<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Archive;

use Piwik\ArchiveProcessor\PluginsArchiver;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Site;
use Piwik\ArchiveProcessor\Parameters;
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
    protected static function getPluginArchivers()
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

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2015-01-01 00:00:00');
        Fixture::createWebsite('2015-01-01 00:00:00');
        Fixture::createWebsite('2015-01-01 00:00:00');

        $this->pluginsArchiver = new CustomPluginsArchiver($this->createArchiveProcessorParameters());
    }

    private function createArchiveProcessorParameters()
    {
        $oPeriod = PeriodFactory::makePeriodFromQueryParams('UTC', 'day', '2015-01-01');

        $segment = new Segment(false, array(1));
        $params  = new Parameters(new Site(1), $oPeriod, $segment);

        return $params;
    }

    public function test_purgeOutdatedArchives_PurgesCorrectTemporaryArchives_WhileKeepingNewerTemporaryArchives_WithBrowserTriggeringEnabled()
    {
        $this->expectException(\Piwik\ArchiveProcessor\PluginsArchiverException::class);
        $this->expectExceptionCode(42);
        $this->expectExceptionMessage('Failed query foo bar - in plugin MyPluginName');

        $this->pluginsArchiver->callAggregateAllPlugins(1, 1);
    }

    public function test_archiveMultipleSites()
    {
        self::expectNotToPerformAssertions();

        Piwik::addAction('ArchiveProcessor.Parameters.getIdSites', function (&$idSites, $period) {
            if (count($idSites) === 1 && reset($idSites) === 1) {
                $idSites = array(2,3);
            }
        });

        Piwik::addAction('ArchiveProcessor.shouldAggregateFromRawData', function (&$shouldAggregateRawData, Parameters $params) {
            // needed as by default we would only aggregate for single site
            if ($params->isDayArchive()) {
                $shouldAggregateRawData = true;
            }
        });

        $this->pluginsArchiver = new PluginsArchiver($this->createArchiveProcessorParameters());
        $this->pluginsArchiver->callAggregateCoreMetrics();
        $this->pluginsArchiver->callAggregateAllPlugins(1, 1, $forceArchivingWithoutVisits = true);
    }
}
