<?php

namespace Piwik\Plugins\API\tests\Unit\HealthCheck;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\API\HealthCheck\Checks\HealthCheckInterface;
use Piwik\Plugins\API\HealthCheck\HealthCheckService;

/**
 * @group Plugin
 * @group API
 * @group HealthCheckService
 */
final class HealthCheckServiceTest extends TestCase
{
    public function test_performChecks_buildsResponseWithResultOfHealthCheck(): void
    {
        $healthCheckThatPasses = $this->createMock(HealthCheckInterface::class);
        $healthCheckThatPasses->method('getName')->willReturn('passingCheck');
        $healthCheckThatPasses->method('test')->willReturn(true);

        $healthCheckThatFails = $this->createMock(HealthCheckInterface::class);
        $healthCheckThatFails->method('getName')->willReturn('failingCheck');
        $healthCheckThatFails->method('test')->willReturn(false);

        $sut = new HealthCheckService([
           $healthCheckThatPasses,
           $healthCheckThatFails
        ]);

        $response = $sut->performChecks();

        $this->assertEquals([
            'status' => 'FAIL',
            'checks' => [
                [
                    'name' => 'passingCheck',
                    'status' => 'PASS'
                ],
                [
                    'name' => 'failingCheck',
                    'status' => 'FAIL'
                ]
            ]
        ], $response->toArray());
    }
}