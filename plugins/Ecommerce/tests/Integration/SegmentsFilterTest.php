<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\tests\Integration;

use Piwik\Cache;
use Piwik\Config;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Policy\CnilPolicy;
use Piwik\Segment\SegmentsList;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Ecommerce
 * @group EcommerceSegmentsFilter
 * @group Integration
 */
class SegmentsFilterTest extends IntegrationTestCase
{
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->idSite = Fixture::createWebsite('2023-01-01 00:00:00', $ecommerce = 1);
    }

    public function tearDown(): void
    {
        $this->disablePrivacyCompliance();
        unset($_GET['idSite'], $_POST['idSite']);
        Cache::getTransientCache()->flushAll();

        parent::tearDown();
    }

    public function testSegmentsNotFilteredWhenComplianceDisabled(): void
    {
        $this->disablePrivacyCompliance();

        $segmentsList = $this->getSegmentsListForSite();

        $this->assertNotNull($segmentsList->getSegment('orderId'));
        $this->assertNotNull($segmentsList->getSegment('revenueOrder'));
        $this->assertNotNull($segmentsList->getSegment('productPrice'));
        $this->assertNotNull($segmentsList->getSegment('productName'));
        $this->assertNotNull($segmentsList->getSegment('productSku'));
    }

    public function testSegmentsFilteredWhenComplianceEnabled(): void
    {
        $this->enablePrivacyCompliance();

        $segmentsList = $this->getSegmentsListForSite();

        $this->assertNull($segmentsList->getSegment('orderId'));
        $this->assertNull($segmentsList->getSegment('revenueOrder'));
        $this->assertNull($segmentsList->getSegment('productPrice'));
        $this->assertNull($segmentsList->getSegment('productName'));
        $this->assertNull($segmentsList->getSegment('productSku'));
    }

    private function getSegmentsListForSite(): SegmentsList
    {
        Cache::getTransientCache()->flushAll();

        $_GET['idSite'] = $this->idSite;

        return SegmentsList::get();
    }

    private function enablePrivacyCompliance(): void
    {
        $featureFlag = new PrivacyCompliance();
        Config::getInstance()->FeatureFlags = [$featureFlag->getName() . '_feature' => 'enabled'];
        CnilPolicy::setActiveStatus(null, true);
    }

    private function disablePrivacyCompliance(): void
    {
        $featureFlag = new PrivacyCompliance();
        Config::getInstance()->FeatureFlags = [$featureFlag->getName() . '_feature' => 'disabled'];
        CnilPolicy::setActiveStatus(null, false);
    }
}
