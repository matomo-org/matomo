<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags\tests\Unit\Storage;

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Plugins\FeatureFlags\FeatureFlag;
use Piwik\Plugins\FeatureFlags\Storage\ConfigFeatureFlagStorage;

class ConfigFeatureFlagStorageTest extends TestCase
{
    public function testIsFeatureActiveReturnsFalseIfConfigIsMissing(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->method('__get')->willThrowException(new \Exception());

        $sut = new ConfigFeatureFlagStorage($configMock);
        $mockFeature = $this->createMock(FeatureFlag::class);

        $this->assertFalse($sut->isFeatureActive($mockFeature));
    }

    public function testIsFeatureActiveReturnsNullIfFeatureIsntConfigured(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->method('__get')->willReturn([
            'SomeOther_feature' => 'enabled',
            'AnotherOne_feature' => 'disabled'
        ]);

        $sut = new ConfigFeatureFlagStorage($configMock);
        $mockFeature = $this->createMock(FeatureFlag::class);
        $mockFeature->method('getName')->willReturn('NotSet');

        $this->assertNull($sut->isFeatureActive($mockFeature));
    }

    public function testIsFeatureActiveReturnsTrueIfFeatureIsConfiguredAndEnabled(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->method('__get')->willReturn([
            'Other_feature' => 'disabled',
            'UnitTest_feature' => 'enabled'
        ]);

        $sut = new ConfigFeatureFlagStorage($configMock);
        $mockFeature = $this->createMock(FeatureFlag::class);
        $mockFeature->method('getName')->willReturn('UnitTest');

        $this->assertTrue($sut->isFeatureActive($mockFeature));
    }

    public function testIsFeatureActiveReturnsFalseIfFeatureIsConfiguredButNotEnabled(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->method('__get')->willReturn([
            'Other_feature' => 'disabled',
            'UnitTest_feature' => 'disabled'
        ]);

        $sut = new ConfigFeatureFlagStorage($configMock);
        $mockFeature = $this->createMock(FeatureFlag::class);
        $mockFeature->method('getName')->willReturn('UnitTest');

        $this->assertFalse($sut->isFeatureActive($mockFeature));
    }
}
