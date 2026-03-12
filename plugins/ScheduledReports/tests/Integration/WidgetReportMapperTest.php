<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports\tests\Integration;

use Piwik\Plugins\ScheduledReports\WidgetReportMapper;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Report\ReportWidgetConfig;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\WidgetsList;

/**
 * @group ScheduledReports
 * @group ScheduledReportsWidgetReportMapperSimpleTest
 */
class WidgetReportMapperTest extends IntegrationTestCase
{
    private $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();

        self::setSuperUser();
        \Piwik\Plugin\Manager::getInstance()->loadPlugins(array(
            'API',
            'CoreHome',
            'ScheduledReports',
            'VisitsSummary',
            'Referrers',
            'Live',
            'Events',
            'Goals',
            'Funnels',
        ));
        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();

        SitesManagerAPI::getInstance()->addSite('Test', array('http://piwik.net'));
    }

    public function testGetMappingForSiteIncludesKnownReports()
    {
        $mapper = new WidgetReportMapper();

        $mapping = $mapper->getMappingForSite($this->idSite);

        $visitsSummaryWidgetId = WidgetsList::getWidgetUniqueId('VisitsSummary', 'get');
        $referrersWidgetId = WidgetsList::getWidgetUniqueId('Referrers', 'getReferrerType');

        $this->assertArrayHasKey($visitsSummaryWidgetId, $mapping);
        $this->assertArrayHasKey($referrersWidgetId, $mapping);
        $this->assertSame('VisitsSummary_get', $mapping[$visitsSummaryWidgetId]);
        $this->assertSame('Referrers_getReferrerType', $mapping[$referrersWidgetId]);
    }

    public function testGetWidgetNamesByIdIncludesNonReportWidgetsWhenNotExcluded()
    {
        $mapper = new WidgetReportMapper();

        $reportWidgetId = null;
        $nonReportWidgetId = null;
        foreach (WidgetsList::get()->getWidgetConfigs() as $widgetConfig) {
            if (null === $reportWidgetId && $widgetConfig instanceof ReportWidgetConfig) {
                $reportWidgetId = $widgetConfig->getUniqueId();
            }
            if (
                null === $nonReportWidgetId
                && !($widgetConfig instanceof ReportWidgetConfig)
                && !in_array($widgetConfig->getUniqueId(), WidgetReportMapper::NO_REPORT_WIDGETS, true)
            ) {
                $nonReportWidgetId = $widgetConfig->getUniqueId();
            }
            if ($reportWidgetId && $nonReportWidgetId) {
                break;
            }
        }

        $this->assertNotEmpty($reportWidgetId);
        $this->assertNotEmpty($nonReportWidgetId);

        $namesById = $mapper->getWidgetNamesById(array($reportWidgetId, $nonReportWidgetId));

        $this->assertArrayHasKey($reportWidgetId, $namesById);
        $this->assertArrayHasKey($nonReportWidgetId, $namesById);
    }

    public function testGetWidgetNamesByIdSkipsNoReportWidgets()
    {
        $mapper = new WidgetReportMapper();

        $noReportWidgetId = null;
        foreach (WidgetsList::get()->getWidgetConfigs() as $widgetConfig) {
            $uniqueId = $widgetConfig->getUniqueId();
            if (in_array($uniqueId, WidgetReportMapper::NO_REPORT_WIDGETS, true)) {
                $noReportWidgetId = $uniqueId;
                break;
            }
        }

        $this->assertNotEmpty($noReportWidgetId);

        $namesById = $mapper->getWidgetNamesById(array($noReportWidgetId));

        $this->assertSame(array(), $namesById);
    }

    public function testGetMappingForSiteIncludesGoalAndFunnelWidgets()
    {
        $mapper = new WidgetReportMapper();

        $goalWidget = new class extends \Piwik\Report\ReportWidgetConfig {
            public function getUniqueId()
            {
                return 'widgetGoal_71';
            }
        };
        $funnelWidget = new class extends \Piwik\Report\ReportWidgetConfig {
            public function getUniqueId()
            {
                return 'widgetFunnelsfunnelReportidGoal0idFunnel1';
            }
        };

        $this->setMapperWidgetConfigs($mapper, array($goalWidget, $funnelWidget));

        $mapping = $mapper->getMappingForSite($this->idSite);

        $this->assertArrayHasKey('widgetGoal_71', $mapping);
        $this->assertArrayHasKey('widgetFunnelsfunnelReportidGoal0idFunnel1', $mapping);
        $this->assertSame('Goals_get_idGoal--71', $mapping['widgetGoal_71']);
        $this->assertSame('Funnels_getMetrics_idFunnel--1', $mapping['widgetFunnelsfunnelReportidGoal0idFunnel1']);
    }

    public function testGetMappingForSiteIncludesEventsContainerMappings()
    {
        $mapper = new WidgetReportMapper();

        $actionWidget = new \Piwik\Widget\WidgetConfig();
        $actionWidget->setModule('Events')->setAction('getAction')->setParameters(array(
            'secondaryDimension' => 'eventName',
        ));
        $nameWidget = new \Piwik\Widget\WidgetConfig();
        $nameWidget->setModule('Events')->setAction('getName')->setParameters(array(
            'secondaryDimension' => 'eventAction',
        ));
        $categoryWidget = new \Piwik\Widget\WidgetConfig();
        $categoryWidget->setModule('Events')->setAction('getCategory')->setParameters(array(
            'secondaryDimension' => 'eventAction',
        ));

        $eventsContainer = new \Piwik\Plugins\Events\Widgets\EventsByDimension();
        $eventsContainer->setWidgetConfigs(array($actionWidget, $nameWidget, $categoryWidget));

        $this->setMapperWidgetConfigs($mapper, array($eventsContainer));

        $mapping = $mapper->getMappingForSite($this->idSite);

        $this->assertSame('Events_getAction', $mapping[$actionWidget->getUniqueId()]);
        $this->assertSame('Events_getName', $mapping[$nameWidget->getUniqueId()]);
        $this->assertSame('Events_getCategory', $mapping[$categoryWidget->getUniqueId()]);
    }

    public function testFindReportIdByWidgetParametersPrefersParameterNameMatches()
    {
        $mapper = new WidgetReportMapper();
        $method = new \ReflectionMethod(WidgetReportMapper::class, 'findReportIdByWidgetParameters');
        $method->setAccessible(true);

        $reportId = $method->invoke(
            $mapper,
            'SomeModule.someAction',
            array(
                'first' => 'firstValue',
                'second' => 'matchingValue',
            ),
            array(
                'SomeModule.someAction.second.matchingValue' => 'SomeModule_someAction_second_matchingValue',
                'SomeModule.someAction.matchingValue' => 'SomeModule_someAction_matchingValue',
            )
        );

        $this->assertSame('SomeModule_someAction_second_matchingValue', $reportId);
    }

    public function testFindReportIdByWidgetParametersDoesNotFallbackToValueOnlyWhenThereAreMultipleParameters()
    {
        $mapper = new WidgetReportMapper();
        $method = new \ReflectionMethod(WidgetReportMapper::class, 'findReportIdByWidgetParameters');
        $method->setAccessible(true);

        $reportId = $method->invoke(
            $mapper,
            'SomeModule.someAction',
            array(
                'first' => 'firstValue',
                'second' => 'matchingValue',
            ),
            array(
                'SomeModule.someAction.matchingValue' => 'SomeModule_someAction_matchingValue',
            )
        );

        $this->assertNull($reportId);
    }

    public function testFindReportIdByWidgetParametersFallsBackToValueOnlyWhenThereIsSingleParameter()
    {
        $mapper = new WidgetReportMapper();
        $method = new \ReflectionMethod(WidgetReportMapper::class, 'findReportIdByWidgetParameters');
        $method->setAccessible(true);

        $reportId = $method->invoke(
            $mapper,
            'SomeModule.someAction',
            array(
                'single' => 'matchingValue',
            ),
            array(
                'SomeModule.someAction.matchingValue' => 'SomeModule_someAction_matchingValue',
            )
        );

        $this->assertSame('SomeModule_someAction_matchingValue', $reportId);
    }

    public function testGuessReportIdFromHeuristicsFallsBackToGetForSimpleEvolutionWidgets()
    {
        $mapper = new WidgetReportMapper();
        $method = new \ReflectionMethod(WidgetReportMapper::class, 'guessReportIdFromHeuristics');
        $method->setAccessible(true);

        $reportId = $method->invoke(
            $mapper,
            'VisitsSummary',
            'getEvolutionGraph',
            array(
                'VisitsSummary.get' => 'VisitsSummary_get',
            ),
            array()
        );

        $this->assertSame('VisitsSummary_get', $reportId);
    }

    public function testGuessReportIdFromHeuristicsMapsParameterizedCustomReportsEvolutionWidgets()
    {
        $mapper = new WidgetReportMapper();
        $method = new \ReflectionMethod(WidgetReportMapper::class, 'guessReportIdFromHeuristics');
        $method->setAccessible(true);

        $reportId = $method->invoke(
            $mapper,
            'CustomReports',
            'getEvolutionGraph',
            array(
                'CustomReports.getCustomReport.idCustomReport.17' => 'CustomReports_getCustomReport_idCustomReport--17',
            ),
            array(
                'idCustomReport' => '17',
            )
        );

        $this->assertSame('CustomReports_getCustomReport_idCustomReport--17', $reportId);
    }

    private function setMapperWidgetConfigs(WidgetReportMapper $mapper, array $configs): void
    {
        $property = new \ReflectionProperty(WidgetReportMapper::class, 'widgetConfigs');
        $property->setAccessible(true);
        $property->setValue($mapper, $configs);
    }

    private static function setSuperUser(): void
    {
        FakeAccess::$superUser = true;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }

    public function testGetWidgetNamesByIdIgnoresUnknownIds()
    {
        $mapper = new WidgetReportMapper();

        $namesById = $mapper->getWidgetNamesById(array('widgetUnknown'));

        $this->assertSame(array(), $namesById);
    }

    public function testExtractWidgetIdsFromLayoutHandlesColumnsAndDedupes()
    {
        $mapper = new WidgetReportMapper();

        $layout = array(
            'columns' => array(
                array(
                    (object) array('uniqueId' => 'widgetOne'),
                    (object) array('uniqueId' => 'widgetTwo'),
                ),
                array(
                    (object) array('uniqueId' => 'widgetOne'),
                    null,
                ),
            ),
        );

        $this->assertSame(
            array('widgetOne', 'widgetTwo'),
            $mapper->extractWidgetIdsFromLayout($layout)
        );
    }

    public function testExtractWidgetIdsFromLayoutAcceptsObjectLayout()
    {
        $mapper = new WidgetReportMapper();

        $layout = (object) array(
            'columns' => (object) array(
                array(
                    (object) array('uniqueId' => 'widgetA'),
                    (object) array('uniqueId' => 'widgetB'),
                ),
            ),
        );

        $this->assertSame(
            array('widgetA', 'widgetB'),
            $mapper->extractWidgetIdsFromLayout($layout)
        );
    }

    public function testExtractWidgetIdsFromLayoutHandlesArrayWidgetsAndInvalidEntries()
    {
        $mapper = new WidgetReportMapper();

        $layout = array(
            'columns' => array(
                array(
                    array('uniqueId' => 'widgetArrayOne'),
                    (object) array('uniqueId' => 'widgetObjectOne'),
                    array('uniqueId' => 'widgetArrayOne'),
                    array('missingUniqueId' => 'ignored'),
                    null,
                    'invalidWidgetType',
                ),
                'invalidColumnType',
                (object) array(
                    array('uniqueId' => 'widgetObjectColumnArrayWidget'),
                    (object) array('uniqueId' => 'widgetObjectColumnOne'),
                    array('uniqueId' => 'widgetObjectOne'),
                    (object) array(),
                ),
            ),
        );

        $this->assertSame(
            array(
                'widgetArrayOne',
                'widgetObjectOne',
                'widgetObjectColumnArrayWidget',
                'widgetObjectColumnOne',
            ),
            $mapper->extractWidgetIdsFromLayout($layout)
        );
    }
}
