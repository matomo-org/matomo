<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Integration;

use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines\Config;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Translate;

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

    public function setUp()
    {
        parent::setUp();
        FakeAccess::$superUser = true;

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }

        $this->config = new Config();

        Translate::loadAllTranslations();
    }

    public function tearDown()
    {
        Translate::reset();

        parent::tearDown();
    }

    public function test_addSparkline_shouldAddAMinimalSparklineWithOneValueAndUseDefaultOrder()
    {
        $this->config->addSparkline($this->sparklineParams(), $value = 10, $description = 'Visits');

        $expectedSparkline = array(
            'url' => '?period=day&date=2012-03-06,2012-04-04&idSite=1&module=CoreHome&action=renderMe&viewDataTable=sparkline',
            'metrics' => array (
                array ('column' => '', 'value' => 10, 'description' => 'Visits'),
            ),
            'order' => 999
        );

        $this->assertSame(array($expectedSparkline), $this->config->getSortedSparklines());
    }

    public function test_addSparkline_shouldAddAMinimalSparklineWithOneValueAndUseDefaultOrderWithColumn()
    {
        $params = $this->sparklineParams();
        $params['columns'] = 'nb_visits';
        $this->config->addSparkline($params, $value = 10, $description = 'Visits');

        $expectedSparkline = array('column' => 'nb_visits', 'value' => 10, 'description' => 'Visits');

        $sparklines = $this->config->getSortedSparklines();
        $this->assertSame(array($expectedSparkline), $sparklines[0]['metrics']);
    }

    public function test_addSparkline_shouldAddSparklineWithMultipleValues()
    {
        $this->config->addSparkline($this->sparklineParams(), $values = array(10, 20), $description = array('Visits', 'Actions'));

        $sparklines = $this->config->getSortedSparklines();

        $this->assertSame(array (
                array ('column' => '', 'value' => 10, 'description' => 'Visits'),
                array ('column' => '', 'value' => 20, 'description' => 'Actions'),
            ), $sparklines[0]['metrics']);
    }

    public function test_addSparkline_shouldAddSparklinesMultipleValuesWithColumns()
    {
        $params = $this->sparklineParams();
        $params['columns'] = array('nb_visits', 'nb_actions');

        $this->config->addSparkline($params, $values = array(10, 20), $description = array('Visits', 'Actions'));

        $expectedSparkline = array(
            array ('column' => 'nb_visits', 'value' => 10, 'description' => 'Visits'),
            array ('column' => 'nb_actions', 'value' => 20, 'description' => 'Actions')
        );

        $sparklines = $this->config->getSortedSparklines();
        $this->assertSame($expectedSparkline, $sparklines[0]['metrics']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Values: 10, 20, 30 Descriptions: Visits, Actions
     */
    public function test_addSparkline_shouldThrowAnException_IfValuesDoesNotMatchAmountOfDescriptions()
    {
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
             'tooltip' => '1 visit compared to 2 visits'
        ), $sparklines[0]['evolution']);
    }

    public function test_addSparkline_shouldAddOrder()
    {
        $this->config->addSparkline($this->sparklineParams(), $value = 10, $description = 'Visits', $evolution = null, $order = '42');

        $sparklines = $this->config->getSortedSparklines();

        $this->assertSame(42, $sparklines[0]['order']);
    }

    public function test_addSparkline_shouldBeAbleToBuildSparklineUrlBasedOnGETparams()
    {
        $oldGet = $_GET;
        $_GET = $this->sparklineParams();
        $this->config->addSparkline(array('columns' => 'nb_visits'), $value = 10, $description = 'Visits');
        $_GET = $oldGet;

        $sparklines = $this->config->getSortedSparklines();

        $this->assertSame('?columns=nb_visits&viewDataTable=sparkline&date=2012-03-06,2012-04-04', $sparklines[0]['url']);
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
