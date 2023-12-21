<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Columns;

use Piwik\Columns\Dimension;
use Piwik\Columns\DimensionMetricFactory;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;
use Piwik\Plugins\UserCountry\Columns\Country;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class DimensionMetricFactoryTest extends IntegrationTestCase
{
    /** @var  Dimension */
    private $country;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();

        $this->country = new Country();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        parent::tearDown();
    }

    private function makeFactory($dimension)
    {
        return new DimensionMetricFactory($dimension);
    }

    public function test_createMetric_count()
    {
        $factory = $this->makeFactory($this->country);
        $metric = $factory->createMetric(ArchivedMetric::AGGREGATION_COUNT);

        $this->assertSame('nb_usercountry_country', $metric->getName());
        $this->assertSame('Countries', $metric->getTranslatedName());
        $this->assertSame('The number of Countries', $metric->getDocumentation());
        $this->assertSame('UserCountry_VisitLocation', $metric->getCategoryId());
        $this->assertSame('count(log_visit.location_country)', $metric->getQuery());
    }

    public function test_createMetric_uniqueCount()
    {
        $factory = $this->makeFactory($this->country);
        $metric = $factory->createMetric(ArchivedMetric::AGGREGATION_UNIQUE);

        $this->assertSame('nb_uniq_usercountry_country', $metric->getName());
        $this->assertSame('Unique Countries', $metric->getTranslatedName());
        $this->assertSame('The unique number of Countries', $metric->getDocumentation());
        $this->assertSame('UserCountry_VisitLocation', $metric->getCategoryId());
        $this->assertSame('count(distinct log_visit.location_country)', $metric->getQuery());
    }

    public function test_createMetric_sum()
    {
        $factory = $this->makeFactory($this->country);
        $metric = $factory->createMetric(ArchivedMetric::AGGREGATION_SUM);

        $this->assertSame('sum_usercountry_country', $metric->getName());
        $this->assertSame('Total Countries', $metric->getTranslatedName());
        $this->assertSame('The total number (sum) of Countries', $metric->getDocumentation());
        $this->assertSame('UserCountry_VisitLocation', $metric->getCategoryId());
        $this->assertSame('sum(log_visit.location_country)', $metric->getQuery());
    }

    public function test_createMetric_min()
    {
        $factory = $this->makeFactory($this->country);
        $metric = $factory->createMetric(ArchivedMetric::AGGREGATION_MIN);

        $this->assertSame('min_usercountry_country', $metric->getName());
        $this->assertSame('Min Countries', $metric->getTranslatedName());
        $this->assertSame('The minimum value for Countries', $metric->getDocumentation());
        $this->assertSame('UserCountry_VisitLocation', $metric->getCategoryId());
        $this->assertSame('min(log_visit.location_country)', $metric->getQuery());
    }

    public function test_createMetric_max()
    {
        $factory = $this->makeFactory($this->country);
        $metric = $factory->createMetric(ArchivedMetric::AGGREGATION_MAX);

        $this->assertSame('max_usercountry_country', $metric->getName());
        $this->assertSame('Max Countries', $metric->getTranslatedName());
        $this->assertSame('The maximum value for Countries', $metric->getDocumentation());
        $this->assertSame('UserCountry_VisitLocation', $metric->getCategoryId());
        $this->assertSame('max(log_visit.location_country)', $metric->getQuery());
    }

    public function test_createMetric_withValue()
    {
        $factory = $this->makeFactory($this->country);
        $metric = $factory->createMetric(ArchivedMetric::AGGREGATION_COUNT_WITH_NUMERIC_VALUE);

        $this->assertSame('nb_with_usercountry_country', $metric->getName());
        $this->assertSame('Entries with Country', $metric->getTranslatedName());
        $this->assertSame('The number of entries that have a value set for Country', $metric->getDocumentation());
        $this->assertSame('UserCountry_VisitLocation', $metric->getCategoryId());
        $this->assertSame('sum(if(log_visit.location_country > 0, 1, 0))', $metric->getQuery());
    }


    public function test_createCustomMetric()
    {
        $factory = $this->makeFactory($this->country);
        $metric = $factory->createCustomMetric($name = 'sum_times_10', $translated = 'MyMetric', $aggregation = 'sum(%s) * 10', $documentation = 'FoobarBaz');

        $this->assertSame($name, $metric->getName());
        $this->assertSame($translated, $metric->getTranslatedName());
        $this->assertSame($documentation, $metric->getDocumentation());
        $this->assertSame('UserCountry_VisitLocation', $metric->getCategoryId());
        $this->assertSame('sum(log_visit.location_country) * 10', $metric->getQuery());
    }

    public function test_createComputedMetric_average()
    {
        $factory = $this->makeFactory($this->country);
        $metric = $factory->createComputedMetric($metricName1 = 'bounce_count', 'nb_visits', ComputedMetric::AGGREGATION_AVG);

        $this->assertSame('avg_bounce_count_per_visits', $metric->getName());
        $this->assertSame('Avg. Actions In Visit per Visit', $metric->getTranslatedName());
        $this->assertSame('Average value of "Actions In Visit" per "Visits".', $metric->getDocumentation());
        $this->assertSame('UserCountry_VisitLocation', $metric->getCategoryId());
        $this->assertCount(2, $metric->getDependentMetrics());
    }

    public function test_createComputedMetric_rate()
    {
        $factory = $this->makeFactory($this->country);
        $metric = $factory->createComputedMetric($metricName1 = 'bounce_count', 'nb_visits', ComputedMetric::AGGREGATION_RATE);

        $this->assertSame('bounce_count_visits_rate', $metric->getName());
        $this->assertSame('Bounces Rate', $metric->getTranslatedName());
        $this->assertSame('The ratio of "Actions In Visit" out of all "Visits".', $metric->getDocumentation());
        $this->assertSame('UserCountry_VisitLocation', $metric->getCategoryId());
        $this->assertCount(2, $metric->getDependentMetrics());
    }
}
