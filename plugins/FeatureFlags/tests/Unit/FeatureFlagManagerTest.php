<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\FeatureFlags\FeatureFlagStorageInterface;
use Piwik\Plugins\Marketplace\Api\Exception;

class FeatureFlagManagerTest extends TestCase
{
    public function testIsFeatureActiveReturnsFalseIfUnknownFeaturePassed()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new FeatureFlagManager([], [], $logger);

        $this->assertFalse($sut->isFeatureActive('UnknownFeature'));
    }

    public function testIsFeatureActiveLogsDebugMessageIfFeatureNotFound()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with('isFeatureActive failed due to not being configured in DI', [
                'featureFlag' => 'UnknownFeature'
            ]);

        $sut = new FeatureFlagManager([], [], $logger);

        $sut->isFeatureActive('UnknownFeature');
    }

    public function testIsFeatureActiveReturnsFalseIfClassPassedInDoesntImplementCorrectInterface()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new FeatureFlagManager([], [\Exception::class], $logger);

        $this->assertFalse($sut->isFeatureActive(Exception::class));
    }

    public function testIsFeatureActiveLogsDebugIfClassPassedInDoesntImplementCorrectInterface()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with('isFeatureActive failed due to class not implementing FeatureFlagInterface', [
                'featureFlag' => \Exception::class
            ]);

        $sut = new FeatureFlagManager([], [\Exception::class], $logger);

        $this->assertFalse($sut->isFeatureActive(\Exception::class));
    }

    /**
     * @dataProvider listOfStorages
     */
    public function testIsFeatureActiveOverridesBasedOnOrderOfStorage(array $storageResponses, bool $expectedOutcome): void
    {
        $storages = [];
        $logger = $this->createMock(LoggerInterface::class);

        foreach ($storageResponses as $storageResponse) {
            $mock = $this->createMock(FeatureFlagStorageInterface::class);
            $mock->method('isFeatureActive')->willReturn($storageResponse);
            $storages[] = $mock;
        }

        $mockFeature = $this->createMock(FeatureFlagInterface::class);

        $sut = new FeatureFlagManager(
            $storages,
            [
                get_class($mockFeature)
            ],
            $logger
        );

        $this->assertEquals($expectedOutcome, $sut->isFeatureActive(get_class($mockFeature)));
    }

    public function listOfStorages(): \Generator
    {
        yield [
            [
                // The return values for isFeatureActive on the storage
                true,
                false,
                true
            ],
            // Expected outcome
            true
        ];

        yield [
            [
                // The return values for isFeatureActive on the storage
                false,
                null,
                false
            ],
            // Expected outcome
            false
        ];

        yield [
            [
                // The return values for isFeatureActive on the storage
                null,
                null,
                true
            ],
            // Expected outcome
            true
        ];

        yield [
            [
                // The return values for isFeatureActive on the storage
                true,
                true,
                false
            ],
            // Expected outcome
            false
        ];

        yield [
            [
                // The return values for isFeatureActive on the storage
                true,
                true,
                null
            ],
            // Expected outcome
            true
        ];

        yield [
            [],
            // Expected outcome
            false
        ];
    }
}
