<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\TrackingSpamPrevention\Configuration;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TrackingSpamPrevention
 * @group ConfigurationTest
 * @group Configuration
 * @group Plugins
 */
class ConfigurationTest extends IntegrationTestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function setUp(): void
    {
        parent::setUp();

        $this->configuration = new Configuration();
        $this->configuration->install();
    }

    public function test_shouldInstallConfig()
    {
        // reset the section as the local config of the instance running the tests may contain different values
        Config::getInstance()->TrackingSpamPrevention = [];

        $this->configuration->install();

        $configs = Config::getInstance()->TrackingSpamPrevention;
        $this->assertEquals(array(
            'block_cloud_sync_throw_exception_on_error' => 0,
        ), $configs);
    }

    public function test_defaultBlockList_containsNewlyAddedHostingProviders()
    {
        $providers = Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS;

        // added in 5.1.0 from the detection-coverage analysis - guards against accidental removal
        $expected = [
            'tencent',
            'fdcservers',
            'ace data centers',
            'egihosting',
            'hangzhou alibaba advertising',
            'sharktech',
            'dmit cloud services',
        ];

        foreach ($expected as $provider) {
            $this->assertContains($provider, $providers, "'$provider' should be in the default block list");
        }
    }

    public function test_defaultBlockList_entriesAreLowercaseAndUnique()
    {
        $providers = Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS;

        $this->assertSame(array_values(array_unique($providers)), $providers, 'default block list must not contain duplicates');
        foreach ($providers as $provider) {
            $this->assertSame(mb_strtolower($provider), $provider, "'$provider' must be lowercase (matching is case-insensitive but entries are compared lowercased)");
        }
    }

    public function test_shouldThrowExceptionOnIpRangeSync_default()
    {
        $this->assertFalse($this->configuration->shouldThrowExceptionOnIpRangeSync());
    }

    public function test_shouldThrowExceptionOnIpRangeSync_enabled()
    {
        Config::getInstance()->TrackingSpamPrevention[Configuration::KEY_RANGE_THROW_EXCEPTION] = 1;
        $this->assertTrue($this->configuration->shouldThrowExceptionOnIpRangeSync());
    }

    public function test_getIpRangesAlwaysAllowed_byDefault()
    {
        $this->assertSame([], $this->configuration->getIpRangesAlwaysAllowed());
    }

    public function test_getIpRangesAlwaysAllowed_delegatesToSystemSetting()
    {
        StaticContainer::get(SystemSettings::class)->ipAllowList->setValue(['10.12.13.14/32', 'f::f/52', '11.12.13.14/21']);

        $this->assertSame(['10.12.13.14/32', 'f::f/52', '11.12.13.14/21'], $this->configuration->getIpRangesAlwaysAllowed());
    }
}
