<?php

namespace HealthCheck\HealthCheck\Responses;

use PHPUnit\Framework\TestCase;
use Piwik\HealthCheck\HealthCheck\Responses\HealthCheckResponse;
use Piwik\HealthCheck\HealthCheck\Responses\HealthCheckSingleResponse;

/**
 * @group Core
 * @group HealthCheck
 * @group HealthCheckResponse
 */
final class HealthCheckResponseTest extends TestCase
{
    public function test_hasPassed_returnsTrueIfAllTestsPassed(): void
    {
        $checkSingleResponses = [
            new HealthCheckSingleResponse('name1', 'PASS'),
            new HealthCheckSingleResponse('name2', 'PASS'),
        ];

        $sut = new HealthCheckResponse($checkSingleResponses);

        $this->assertTrue($sut->hasPassed());
    }

    public function test_hasPassed_returnsFalseIfOneTestFails(): void
    {
        $checkSingleResponses = [
            new HealthCheckSingleResponse('name1', 'PASS'),
            new HealthCheckSingleResponse('name2', 'FAIL'),
        ];

        $sut = new HealthCheckResponse($checkSingleResponses);

        $this->assertFalse($sut->hasPassed());
    }
}
