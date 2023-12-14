<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\ComputedMetric;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ComputedMetric
 * @group ComputedMetricTest
 */
class ComputedMetricTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();

        Fixture::createWebsite('2015-01-01 00:00:00');
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        parent::tearDown();
    }

    private function makeMetric($metric1, $metric2, $aggregation)
    {
        return new ComputedMetric($metric1, $metric2, $aggregation);
    }

    /**
     * @dataProvider getFormatValueProvider
     */
    public function test_formatValue($type, $value, $expected)
    {
        $metric = $this->makeMetric('bonuce_count', 'nb_visits', ComputedMetric::AGGREGATION_AVG);

        $formatter = new Formatter();
        $metric->setType($type);

        $table = new DataTable();
        $table->setMetadata('site', new Site(1));
        $metric->beforeFormat(null, $table);
        $formatted = $metric->format($value, $formatter);

        $this->assertEquals($expected, $formatted);
    }

    public function getFormatValueProvider()
    {
        return array(
            array($type = Dimension::TYPE_NUMBER, $value = 5.354, $expected = 5.4),
            array($type = Dimension::TYPE_FLOAT, $value = 5.354, $expected = 5.35),
            array($type = Dimension::TYPE_MONEY, $value = 5.392, $expected = '$5.39'),
            array($type = Dimension::TYPE_PERCENT, $value = 0.343, $expected = '34.3%'),
            array($type = Dimension::TYPE_DURATION_S, $value = 121, $expected = '2 min 1s'),
            array($type = Dimension::TYPE_DURATION_MS, $value = 392, $expected = '0.39s'),
            array($type = Dimension::TYPE_BYTE, $value = 392, $expected = '392 B'),
        );
    }

    public function test_getName()
    {
        $metric = $this->makeMetric('bounces', 'nb_visits', ComputedMetric::AGGREGATION_AVG);
        $this->assertSame('avg_bounces_per_visits', $metric->getName());

        $metric = $this->makeMetric('bounces', 'nb_uniq_visits', ComputedMetric::AGGREGATION_AVG);
        $this->assertSame('avg_bounces_per_uniq_visits', $metric->getName());

        $metric = $this->makeMetric('sum_bounces', 'nb_uniq_visits', ComputedMetric::AGGREGATION_AVG);
        $this->assertSame('avg_sum_bounces_per_uniq_visits', $metric->getName());

        $metric = $this->makeMetric('bounces', 'nb_visits', ComputedMetric::AGGREGATION_RATE);
        $this->assertSame('bounces_visits_rate', $metric->getName());

        $metric = $this->makeMetric('bounces', 'nb_uniq_visits', ComputedMetric::AGGREGATION_RATE);
        $this->assertSame('bounces_uniq_visits_rate', $metric->getName());

        $metric = $this->makeMetric('sum_bounces', 'nb_uniq_visits', ComputedMetric::AGGREGATION_RATE);
        $this->assertSame('sum_bounces_uniq_visits_rate', $metric->getName());
    }

    public function test_setName()
    {
        $metric = $this->makeMetric('bounces', 'nb_visits', ComputedMetric::AGGREGATION_AVG);
        $metric->setName('avg_bounces');
        $this->assertSame('avg_bounces', $metric->getName());
    }

    public function test_getTranslatedName()
    {
        $metric = $this->makeMetric('bounce_count', 'nb_visits', ComputedMetric::AGGREGATION_AVG);
        $this->assertSame('Avg. Actions In Visit per Visit', $metric->getTranslatedName());

        $metric = $this->makeMetric('bounce_count', 'nb_visits', ComputedMetric::AGGREGATION_RATE);
        $this->assertSame('Bounces Rate', $metric->getTranslatedName());
    }

    public function test_getDocumentation()
    {
        $metric = $this->makeMetric('bounce_count', 'nb_visits', ComputedMetric::AGGREGATION_AVG);
        $this->assertSame('Average value of "Actions In Visit" per "Visits".', $metric->getDocumentation());

        $metric = $this->makeMetric('bounce_count', 'nb_visits', ComputedMetric::AGGREGATION_RATE);
        $this->assertSame('The ratio of "Actions In Visit" out of all "Visits".', $metric->getDocumentation());
    }

    public function test_getDependentMetrics()
    {
        $metric = $this->makeMetric('bounce_count', 'nb_visits', ComputedMetric::AGGREGATION_AVG);
        $this->assertSame(array('bounce_count', 'nb_visits'), $metric->getDependentMetrics());
    }

    public function test_setCategory()
    {
        $metric = $this->makeMetric('bounce_count', 'nb_visits', ComputedMetric::AGGREGATION_AVG);
        $metric->setCategory('123');
        $this->assertSame('123', $metric->getCategoryId());
    }

}
