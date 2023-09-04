<?php

namespace Piwik\Tests\Unit\HealthCheck\Responses;

use PHPUnit\Framework\TestCase;
use Piwik\HealthCheck\Responses\HealthCheckSingleResponse;

/**
 * @group Core
 * @group HealthCheck2
 * @group HealthCheckService
 * @group HealthCheckSingleResponse
 */
final class HealthCheckSingleResponseTest extends TestCase
{
    public function testConstructThrowsExceptionWhenInvalidStatus(): void
    {
        $this->expectExceptionMessage('Status must be PASS or FAIL');

        new HealthCheckSingleResponse('name', 'bad status');
    }

    public function testToArrayReturnsMappedArrayOfData(): void
    {
        $sut = new HealthCheckSingleResponse('name', 'PASS');

        $response = $sut->toArray();

        $this->assertEquals(
            [
                'name' => 'name',
                'status' => 'PASS'
            ],
            $response
        );
    }
}
