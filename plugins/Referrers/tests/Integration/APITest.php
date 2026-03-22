<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Integration;

use Piwik\Option;
use Piwik\Plugins\Referrers\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Referrers
 * @group ApiTest
 * @group Plugins
 */
class APITest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var int
     */
    private $siteId;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->siteId = Fixture::createWebsite('2014-01-01 01:02:03');
        $this->api = API::getInstance();
    }

    public function tearDown(): void
    {
        Option::delete($this->getPresetOptionName());

        parent::tearDown();
    }

    public function testSaveCampaignUrlPresetReturnsSavedPreset(): void
    {
        $result = $this->api->saveCampaignUrlPreset(
            $this->siteId,
            'https://example.com/landing-page',
            'https://example.com/landing-page?mtm_campaign=Spring+sale',
            'Spring sale',
            'shoes',
            'newsletter',
            'email',
            'spring-2026',
            'hero-banner',
            'vip',
            'top-slot'
        );

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame('Spring sale', $result[0]['campaignName']);
        $this->assertSame('newsletter', $result[0]['campaignSource']);
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(true),
        ];
    }

    private function getPresetOptionName(): string
    {
        return 'Referrers.campaignUrlPresets.' . $this->siteId;
    }
}
