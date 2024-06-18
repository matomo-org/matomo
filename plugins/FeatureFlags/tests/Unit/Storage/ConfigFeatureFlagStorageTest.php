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
use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;
use Piwik\Plugins\FeatureFlags\Storage\ConfigFeatureFlagStorage;
use Piwik\Tests\Framework\Mock\FakeConfig;

class ConfigFeatureFlagStorageTest extends TestCase
{
    public function testIsFeatureActiveReturnsFalseIfConfigIsMissing(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->method('__get')->willThrowException(new \Exception());

        $sut = new ConfigFeatureFlagStorage($configMock);
        $mockFeature = $this->createMock(FeatureFlagInterface::class);

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
        $mockFeature = $this->createMock(FeatureFlagInterface::class);
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
        $mockFeature = $this->createMock(FeatureFlagInterface::class);
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
        $mockFeature = $this->createMock(FeatureFlagInterface::class);
        $mockFeature->method('getName')->willReturn('UnitTest');

        $this->assertFalse($sut->isFeatureActive($mockFeature));
    }

    public function testDisableFeatureFlagDoesntUpdateIfFeatureDoesntExists(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->never())->method('__set');
        $configMock->expects($this->never())->method('forceSave');

        $mockFeature = $this->createMock(FeatureFlagInterface::class);

        $sut = new ConfigFeatureFlagStorage($configMock);
        $sut->disableFeatureFlag($mockFeature);
    }

    public function testDisableFeatureFlagUpdatesConfigAndForcesSaveOfConfig(): void
    {
        $configMock = $this->getMockBuilder(FakeConfig::class)
            ->setMethodsExcept(['__get', '__set', '__construct'])
            ->setConstructorArgs([
                'configValues' => [
                    'FeatureFlags' =>
                    [
                        'TestFeature_feature' => 'enabled'
                    ]
                ]
            ])
            ->getMock();

        $configMock->expects($this->once())->method('forceSave');

        $mockFeature = $this->createMock(FeatureFlagInterface::class);
        $mockFeature->method('getName')->willReturn('TestFeature');

        $sut = new ConfigFeatureFlagStorage($configMock);
        $sut->disableFeatureFlag($mockFeature);

        $this->assertEquals(
            [
                'TestFeature_feature' => 'disabled'
            ],
            $configMock->FeatureFlags
        );
    }

    public function testEnableFeatureFlagUpdatesConfig(): void
    {
        $configMock = $this->getMockBuilder(FakeConfig::class)
            ->setMethodsExcept(['__get', '__set', '__construct'])
            ->setConstructorArgs([
                'configValues' => []
            ])
            ->getMock();

        $configMock->expects($this->once())->method('forceSave');

        $mockFeature = $this->createMock(FeatureFlagInterface::class);
        $mockFeature->method('getName')->willReturn('TestFeature');

        $sut = new ConfigFeatureFlagStorage($configMock);
        $sut->enableFeatureFlag($mockFeature);

        $this->assertEquals(
            [
                'TestFeature_feature' => 'enabled'
            ],
            $configMock->FeatureFlags
        );
    }

    public function testDeleteFeatureDoesNothingIfFeatureDoesntExist(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->never())->method('forceSave');

        $sut = new ConfigFeatureFlagStorage($configMock);
        $sut->deleteFeatureFlag('UnknownFeature');
    }

    public function testDeleteFeatureRemovesFlagFromConfig(): void
    {
        $configMock = $this->getMockBuilder(FakeConfig::class)
            ->setMethodsExcept(['__get', '__set', '__construct'])
            ->setConstructorArgs([
                'configValues' => [
                    'FeatureFlags' =>
                        [
                            'TestFeature_feature' => 'enabled'
                        ]
                ]
            ])
            ->getMock();
        $configMock->expects($this->once())->method('forceSave');

        $sut = new ConfigFeatureFlagStorage($configMock);
        $sut->deleteFeatureFlag('TestFeature');

        $this->assertEquals([], $configMock->FeatureFlags);
    }
}
