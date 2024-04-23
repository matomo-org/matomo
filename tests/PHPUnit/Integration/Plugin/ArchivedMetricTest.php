<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugins\UserCountry\Columns\Country;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ArchivedMetric
 * @group ArchivedMetricTest
 */
class ArchivedMetricTest extends IntegrationTestCase
{
    /**
     * @var Country
     */
    private $dimension;

    /**
     * @var ArchivedMetric
     */
    private $metric;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();

        Fixture::createWebsite('2015-01-01 00:00:00');

        $this->dimension = new Country();
        $this->metric = $this->makeMetric('%s');
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        parent::tearDown();
    }

    private function makeMetric($aggregation)
    {
        return new ArchivedMetric($this->dimension, $aggregation);
    }

    /**
     * @dataProvider getFormatValueProvider
     */
    public function testFormatValue($type, $value, $expected)
    {
        $formatter = new Formatter();
        $this->metric->setType($type);

        $table = new DataTable();
        $table->setMetadata('site', new Site(1));
        $this->metric->beforeFormat(null, $table);
        $formatted = $this->metric->format($value, $formatter);

        $this->assertEquals($expected, $formatted);
    }

    public function getFormatValueProvider()
    {
        return array(
            array($type = Dimension::TYPE_NUMBER, $value = 5.354, $expected = 5),
            array($type = Dimension::TYPE_FLOAT, $value = 5.354, $expected = 5.35),
            array($type = Dimension::TYPE_MONEY, $value = 5.392, $expected = '$5.39'),
            array($type = Dimension::TYPE_PERCENT, $value = 0.343, $expected = '34.3%'),
            array($type = Dimension::TYPE_DURATION_S, $value = 121, $expected = '2 min 1s'),
            array($type = Dimension::TYPE_DURATION_MS, $value = 392, $expected = '0.39s'),
            array($type = Dimension::TYPE_BYTE, $value = 3912, $expected = '3.8 K'),
        );
    }
    public function testGetQueryReturnsDefaultColumns()
    {
        $this->assertSame('log_visit.location_country', $this->metric->getQuery());
    }

    public function testGetQueryWhenNoAggregationSet()
    {
        $metric = $this->makeMetric('');
        $this->assertSame('log_visit.location_country', $metric->getQuery());
    }

    public function testGetQueryWhenAggregationSet()
    {
        $metric = $this->makeMetric('count(%s)');
        $this->assertSame('count(log_visit.location_country)', $metric->getQuery());
    }

    public function testSetQuery()
    {
        $this->metric->setQuery('count(log_visit.foobar) + 1');
        $this->assertSame('count(log_visit.foobar) + 1', $this->metric->getQuery());
    }

    public function testSetDimension()
    {
        $this->assertSame($this->dimension, $this->metric->getDimension());
    }

    public function testGetDbTableName()
    {
        $this->assertSame('log_visit', $this->metric->getDbTableName());
    }

    public function testSetCategoryGetCategoryId()
    {
        $this->assertSame('', $this->metric->getCategoryId());
        $this->metric->setCategory('General_Visitors');
        $this->assertSame('General_Visitors', $this->metric->getCategoryId());
    }
}
