<?php

namespace Piwik\Tests\Unit\HealthCheck\Responses;

use PHPUnit\Framework\TestCase;
use Piwik\HealthCheck\Responses\HealthCheckResponse;
use Piwik\HealthCheck\Responses\HealthCheckSingleResponse;

/**
 * @group Core
 * @group HealthCheck2
 * @group HealthCheckResponse
 */
final class HealthCheckResponseTest extends TestCase
{
    public function testHasPassedReturnsTrueIfAllTestsPassed(): void
    {
        $checkSingleResponses = [
            new HealthCheckSingleResponse('name1', 'PASS'),
            new HealthCheckSingleResponse('name2', 'PASS'),
        ];

        $sut = new HealthCheckResponse($checkSingleResponses);

        $this->assertTrue($sut->hasPassed());
    }

    public function testHasPassedReturnsFalseIfOneTestFails(): void
    {
        $checkSingleResponses = [
            new HealthCheckSingleResponse('name1', 'PASS'),
            new HealthCheckSingleResponse('name2', 'FAIL'),
        ];

        $sut = new HealthCheckResponse($checkSingleResponses);

        $this->assertFalse($sut->hasPassed());
    }
}
