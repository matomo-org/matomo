<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags\tests\Integration;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\FeatureFlags\Storage\ConfigFeatureFlagStorage;
use Piwik\Plugins\FeatureFlags\tests\Integration\FeatureFlags\FakeFeatureFlag;
use Piwik\Plugins\FeatureFlags\tests\Integration\FeatureFlags\FakeForcedEnabledFeatureFlag;
use Piwik\Tests\Framework\Mock\FakeConfig;
use Piwik\Tests\Framework\Mock\FakeLogger;

class FeatureFlagManagerTests extends TestCase
{
    public function testConfigStorageReadsFeatureFlagsCorrectly(): void
    {
        $config = new FakeConfig(['FeatureFlags' => ['NotReal_feature' => 'enabled']]);

        $configFeatureFlagStorage = new ConfigFeatureFlagStorage($config);

        $featureFlagManager = new FeatureFlagManager(
            [$configFeatureFlagStorage],
            new FakeLogger()
        );

        $this->assertTrue($featureFlagManager->isFeatureActive(FakeFeatureFlag::class));
    }

    public function testForcedFeatureStateOverridesDisabledConfig(): void
    {
        $config = new FakeConfig(['FeatureFlags' => ['ForcedEnabled_feature' => 'disabled']]);

        $configFeatureFlagStorage = new ConfigFeatureFlagStorage($config);

        $featureFlagManager = new FeatureFlagManager(
            [$configFeatureFlagStorage],
            new FakeLogger()
        );

        $this->assertTrue($featureFlagManager->isFeatureActive(FakeForcedEnabledFeatureFlag::class));
    }
}
