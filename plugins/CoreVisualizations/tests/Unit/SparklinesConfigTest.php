<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Unit;

use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines\Config;

/**
 * @group CoreVisualizations
 * @group SparklinesConfigTest
 * @group Sparklines
 * @group Plugins
 */
class SparklinesConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $config;

    public function setUp(): void
    {
        $this->config = new Config();
    }

    public function testHasSparklineMetricsShouldNotHaveSparklineMetricsByDefault()
    {
        $this->assertFalse($this->config->hasSparklineMetrics());
    }

    public function testHasSparklineMetricsShouldHaveSparklineMetricsIfAtLeastOneWasAdded()
    {
        $this->config->addSparklineMetric('nb_visits');

        $this->assertTrue($this->config->hasSparklineMetrics());
    }

    public function testGetSparklineMetricsShouldNotHaveSparklineMetricsByDefault()
    {
        $this->assertSame(array(), $this->config->getSparklineMetrics());
    }

    public function testAddSparklineMetricGetSparklineMetricsShouldReturnAllAddedSparklineMetrics()
    {
        $this->addFewSparklines();

        $this->assertSame(array(
            array('columns' => 'nb_visits', 'order' => null, 'graphParams' => null),
            array('columns' => 'nb_unique_visitors', 'order' => 99, 'graphParams' => null),
            array('columns' => array('nb_downloads', 'nb_outlinks'), 'order' => null, 'graphParams' => null),
        ), $this->config->getSparklineMetrics());
    }

    public function testRemoveSparklineMetricShouldRemoveMetricIfOnlySingleMetricIsGiven()
    {
        $this->addFewSparklines();

        $this->config->removeSparklineMetric('nb_unique_visitors');

        $this->assertSame(array(
            array('columns' => 'nb_visits', 'order' => null, 'graphParams' => null),
            array('columns' => array('nb_downloads', 'nb_outlinks'), 'order' => null, 'graphParams' => null),
        ), $this->config->getSparklineMetrics());
    }

    public function testRemoveSparklineMetricShouldRemoveMetricIfMultipleMetricsAreGiven()
    {
        $this->addFewSparklines();

        $this->config->removeSparklineMetric(array('nb_downloads', 'nb_outlinks'));

        $this->assertSame(array(
            array('columns' => 'nb_visits', 'order' => null, 'graphParams' => null),
            array('columns' => 'nb_unique_visitors', 'order' => 99, 'graphParams' => null),
        ), $this->config->getSparklineMetrics());
    }

    public function testReplaceSparklineMetricShouldBeAbleToReplaceColumnsIfSingleMetricIsGiven()
    {
        $this->addFewSparklines();

        $this->config->replaceSparklineMetric('nb_unique_visitors', '');

        $this->assertSame(array(
            array('columns' => 'nb_visits', 'order' => null, 'graphParams' => null),
            array('columns' => '', 'order' => 99, 'graphParams' => null),
            array('columns' => array('nb_downloads', 'nb_outlinks'), 'order' => null, 'graphParams' => null),
        ), $this->config->getSparklineMetrics());
    }

    public function testReplaceSparklineMetricShouldBeAbleToReplaceColumnsIfMultipleMetricsAreGiven()
    {
        $this->addFewSparklines();

        $this->config->replaceSparklineMetric(array('nb_downloads', 'nb_outlinks'), '');

        $this->assertSame(array(
            array('columns' => 'nb_visits', 'order' => null, 'graphParams' => null),
            array('columns' => 'nb_unique_visitors', 'order' => 99, 'graphParams' => null),
            array('columns' => '', 'order' => null, 'graphParams' => null),
        ), $this->config->getSparklineMetrics());
    }

    public function testAddPlaceholderGetSortedSparklines()
    {
        $this->config->addPlaceholder();
        $this->config->addPlaceholder($order = 10);
        $this->config->addPlaceholder();
        $this->config->addPlaceholder($order = 3);

        $this->assertSame(array(
            'placeholder3' => [['url' => '', 'metrics' => array(), 'order' => 3, 'group' => 'placeholder3']],
            'placeholder1' => [['url' => '', 'metrics' => array(), 'order' => 10, 'group' => 'placeholder1']],
            'placeholder0' => [['url' => '', 'metrics' => array(), 'order' => 999, 'group' => 'placeholder0']],
            'placeholder2' => [['url' => '', 'metrics' => array(), 'order' => 1001, 'group' => 'placeholder2']],
        ), $this->config->getSortedSparklines());
    }

    private function addFewSparklines()
    {
        $this->config->addSparklineMetric('nb_visits');
        $this->config->addSparklineMetric('nb_unique_visitors', 99);
        $this->config->addSparklineMetric(array('nb_downloads', 'nb_outlinks'));
    }
}
