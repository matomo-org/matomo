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
use Piwik\Plugins\FeatureFlags\tests\Integration\Features\FakeFeature;
use Piwik\Tests\Framework\Mock\FakeConfig;

class FeatureFlagTests extends TestCase
{
    public function testConfigStorageReadsFeatureFlagsCorrectly(): void
    {
        $config = new FakeConfig(['FeatureFlags' => ['NotReal_feature' => 'enabled']]);

        $configFeatureFlagStorage = new ConfigFeatureFlagStorage($config);

        $featureFlagManager = new FeatureFlagManager([$configFeatureFlagStorage]);

        $this->assertTrue($featureFlagManager->isFeatureActive(new FakeFeature()));
    }
}
