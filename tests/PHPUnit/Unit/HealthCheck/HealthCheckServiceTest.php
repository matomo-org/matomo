<?php

namespace Piwik\Tests\Unit\HealthCheck;

use PHPUnit\Framework\TestCase;
use Piwik\HealthCheck\Checks\HealthCheckInterface;
use Piwik\HealthCheck\HealthCheckService;

/**
 * @group Core
 * @group HealthCheck
 * @group HealthCheckService
 */
final class HealthCheckServiceTest extends TestCase
{
    public function test_construct_throwsExceptionIfNoHealthChecksPassed(): void
    {
        $this->expectExceptionMessage('At least 1 health check is required to operate the health check service');
        new HealthCheckService([]);
    }

    public function test_construct_performChecksIgnoresDuplicates(): void
    {
        $healthCheck1 = $this->createMock(HealthCheckInterface::class);
        $healthCheck1
            ->method('getName')
            ->willReturn('sameName');
        $healthCheck1
            ->expects($this->never())
            ->method('test');

        $healthCheck2 = $this->createMock(HealthCheckInterface::class);
        $healthCheck2
            ->expects($this->once())
            ->method('test');
        $healthCheck2
            ->method('getName')
            ->willReturn('sameName');


        $sut = new HealthCheckService([
            $healthCheck1,
            $healthCheck2
        ]);
        $sut->performChecks();
    }

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
