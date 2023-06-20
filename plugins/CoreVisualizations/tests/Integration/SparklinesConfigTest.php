<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Integration;

use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines\Config;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreVisualizations
 * @group SparklinesConfigTest
 * @group Plugins
 */
class SparklinesConfigTest extends IntegrationTestCase
{
    /**
     * @var Config
     */
    private $config;

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }

        $this->config = new Config();

        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function test_generateSparklineTooltip_noParams()
    {
        $this->assertSame('', $this->config->generateSparklineTooltip([]));
    }

    public function test_generateSparklineTooltip_onlyPeriod()
    {
        $this->assertSame('Each data point in the sparkline represents a week.', $this->config->generateSparklineTooltip(['period' => 'week']));
    }

    public function test_generateSparklineTooltip_periodAndDate()
    {
        $this->assertSame('Each data point in the sparkline represents a week. Period: Feb 2 – May 5, 2022.', $this->config->generateSparklineTooltip(['period' => 'week', 'date' => '2022-02-02,2022-05-05']));
    }

    public function test_generateSparklineTooltip_periodAndDateAndComparison()
    {
        $tooltip = $this->config->generateSparklineTooltip([
            'period' => 'week', 'date' => '2022-02-02,2022-05-05',
            'comparePeriods' => ['week', 'week'], 'compareDates' => ['2021-02-02,2021-05-05', '2020-02-02,2020-05-05']
        ]);

        $expected = 'Each data point in the sparkline represents a week. Period: Feb 2 – May 5, 2022. Period 2: Feb 2 – May 5, 2021. Period 3: Feb 2 – May 5, 2020.';
        $this->assertSame($expected, $tooltip);
    }

    public function test_areSparklinesLinkable_byDefaultSparklinesAreLinkable()
    {
        $this->assertTrue($this->config->areSparklinesLinkable());
    }

    public function test_setNotLinkableWithAnyEvolutionGraph_areSparklinesLinkable_sparklinesCanBeMadeNotLinkable()
    {
        $this->config->setNotLinkableWithAnyEvolutionGraph();
        $this->assertFalse($this->config->areSparklinesLinkable());
    }

    public function test_addSparkline_shouldAddAMinimalSparklineWithOneValueAndUseDefaultOrder()
    {
        $this->config->addSparkline($this->sparklineParams(), $value = 10, $description = 'Visits');

        $expectedSparkline = array(
            'url' => '?period=day&date=2012-03-06,2012-04-04&idSite=1&module=CoreHome&action=renderMe&viewDataTable=sparkline',
            'tooltip' => 'Each data point in the sparkline represents a day. Period: Wed, Apr 4.',
            'metrics' => array (
                '' => [
                    array ('value' => 10, 'description' => 'Visits', 'column' => ''),
                ],
            ),
            'order' => 999,
            'title' => null,
            'group' => '',
            'seriesIndices' => null,
            'graphParams' => null,
        );

        $this->assertSame(array($expectedSparkline), $this->config->getSortedSparklines()['']);
    }

    public function test_addSparkline_shouldAddAMinimalSparklineWithOneValueAndUseDefaultOrderWithColumn()
    {
        $params = $this->sparklineParams();
        $params['columns'] = 'nb_visits';
        $this->config->addSparkline($params, $value = 10, $description = 'Visits');

        $expectedSparkline = array('value' => 10, 'description' => 'Visits', 'column' => 'nb_visits');

        $sparklines = $this->config->getSortedSparklines();
        $this->assertSame(array($expectedSparkline), $sparklines[''][0]['metrics']['']);
    }

    public function test_addSparkline_shouldAddSparklineWithMultipleValues()
    {
        $this->config->addSparkline($this->sparklineParams(), $values = array(10, 20), $description = array('Visits', 'Actions'));

        $sparklines = $this->config->getSortedSparklines();

        $this->assertSame(array (
                array ('value' => 10, 'description' => 'Visits', 'column' => ''),
                array ('value' => 20, 'description' => 'Actions', 'column' => ''),
            ), $sparklines[''][0]['metrics']['']);
    }

    public function test_addSparkline_shouldAddSparklinesMultipleValuesWithColumns()
    {
        $params = $this->sparklineParams();
        $params['columns'] = array('nb_visits', 'nb_actions');

        $this->config->addSparkline($params, $values = array(10, 20), $description = array('Visits', 'Actions'));

        $expectedSparkline = array(
            array ('value' => 10, 'description' => 'Visits', 'column' => 'nb_visits'),
            array ('value' => 20, 'description' => 'Actions', 'column' => 'nb_actions')
        );

        $sparklines = $this->config->getSortedSparklines();
        $this->assertSame($expectedSparkline, $sparklines[''][0]['metrics']['']);
    }

    public function test_addSparkline_shouldThrowAnException_IfValuesDoesNotMatchAmountOfDescriptions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Values: 10, 20, 30 Descriptions: Visits, Actions');

        $this->config->addSparkline($this->sparklineParams(), $values = array(10, 20, 30), $description = array('Visits', 'Actions'));
    }

    public function test_addSparkline_shouldAddEvolution()
    {
        $evolution = array('currentValue' => 10, 'pastValue' => 21,
                            'tooltip' => '1 visit compared to 2 visits');
        $this->config->addSparkline($this->sparklineParams(), $value = 10, $description = 'Visits', $evolution);

        $sparklines = $this->config->getSortedSparklines();

        $this->assertSame(array (
            'percent'  => '-52.4%',
            'isLowerValueBetter' => false,
            'tooltip' => '1 visit compared to 2 visits',
            'trend' => -11
        ), $sparklines[''][0]['evolution']);
    }

    public function test_addSparkline_shouldAddEvolutionWhereLowerValueIsBetter()
    {
        $evolution = array('currentValue' => 20, 'pastValue' => 41,
                            'tooltip' => '2 bounces compared to 1 bounce',
                            'isLowerValueBetter' => true);
        $this->config->addSparkline($this->sparklineParams(), $value = 10, $description = 'Bounces', $evolution);

        $sparklines = $this->config->getSortedSparklines();

        $this->assertSame(array (
            'percent'  => '-51.2%',
            'isLowerValueBetter' => true,
            'tooltip' => '2 bounces compared to 1 bounce',
            'trend' => -21
        ), $sparklines[''][0]['evolution']);
    }

    public function test_addSparkline_shouldAddOrder()
    {
        $this->config->addSparkline($this->sparklineParams(), $value = 10, $description = 'Visits', $evolution = null, $order = '42');

        $sparklines = $this->config->getSortedSparklines();

        $this->assertSame(42, $sparklines[''][0]['order']);
    }

    public function test_addSparkline_shouldBeAbleToBuildSparklineUrlBasedOnGETparams()
    {
        $oldGet = $_GET;
        $_GET = $this->sparklineParams();
        $this->config->addSparkline(array('columns' => 'nb_visits'), $value = 10, $description = 'Visits');
        $_GET = $oldGet;

        $sparklines = $this->config->getSortedSparklines();

        $this->assertSame('?columns=nb_visits&viewDataTable=sparkline&date=2012-03-06,2012-04-04&period=day', $sparklines[''][0]['url']);
    }

    public function test_addSparkline_shouldAddSparklinesWithGroups()
    {
        $this->config->addSparkline($this->sparklineParams(), $value = 10, $description = 'Visits', $evolution = null, $order = '4', $title = 'title1', $group = 'one');
        $this->config->addSparkline($this->sparklineParams(), $value = 11, $description = 'Visits1', $evolution = null, $order = '1', $title = 'title2', $group = 'one');
        $this->config->addSparkline($this->sparklineParams(), $value = 12, $description = 'Visits2', $evolution = null, $order = '3', $title = 'title3', $group = 'two');
        $this->config->addSparkline($this->sparklineParams(), $value = 13, $description = 'Visits3', $evolution = null, $order = '6', $title = 'title4', $group = 'two');

        $sparklines = $this->config->getSortedSparklines();
        $expectedSparklines = [
            'one' => [
                [
                    'url' => '?period=day&date=2012-03-06,2012-04-04&idSite=1&module=CoreHome&action=renderMe&viewDataTable=sparkline',
                    'tooltip' => 'Each data point in the sparkline represents a day. Period: Wed, Apr 4.',
                    'metrics' => [
                        '' => [
                            0 => [
                                'value' => 11,
                                'description' => 'Visits1',
                                'column' => '',
                            ],
                        ],
                    ],
                    'order' => 1,
                    'title' => 'title2',
                    'group' => 'one',
                    'seriesIndices' => null,
                    'graphParams' => null,
                ],
                [
                    'url' => '?period=day&date=2012-03-06,2012-04-04&idSite=1&module=CoreHome&action=renderMe&viewDataTable=sparkline',
                    'tooltip' => 'Each data point in the sparkline represents a day. Period: Wed, Apr 4.',
                    'metrics' => [
                        '' => [
                            0 => [
                                'value' => 10,
                                'description' => 'Visits',
                                'column' => '',
                            ],
                        ],
                    ],
                    'order' => 4,
                    'title' => 'title1',
                    'group' => 'one',
                    'seriesIndices' => null,
                    'graphParams' => null,
                ],
            ],
            'two' => [
                [
                    'url' => '?period=day&date=2012-03-06,2012-04-04&idSite=1&module=CoreHome&action=renderMe&viewDataTable=sparkline',
                    'tooltip' => 'Each data point in the sparkline represents a day. Period: Wed, Apr 4.',
                    'metrics' => [
                        '' => [
                            0 => [
                                'value' => 12,
                                'description' => 'Visits2',
                                'column' => '',
                            ],
                        ],
                    ],
                    'order' => 3,
                    'title' => 'title3',
                    'group' => 'two',
                    'seriesIndices' => null,
                    'graphParams' => null,
                ],
                [
                    'url' => '?period=day&date=2012-03-06,2012-04-04&idSite=1&module=CoreHome&action=renderMe&viewDataTable=sparkline',
                    'tooltip' => 'Each data point in the sparkline represents a day. Period: Wed, Apr 4.',
                    'metrics' => [
                        '' => [
                            0 => [
                                'value' => 13,
                                'description' => 'Visits3',
                                'column' => '',
                            ],
                        ],
                    ],
                    'order' => 6,
                    'title' => 'title4',
                    'group' => 'two',
                    'seriesIndices' => null,
                    'graphParams' => null,
                ],
            ],
        ];

        $this->assertSame($expectedSparklines, $sparklines);
    }

    public function test_addSparkline_shouldAddSparklineMetricsWithGroups()
    {
        $metricInfos = [
            [
                'value' => 'v1',
                'description' => 'd1',
                'group' => 'g1',
            ],
            [
                'value' => 'v2',
                'description' => 'd3',
                'group' => 'g1',
            ],
            [
                'value' => 'v3',
                'description' => 'd3',
                'group' => 'g3',
            ],
            [
                'value' => 'v4',
                'description' => 'd4',
                'group' => 'g1',
            ],
        ];
        $this->config->addSparkline($this->sparklineParams(), $metricInfos, $description = null);

        $sparklines = $this->config->getSortedSparklines();
        $expectedSparklines = [
            '' => [
                [
                    'url' => '?period=day&date=2012-03-06,2012-04-04&idSite=1&module=CoreHome&action=renderMe&viewDataTable=sparkline',
                    'tooltip' => 'Each data point in the sparkline represents a day. Period: Wed, Apr 4.',
                    'metrics' => [
                        'g1' => [
                            0 => [
                                'value' => 'v1',
                                'description' => 'd1',
                                'group' => 'g1',
                                'column' => '',
                            ],
                            1 => [
                                'value' => 'v2',
                                'description' => 'd3',
                                'group' => 'g1',
                                'column' => '',
                            ],
                            2 => [
                                'value' => 'v4',
                                'description' => 'd4',
                                'group' => 'g1',
                                'column' => '',
                            ],
                        ],
                        'g3' => [
                            0 => [
                                'value' => 'v3',
                                'description' => 'd3',
                                'group' => 'g3',
                                'column' => '',
                            ],
                        ],
                    ],
                    'order' => 999,
                    'title' => null,
                    'group' => '',
                    'seriesIndices' => null,
                    'graphParams' => null,
                ],
            ],
        ];

        $this->assertSame($expectedSparklines, $sparklines);
    }

    private function sparklineParams($params = array())
    {
        $params['period'] = 'day';
        $params['date']   = '2012-04-04';
        $params['idSite'] = '1';
        $params['module'] = 'CoreHome';
        $params['action'] = 'renderMe';

        return $params;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
