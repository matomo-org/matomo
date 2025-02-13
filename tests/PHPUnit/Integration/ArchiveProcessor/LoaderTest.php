<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\ArchiveProcessor;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Loader;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period\Factory;
use Piwik\Piwik;
use Piwik\Plugins\ExamplePlugin\RecordBuilders\ExampleMetric;
use Piwik\Plugins\ExamplePlugin\RecordBuilders\ExampleMetric2;
use Piwik\Plugins\Goals\API;
use Piwik\Segment;
use Piwik\Sequence;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\SegmentEditor\API as SegmentApi;
use Piwik\ArchiveProcessor\Rules;

/**
 * @group ArchiveProcessor
 * @group ArchiveProcessorLoader
 */
class LoaderTest extends IntegrationTestCase
{
    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createWebsite('2012-02-03 00:00:00');
        Fixture::createWebsite('2012-02-03 00:00:00');
    }

    public function testPluginOnlyArchivingDoesNotRelaunchChildArchives()
    {
        $_GET['pluginOnly'] = 1;
        $_GET['trigger'] = 'archivephp';

        $idSite = 1;
        $dateTime = '2020-01-20 02:03:04';
        $date = '2020-01-20';
        $period = 'week';
        $segment = '';
        $plugin = 'Actions';

        $t = Fixture::getTracker($idSite, $dateTime);
        $t->setUrl('http://slkdfj.com');
        Fixture::checkResponse($t->doTrackPageView('alsdkjf'));

        $periodObj = Factory::build($period, $date);
        foreach ($periodObj->getSubperiods() as $day) {
            // archive each day before hand
            $params = new Parameters(new Site($idSite), $day, new Segment($segment, [$idSite]));
            $loader = new Loader($params);
            $loader->prepareArchive($plugin);
        }

        $existingArchives = $this->getExistingArchives($date);
        $this->assertEquals([
            [
                'idarchive' => '1',
                'name' => 'done.VisitsSummary',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            [
                'idarchive' => '2',
                'name' => 'done.Actions',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
        ], $existingArchives);

        $params = new Parameters(new Site($idSite), $periodObj, new Segment($segment, [$idSite]));

        $loader = new Loader($params);
        $loader->prepareArchive($plugin);

        $existingArchives = $this->getExistingArchives($date);

        $this->assertEquals([
            [
                'idarchive' => '1',
                'name' => 'done.VisitsSummary',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            [
                'idarchive' => '2',
                'name' => 'done.Actions',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            [
                'idarchive' => '3',
                'name' => 'done.VisitsSummary',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-26',
                'period' => '2',
            ],
            [
                'idarchive' => '4',
                'name' => 'done.Actions',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-26',
                'period' => '2',
            ],
        ], $existingArchives);
    }

    public function testPluginOnlyArchivingDoesNotRelaunchChildArchivesWhenReusingAllPluginsArchives()
    {
        // not setting pluginOnly=1 to ensure all plugins archive is created for the day w/ visits
        $_GET['trigger'] = 'archivephp';

        $idSite = 1;
        $dateTime = '2020-01-20 02:03:04';
        $anotherDayDateTime = '2020-01-22 08:00:00';
        $date = '2020-01-20';
        $period = 'week';
        $segment = '';
        $plugin = 'ExamplePlugin'; // NOTE: it's important to use ExamplePlugin here since it has an example of creating partial archives

        API::getInstance()->addGoal($idSite, 'test goal', 'url', 'http', 'contains');

        $t = Fixture::getTracker($idSite, $dateTime);
        $t->setUrl('http://slkdfj.com');
        Fixture::checkResponse($t->doTrackPageView('alsdkjf'));

        $periodObj = Factory::build($period, $date);
        foreach ($periodObj->getSubperiods() as $day) {
            // archive each day before hand
            $params = new Parameters(new Site($idSite), $day, new Segment($segment, [$idSite]));
            $loader = new Loader($params);
            $loader->prepareArchive($plugin);
        }

        // add a visit to another day in the week, but no archive so it will get archived in pluginOnly request
        $t = Fixture::getTracker($idSite, $anotherDayDateTime);
        $t->setUrl('http://slkdfj.com');
        Fixture::checkResponse($t->doTrackPageView('alsdkjf 2'));

        $existingArchives = $this->getExistingArchives($date);
        $this->assertEquals([
            [
                'idarchive' => '1',
                'name' => 'done',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            [
                'idarchive' => '2',
                'name' => 'done90a5a511e1974bca37613b6daec137ba.VisitsSummary',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            [
                'idarchive' => '3',
                'name' => 'done90a5a511e1974bca37613b6daec137ba.Goals',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            // Why archive 4 is missing:
            // Triggering the archiving will at first archive core metrics (aka VisitsSummary) if they are not yet available.
            // In case there were no visits, the created archive will only contain a done flag, but no other metrics.
            // This causes the archiving (for core metrics) to be triggered again, which will create a new (empty) archive, while removing the previous one.
            // As archiving dependent segments also first triggers archiving VisitsSummary, it creates an empty archive.
            // Afterwards when archiving the Goals plugin it will archive VisitsSummary again, as the previous one is empty.
            // Which then causes this missing archive id.
            [
                'idarchive' => '5',
                'name' => 'donefea44bece172bc9696ae57c26888bf8a.VisitsSummary',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            [
                'idarchive' => '6',
                'name' => 'donefea44bece172bc9696ae57c26888bf8a.Goals',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
        ], $existingArchives);

        // archiving w/ pluginOnly=1
        $_GET['pluginOnly'] = 1;
        $_GET['requestedReport'] = ExampleMetric::EXAMPLEPLUGIN_METRIC_NAME; // so it will be set when the archiver recurses

        $params = new Parameters(new Site($idSite), $periodObj, new Segment($segment, [$idSite]));
        $params->setRequestedPlugin($plugin);
        $params->setArchiveOnlyReport(ExampleMetric::EXAMPLEPLUGIN_METRIC_NAME);

        $loader = new Loader($params);
        $loader->prepareArchive($plugin);

        $existingArchives = $this->getExistingArchives($date);

        // expected result means:
        // - we keep and reuse already existing all plugins archive for 2020-01-20
        // - we create new single plugin (non-partial) archives for VisitsSummary
        // - we create new single report (partial) archives for ExamplePlugin
        $this->assertEquals([
            [
                'idarchive' => '1',
                'name' => 'done',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            [
                'idarchive' => '2',
                'name' => 'done90a5a511e1974bca37613b6daec137ba.VisitsSummary',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            [
                'idarchive' => '3',
                'name' => 'done90a5a511e1974bca37613b6daec137ba.Goals',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            // archive 4 is missing as VisitsSummary is archived twice, as it doesn't contain data
            [
                'idarchive' => '5',
                'name' => 'donefea44bece172bc9696ae57c26888bf8a.VisitsSummary',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],
            [
                'idarchive' => '6',
                'name' => 'donefea44bece172bc9696ae57c26888bf8a.Goals',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-20',
                'period' => '1',
            ],

            // start of new archives
            [
                'idarchive' => '7',
                'name' => 'done.VisitsSummary',
                'value' => '1',
                'date1' => '2020-01-20',
                'date2' => '2020-01-26',
                'period' => '2',
            ],
            [
                'idarchive' => '8',
                'name' => 'done.VisitsSummary',
                'value' => '1',
                'date1' => '2020-01-22',
                'date2' => '2020-01-22',
                'period' => '1',
            ],
            [
                'idarchive' => '9',
                'name' => 'done.ExamplePlugin',
                'value' => '5',
                'date1' => '2020-01-20',
                'date2' => '2020-01-26',
                'period' => '2',
            ],
            [
                'idarchive' => '10',
                'name' => 'done.ExamplePlugin',
                'value' => '5',
                'date1' => '2020-01-22',
                'date2' => '2020-01-22',
                'period' => '1',
            ],
        ], $existingArchives);
    }

    private function getExistingArchives($date)
    {
        $table = ArchiveTableCreator::getNumericTable(Date::factory($date));
        return Db::fetchAll("SELECT idarchive, `name`, date1, date2, period, `value` FROM `$table` WHERE `name` LIKE 'done%' ORDER BY idarchive ASC");
    }

    /**
     * @dataProvider getTestDataForArchiving
     */
    public function testPluginOnlyArchivingCreatesAndReusesCorrectArchives($archiveData, $params, $expectedArchives, $archiveTwice)
    {
        $_GET['pluginOnly'] = 1;
        $_GET['trigger'] = 'archivephp';

        Date::$now = strtotime('2018-03-04 05:00:00');

        [$idSite, $period, $date, $segment, $plugin, $report] = $params;

        $t = Fixture::getTracker($idSite, $date);
        $t->setUrl('http://slkdfj.com');
        $t->doTrackPageView('alsdkjf');

        $params = new Parameters(new Site($idSite), Factory::build($period, $date), new Segment($segment, [$idSite]));
        $params->setRequestedPlugin($plugin);
        if ($report) {
            $params->setArchiveOnlyReport($report);
        }

        $this->insertArchiveData($archiveData);

        $loader = new Loader($params);
        $loader->prepareArchive($params->getRequestedPlugin());

        if ($archiveTwice) {
            if (is_array($archiveTwice)) {
                [$idSite2, $period2, $date2, $segment2, $plugin2, $report2] = $archiveTwice;

                $params2 = new Parameters(new Site($idSite2), Factory::build($period2, $date2), new Segment($segment2, [$idSite2]));
                $params2->setRequestedPlugin($plugin2);
                if ($report2) {
                    $params2->setArchiveOnlyReport($report2);
                }
            } else {
                $params2 = $params;
            }

            $loader2 = new Loader($params2);
            $loader2->prepareArchive($params->getRequestedPlugin());
        }

        $actualArchives = $this->getArchives();
        if ($actualArchives != $expectedArchives) {
            var_export($actualArchives);
        }
        $this->assertEquals($expectedArchives, $actualArchives);
    }

    public function getTestDataForArchiving()
    {
        $pluginSpecificArchive = [1, 'day', '2018-03-03', '', 'ExamplePlugin', false];

        $reportSpecificArchive1 = [1, 'day', '2018-03-03', '', 'ExamplePlugin', ExampleMetric::EXAMPLEPLUGIN_METRIC_NAME];
        $reportSpecificArchive2 = [1, 'day', '2018-03-03', '', 'ExamplePlugin', ExampleMetric2::EXAMPLEPLUGIN_CONST_METRIC_NAME];

        $unloadedPluginArchive = [1, 'day', '2018-03-03', '', 'MyImaginaryPlugin', false];

        return [
            // no archive, archive specific plugin
            [
                [],
                $pluginSpecificArchive,
                array (
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'bounce_count',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.VisitsSummary',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'max_actions',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_actions',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_uniq_visitors',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.ExamplePlugin',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'ExamplePlugin_example_metric',
                        'value' => '-603',
                    ),
                ),
                false,
            ],

            // all plugins, recent, archive specific plugin
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_OK, 'ts_archived' => '2018-03-04 04:50:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'nb_visits', 'value' => 12, 'ts_archived' => '2018-03-04 04:50:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'nb_visits_converted', 'value' => 3, 'ts_archived' => '2018-03-04 04:50:00'],
                ],
                $pluginSpecificArchive,
                array ( // done archive already exists and is recent, so we don't archive the plugin
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits',
                        'value' => '12',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits_converted',
                        'value' => '3',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.ExamplePlugin',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'ExamplePlugin_example_metric',
                        'value' => '-603',
                    ),
                ),
                false,
            ],

            // visitssummary, recent, archive specific plugin
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'done.VisitsSummary', 'value' => ArchiveWriter::DONE_OK, 'ts_archived' => '2018-03-04 04:50:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'nb_visits', 'value' => 12, 'ts_archived' => '2018-03-04 04:50:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'nb_visits_converted', 'value' => 3, 'ts_archived' => '2018-03-04 04:50:00'],
                ],
                $pluginSpecificArchive,
                array (
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.VisitsSummary',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits',
                        'value' => '12',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits_converted',
                        'value' => '3',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.ExamplePlugin',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'ExamplePlugin_example_metric',
                        'value' => '-603',
                    ),
                ),
                false,
            ],

            // all plugins, old, archive specific plugin
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_OK, 'ts_archived' => '2018-03-01 04:50:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'nb_visits', 'value' => 12, 'ts_archived' => '2018-03-01 04:50:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'nb_visits_converted', 'value' => 3, 'ts_archived' => '2018-03-01 04:50:00'],
                ],
                $pluginSpecificArchive,
                array (
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits',
                        'value' => '12',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits_converted',
                        'value' => '3',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.ExamplePlugin',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'ExamplePlugin_example_metric',
                        'value' => '-603',
                    ),
                ),
                false,
            ],

            // visitssummary, old, archive specific plugin
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'done.VisitsSummary', 'value' => ArchiveWriter::DONE_OK, 'ts_archived' => '2018-03-01 04:50:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'nb_visits', 'value' => 12, 'ts_archived' => '2018-03-01 04:50:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'name' => 'nb_visits_converted', 'value' => 3, 'ts_archived' => '2018-03-01 04:50:00'],
                ],
                $pluginSpecificArchive,
                array (
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.VisitsSummary',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits',
                        'value' => '12',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits_converted',
                        'value' => '3',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.ExamplePlugin',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'ExamplePlugin_example_metric',
                        'value' => '-603',
                    ),
                ),
                false,
            ],

            // no archive, archive specific plugin, archive specific plugin again
            [
                [],
                $pluginSpecificArchive,
                array (
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'bounce_count',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.VisitsSummary',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'max_actions',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_actions',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_uniq_visitors',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '3',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.ExamplePlugin',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '3',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'ExamplePlugin_example_metric',
                        'value' => '-603',
                    ),
                    array (
                        'idarchive' => '3',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'ExamplePlugin_example_metric2',
                        'value' => '1',
                    ),
                ),
                true,
            ],

            // no archive, archive specific report, archive specific report again
            [
                [],
                $reportSpecificArchive1,
                array (
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'bounce_count',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.VisitsSummary',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'max_actions',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_actions',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_uniq_visitors',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.ExamplePlugin',
                        'value' => '5',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'ExamplePlugin_example_metric',
                        'value' => '-603',
                    ),
                    array (
                        'idarchive' => '3',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.ExamplePlugin',
                        'value' => '5',
                    ),
                    array (
                        'idarchive' => '3',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'ExamplePlugin_example_metric',
                        'value' => '-603',
                    ),
                ),
                true,
            ],

            // no archive, archive specific report, archive different report again
            [
                [],
                $reportSpecificArchive1,
                array (
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'bounce_count',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.VisitsSummary',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'max_actions',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_actions',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_uniq_visitors',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.ExamplePlugin',
                        'value' => '5',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'ExamplePlugin_example_metric',
                        'value' => '-603',
                    ),
                ),
                $reportSpecificArchive2,
            ],

            // no archive, unloaded plugin
            [
                [],
                $unloadedPluginArchive,
                array (
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'bounce_count',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.VisitsSummary',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'max_actions',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_actions',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_uniq_visitors',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'nb_visits',
                        'value' => '1',
                    ),
                    array (
                        'idarchive' => '2',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done.MyImaginaryPlugin',
                        'value' => '1',
                    ),
                ),
                false,
            ],
        ];
    }

    public function testLoadExistingArchiveIdFromDbReturnsFalsesIfNoArchiveFound()
    {
        $params = new Parameters(new Site(1), Factory::build('day', '2015-03-03'), new Segment('', [1]));
        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        // unset numeric index keys kept for BC
        unset($archiveInfo[0]);
        unset($archiveInfo[1]);
        unset($archiveInfo[2]);
        unset($archiveInfo[3]);
        unset($archiveInfo[4]);
        unset($archiveInfo[5]);

        $this->assertEquals([
            'idArchives' => false,
            'visits' => false,
            'visitsConverted' => false,
            'archiveExists' => false,
            'doneFlagValue' => false,
            'tsArchived' => false,
            'existingRecords' => null,
        ], $archiveInfo);
    }

    /**
     * @dataProvider getTestDataForLoadExistingArchiveIdFromDbDebugConfig
     */
    public function testLoadExistingArchiveIdFromDbReturnsFalseIfPeriodIsForcedToArchive($periodType, $configSetting)
    {
        $date = $periodType == 'range' ? '2015-03-03,2015-03-04' : '2015-03-03';
        $params = new Parameters(new Site(1), Factory::build($periodType, $date), new Segment('', [1]));
        $this->insertArchive($params);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        $this->assertNotEmpty($archiveInfo['tsArchived']);
        $this->assertLessThanOrEqual(time(), strtotime($archiveInfo['tsArchived']));

        unset($archiveInfo['tsArchived']);

        // unset numeric index keys kept for BC
        unset($archiveInfo[0]);
        unset($archiveInfo[1]);
        unset($archiveInfo[2]);
        unset($archiveInfo[3]);
        unset($archiveInfo[4]);
        unset($archiveInfo[5]);

        $this->assertEquals([
            'idArchives' => [1],
            'visits' => 10,
            'visitsConverted' => 0,
            'archiveExists' => true,
            'doneFlagValue' => 1,
            'existingRecords' => null,
        ], $archiveInfo);

        Config::getInstance()->Debug[$configSetting] = 1;

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        // unset numeric index keys kept for BC
        unset($archiveInfo[0]);
        unset($archiveInfo[1]);
        unset($archiveInfo[2]);
        unset($archiveInfo[3]);
        unset($archiveInfo[4]);
        unset($archiveInfo[5]);

        $this->assertEquals([
            'idArchives' => false,
            'visits' => false,
            'visitsConverted' => false,
            'archiveExists' => false,
            'doneFlagValue' => false,
            'tsArchived' => false,
            'existingRecords' => null,
        ], $archiveInfo);
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

    public function testLoadExistingArchiveIdFromDbReturnsArchiveIfArchiveInThePast()
    {
        $params = new Parameters(new Site(1), Factory::build('month', '2015-03-03'), new Segment('', [1]));
        $this->insertArchive($params);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        $this->assertNotEmpty($archiveInfo['tsArchived']);
        unset($archiveInfo['tsArchived']);

        // unset numeric index keys kept for BC
        unset($archiveInfo[0]);
        unset($archiveInfo[1]);
        unset($archiveInfo[2]);
        unset($archiveInfo[3]);
        unset($archiveInfo[4]);
        unset($archiveInfo[5]);

        $this->assertEquals([
            'idArchives' => ['1'],
            'visits' => '10',
            'visitsConverted' => '0',
            'archiveExists' => true,
            'doneFlagValue' => '1',
            'existingRecords' => null,
        ], $archiveInfo);
    }

    public function testLoadExistingArchiveIdFromDbReturnsArchiveIfForACurrentPeriodAndNewEnough()
    {
        $params = new Parameters(new Site(1), Factory::build('day', 'now'), new Segment('', [1]));
        $this->insertArchive($params, $tsArchived = time() - 1);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        $this->assertNotEmpty($archiveInfo['tsArchived']);
        unset($archiveInfo['tsArchived']);

        // unset numeric index keys kept for BC
        unset($archiveInfo[0]);
        unset($archiveInfo[1]);
        unset($archiveInfo[2]);
        unset($archiveInfo[3]);
        unset($archiveInfo[4]);
        unset($archiveInfo[5]);

        $this->assertEquals([
            'idArchives' => ['1'],
            'visits' => '10',
            'visitsConverted' => '0',
            'archiveExists' => true,
            'doneFlagValue' => '1',
            'existingRecords' => null,
        ], $archiveInfo);
    }

    public function testLoadExistingArchiveIdFromDbReturnsNoArchiveIfForACurrentPeriodAndNoneAreNewEnough()
    {
        $params = new Parameters(new Site(1), Factory::build('month', 'now'), new Segment('', [1]));
        $this->insertArchive($params, $tsArchived = time() - 3 * 3600);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        $this->assertNotEmpty($archiveInfo['tsArchived']);
        unset($archiveInfo['tsArchived']);

        // unset numeric index keys kept for BC
        unset($archiveInfo[0]);
        unset($archiveInfo[1]);
        unset($archiveInfo[2]);
        unset($archiveInfo[3]);
        unset($archiveInfo[4]);
        unset($archiveInfo[5]);

        $this->assertEquals([
            'idArchives' => false,
            'visits' => '10',
            'visitsConverted' => '0',
            'archiveExists' => true,
            'doneFlagValue' => '1',
            'existingRecords' => null,
        ], $archiveInfo); // visits are still returned as this was the original behavior
    }

    /**
     * @dataProvider getTestDataForGetReportsToInvalidate
     */
    public function testGetReportsToInvalidateReturnsCorrectReportsToInvalidate($rememberedReports, $idSite, $period, $date, $segment, $expected)
    {
        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        foreach ($rememberedReports as $entry) {
            $invalidator->rememberToInvalidateArchivedReportsLater($entry['idSite'], Date::factory($entry['date']));
        }

        $params = new Parameters(new Site($idSite), Factory::build($period, $date), new Segment($segment, [$idSite]));
        $loader = new Loader($params);

        $reportsToInvalidate = $loader->getReportsToInvalidate();
        foreach ($reportsToInvalidate as &$sites) {
            sort($sites);
        }
        $this->assertEquals($expected, $reportsToInvalidate);
    }

    public function getTestDataForGetReportsToInvalidate()
    {
        return [
            // two dates for one site
            [
                [
                    ['idSite' => 1, 'date' => '2013-04-05'],
                    ['idSite' => 1, 'date' => '2013-03-05'],
                    ['idSite' => 2, 'date' => '2013-05-05'],
                ],
                1,
                'day',
                '2013-04-05',
                '',
                [
                    '2013-04-05' => [1],
                ],
            ],

            // no dates for a site
            [
                [
                    ['idSite' => '', 'date' => '2013-04-05'],
                    ['idSite' => '', 'date' => '2013-04-06'],
                    ['idSite' => 2, 'date' => '2013-05-05'],
                ],
                1,
                'day',
                '2013-04-05',
                'browserCode==ff',
                [],
            ],

            // day period not within range
            [
                [
                    ['idSite' => 1, 'date' => '2014-03-04'],
                    ['idSite' => 1, 'date' => '2014-03-06'],
                ],
                1,
                'day',
                '2013-03-05',
                '',
                [],
            ],

            // non-day periods
            [
                [
                    ['idSite' => 1, 'date' => '2014-03-01'],
                    ['idSite' => 1, 'date' => '2014-03-06'],
                    ['idSite' => 2, 'date' => '2014-03-01'],
                ],
                1,
                'week',
                '2014-03-01',
                '',
                [
                    '2014-03-01' => [1, 2],
                ],
            ],
            [
                [
                    ['idSite' => 1, 'date' => '2014-02-01'],
                    ['idSite' => 1, 'date' => '2014-03-06'],
                    ['idSite' => 2, 'date' => '2014-03-05'],
                    ['idSite' => 2, 'date' => '2014-03-06'],
                ],
                1,
                'month',
                '2014-03-01',
                '',
                [
                    '2014-03-06' => [1, 2],
                ],
            ],
        ];
    }

    public function testCanSkipThisArchiveReturnsFalseIfSiteIsNotUsingTracker()
    {
        Piwik::addAction('CronArchive.getIdSitesNotUsingTracker', function (&$idSites) {
            $idSites[] = 1;
        });

        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('', []));
        $loader = new Loader($params);

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function testCanSkipThisArchiveReturnsFalseIfSiteHasVisitWithinTimeframeForPeriodDay()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('', []));
        $loader = new Loader($params);

        $tracker = Fixture::getTracker(1, '2016-02-03 04:00:00');
        $tracker->setUrl('http://example.org/abc');
        Fixture::checkResponse($tracker->doTrackPageView('abc'));

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function testCanSkipThisArchiveReturnsFalseIfSiteHasVisitWithinTimeframeForPeriodYear()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('', []));
        $loader = new Loader($params);

        $tracker = Fixture::getTracker(1, '2016-03-04 00:00:00');
        $tracker->setUrl('http://example.org/abc');
        Fixture::checkResponse($tracker->doTrackPageView('abc'));

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function testCanSkipThisArchiveReturnsFalseIfSiteHasChildArchiveWithinPeriodForPeriodWeek()
    {
        $params = new Parameters(new Site(1), Factory::build('week', '2016-02-03'), new Segment('browserCode==ch', []));
        $loader = new Loader($params);

        $dayParams = new Parameters(new Site(1), Factory::build('day', '2016-02-03'), new Segment('', []));

        $archiveWriter = new ArchiveWriter($dayParams);
        $archiveWriter->initNewArchive();
        $archiveWriter->finalizeArchive();

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function testCanSkipThisArchiveReturnsFalseIfSiteHasChildArchiveWithinPeriodForPeriodMonthWhenWeekChildSpansTwoMonths()
    {
        $params = new Parameters(new Site(1), Factory::build('month', '2016-02-01'), new Segment('browserCode==ch', []));
        $loader = new Loader($params);

        $dayParams = new Parameters(new Site(1), Factory::build('week', '2016-02-01'), new Segment('', []));

        $archiveWriter = new ArchiveWriter($dayParams);
        $archiveWriter->initNewArchive();
        $archiveWriter->finalizeArchive();

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function testCanSkipThisArchiveReturnsFalseIfSiteHasChildArchiveWithinPeriodForPeriodYear()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('browserCode==ch', []));
        $loader = new Loader($params);

        $dayParams = new Parameters(new Site(1), Factory::build('day', '2016-03-04'), new Segment('', []));

        $archiveWriter = new ArchiveWriter($dayParams);
        $archiveWriter->initNewArchive();
        $archiveWriter->finalizeArchive();

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function testCanSkipThisArchiveReturnsTrueIfThereAreNoVisitsNoChildArchivesAndSiteIsUsingTheTracker()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('', []));
        $loader = new Loader($params);

        $this->assertTrue($loader->canSkipThisArchive());

        $tracker = Fixture::getTracker(2, '2016-03-04 00:00:00');
        $tracker->setUrl('http://example.org/abc');
        Fixture::checkResponse($tracker->doTrackPageView('abc'));

        $this->assertTrue($loader->canSkipThisArchive());
    }

    public function testCanSkipThisArchiveIgnoresSegments()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('browserCode==ch', []));
        $loader = new Loader($params);

        $tracker = Fixture::getTracker(1, '2016-03-04 00:00:00');
        $tracker->setUrl('http://example.org/abc');
        Fixture::checkResponse($tracker->doTrackPageView('abc'));

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function testCanSkipArchiveForSegmentReturnsFalseIfNoSegments()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('', []));
        $loader = new Loader($params);

        $this->assertFalse($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsFalseIfPeriodEndLaterThanSegmentArchiveStartDate()
    {
        Rules::setBrowserTriggerArchiving(false);
        $definition = 'browserCode==ch';

        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('year', '2021-04-23'), new Segment($definition, [1]));
        $loader = new Loader($params);

        $this->assertFalse($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsTrueIfPeriodEndEarlierThanSegmentArchiveStartDate()
    {
        Rules::setBrowserTriggerArchiving(false);

        $definition = 'browserCode==ch';
        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('year', '2010-04-23'), new Segment($definition, [1]));
        $loader = new Loader($params);

        $this->assertTrue($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsFalseIfHasInvalidationForThePeriod()
    {
        Rules::setBrowserTriggerArchiving(false);

        $date = '2010-04-23';
        $definition = 'browserCode==ch';
        $segment = new Segment($definition, [1]);
        $doneFlag = Rules::getDoneStringFlagFor([1], $segment, 'day', null);

        $this->insertInvalidations([
            ['date1' => $date, 'date2' => $date, 'period' => 1, 'name' => $doneFlag],
        ]);

        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('day', $date), $segment);
        $loader = new Loader($params);

        $this->assertFalse($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsTrueIfHasInvalidationForReportButWeDonSpecifyReport()
    {
        Rules::setBrowserTriggerArchiving(false);

        $date = '2010-04-23';
        $definition = 'browserCode==ch';
        $segment = new Segment($definition, [1]);
        $doneFlag = Rules::getDoneStringFlagFor([1], $segment, 'day', null);

        $this->insertInvalidations([
            ['date1' => $date, 'date2' => $date, 'period' => 1, 'name' => $doneFlag, 'report' => 'myReport'],
        ]);

        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('day', $date), $segment);
        $loader = new Loader($params);

        $this->assertTrue($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsFalseIfHasInvalidationForReportWeAskedFor()
    {
        Rules::setBrowserTriggerArchiving(false);

        $date = '2010-04-23';
        $definition = 'browserCode==ch';
        $segment = new Segment($definition, [1]);
        $doneFlag = Rules::getDoneStringFlagFor([1], $segment, 'day', null);

        $this->insertInvalidations([
            ['date1' => $date, 'date2' => $date, 'period' => 1, 'name' => $doneFlag, 'report' => 'myReport'],
        ]);

        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('day', $date), $segment);
        $params->setArchiveOnlyReport('myReport');
        $loader = new Loader($params);

        $this->assertFalse($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsTrueIfHasNoInvalidationForReportWeAskedFor()
    {
        Rules::setBrowserTriggerArchiving(false);

        $date = '2010-04-23';
        $definition = 'browserCode==ch';
        $segment = new Segment($definition, [1]);
        $doneFlag = Rules::getDoneStringFlagFor([1], $segment, 'day', null);

        $this->insertInvalidations([
            ['date1' => $date, 'date2' => $date, 'period' => 1, 'name' => $doneFlag, 'report' => 'myReport'],
        ]);

        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('day', $date), $segment);
        $params->setArchiveOnlyReport('otherReport');
        $loader = new Loader($params);

        $this->assertTrue($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsTrueIfPluginIsDisabled()
    {
        Rules::setBrowserTriggerArchiving(false);
        $config = Config::getInstance();
        $config->General['disable_archiving_segment_for_plugins'] = 'testPlugin';
        $date = '2010-04-23';
        $definition = 'browserCode==ch';
        $segment = new Segment($definition, [1]);
        $doneFlag = Rules::getDoneStringFlagFor([1], $segment, 'day', null);

        $this->insertInvalidations([
          ['date1' => $date, 'date2' => $date, 'period' => 1, 'name' => $doneFlag, 'report' => 'myReport'],
        ]);

        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('day', $date), $segment);
        $params->setRequestedPlugin('testPlugin');
        $params->setArchiveOnlyReport('myReport');
        $loader = new Loader($params);
        $this->assertTrue($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsTrueIfPluginIsDisabledBySiteId()
    {
        Rules::setBrowserTriggerArchiving(false);
        Config::setSetting('General_1', 'disable_archiving_segment_for_plugins', 'testPlugin');
        $date = '2010-04-23';
        $definition = 'browserCode==ch';
        $segment = new Segment($definition, [1]);
        $doneFlag = Rules::getDoneStringFlagFor([1], $segment, 'day', null);

        $this->insertInvalidations([
          ['date1' => $date, 'date2' => $date, 'period' => 1, 'name' => $doneFlag, 'report' => 'myReport'],
        ]);

        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('day', $date), $segment);
        $params->setRequestedPlugin('testPlugin');
        $params->setArchiveOnlyReport('myReport');
        $loader = new Loader($params);
        $this->assertTrue($loader->canSkipArchiveForSegment());

        $params = new Parameters(new Site(2), Factory::build('day', $date), $segment);
        $params->setRequestedPlugin('testPlugin');
        $params->setArchiveOnlyReport('myReport');
        $loader = new Loader($params);
        $this->assertFalse($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsFalseIfPeriodIsRangeAndBrowserArchivingDisabledAndNotCLI()
    {
        Rules::setBrowserTriggerArchiving(false);

        $definition = 'browserCode==ch';
        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('range', '2015-03-03,2015-03-04'), new Segment($definition, [1]));
        $loader = new Loader($params);

        $this->assertFalse($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsFalseIfPeriodIsRangeAndBrowserArchiving()
    {
        Rules::setBrowserTriggerArchiving(true);

        $definition = 'browserCode==ch';
        SegmentApi::getInstance()->add('segment', $definition, 1, false, true);
        $params = new Parameters(new Site(1), Factory::build('range', '2015-03-03,2015-03-04'), new Segment($definition, [1]));
        $loader = new Loader($params);

        $this->assertFalse($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsTrueIfPeriodIsRangeAndCliArchiving()
    {
        Rules::setBrowserTriggerArchiving(false);
        $_GET['trigger'] = 'archivephp';

        $definition = 'browserCode==ch';
        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('range', '2015-03-03,2015-03-04'), new Segment($definition, [1]));
        $loader = new Loader($params);

        $this->assertTrue($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsTrueIfPeriodIsDayAndCliArchiving()
    {
        Rules::setBrowserTriggerArchiving(false);
        $_GET['trigger'] = 'archivephp';

        $definition = 'browserCode==ch';
        SegmentApi::getInstance()->add('segment', $definition, 1, true, true);
        $params = new Parameters(new Site(1), Factory::build('day', '2015-03-03'), new Segment($definition, [1]));
        $loader = new Loader($params);

        $this->assertTrue($loader->canSkipArchiveForSegment());
    }

    public function testCanSkipArchiveForSegmentReturnsFalseIfPeriodIsDayAndBrowserArchiving()
    {
        Rules::setBrowserTriggerArchiving(true);

        $definition = 'browserCode==ch';
        SegmentApi::getInstance()->add('segment', $definition, 1, false, true);
        $params = new Parameters(new Site(1), Factory::build('day', '2015-03-03'), new Segment($definition, [1]));
        $loader = new Loader($params);

        $this->assertFalse($loader->canSkipArchiveForSegment());
    }

    public function testForcePluginArchivingCreatesPluginSpecificArchive()
    {
        $_GET['trigger'] = 'archivephp';
        $_GET['pluginOnly'] = '1';

        $params = new Parameters(new Site(1), Factory::build('day', '2016-02-03'), new Segment('', [1]));
        $loader = new Loader($params);

        $tracker = Fixture::getTracker(1, '2016-02-03 00:00:00');
        $tracker->setUrl('http://example.org/abc');
        Fixture::checkResponse($tracker->doTrackPageView('abc'));

        $idArchive = $loader->prepareArchive('Actions')[0];
        $this->assertNotEmpty($idArchive);

        $table = ArchiveTableCreator::getNumericTable(Date::factory('2016-02-03'));
        $doneFlag = Db::fetchOne("SELECT `name` FROM `$table` WHERE `name` LIKE 'done%' AND idarchive IN (" . implode(',', $idArchive) . ")");
        $this->assertEquals('done.Actions', $doneFlag);
    }

    private function insertArchive(Parameters $params, $tsArchived = null, $visits = 10)
    {
        $archiveWriter = new ArchiveWriter($params);
        $archiveWriter->initNewArchive();
        $archiveWriter->insertRecord('nb_visits', $visits);
        $archiveWriter->finalizeArchive();

        if ($tsArchived) {
            Db::query(
                "UPDATE " . ArchiveTableCreator::getNumericTable($params->getPeriod()->getDateStart()) . " SET ts_archived = ?",
                [Date::factory($tsArchived)->getDatetime()]
            );
        }
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
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

        if (!empty($archiveRows)) {
            $idarchives = array_column($archiveRows, 'idarchive');
            $max = max($idarchives);

            $seq = new Sequence(ArchiveTableCreator::getNumericTable(Date::factory($archiveRows[0]['date1'])));
            $seq->create($max);
        }
    }

    private function getArchives()
    {
        $results = [];
        foreach (ArchiveTableCreator::getTablesArchivesInstalled('numeric', true) as $table) {
            $queryResults = Db::fetchAll("SELECT idarchive, idsite, date1, date2, period, `name`, `value` FROM `$table`");
            $results = array_merge($results, $queryResults);
        }
        return $results;
    }

    private function insertInvalidations(array $invalidations)
    {
        $table = Common::prefixTable('archive_invalidations');
        $now = Date::now()->getDatetime();
        foreach ($invalidations as $invalidation) {
            $sql = "INSERT INTO `$table` (idsite, date1, date2, period, `name`, status, ts_invalidated, ts_started, report) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            Db::query($sql, [
                $invalidation['idsite'] ?? 1, $invalidation['date1'], $invalidation['date2'], $invalidation['period'], $invalidation['name'],
                $invalidation['status'] ?? 0, $invalidation['ts_invalidated'] ?? $now, $invalidation['ts_started'] ?? null, $invalidation['report'] ?? null
            ]);
        }
    }
}
