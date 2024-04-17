<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Columns;

use Piwik\Columns\Dimension;
use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Plugin\Segment;
use Piwik\Plugins\UserCountry\Columns\Country;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group DimensionSegmentFactory
 */
class DimensionSegmentFactoryTest extends IntegrationTestCase
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
        return new DimensionSegmentFactory($dimension);
    }

    public function test_createSegment()
    {
        $factory = $this->makeFactory($this->country);
        $segment = $factory->createSegment();

        $this->assertSame('countryCode', $segment->getSegment());
        $this->assertSame('Country', $segment->getName());
        $this->assertSame('UserCountry_VisitLocation', $segment->getCategoryId());
        $this->assertSame(Dimension::TYPE_DIMENSION, $segment->getType());
    }

    public function test_createSegment_predefined()
    {
        $factory = $this->makeFactory($this->country);
        $segment = new Segment();
        $segment->setName('My Name');
        $segment->setCategory('My Category');
        $segment = $factory->createSegment($segment);

        $this->assertSame('countryCode', $segment->getSegment());
        $this->assertSame('My Name', $segment->getName());
        $this->assertSame('My Category', $segment->getCategoryId());
        $this->assertSame(Dimension::TYPE_DIMENSION, $segment->getType());
    }
}
