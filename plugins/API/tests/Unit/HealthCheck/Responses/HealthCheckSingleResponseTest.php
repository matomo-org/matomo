<?php

namespace Piwik\Plugins\API\tests\Unit\HealthCheck\Responses;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\API\HealthCheck\Responses\HealthCheckSingleResponse;

final class HealthCheckSingleResponseTest extends TestCase
{
    public function test_construct_throwsExceptionWhenInvalidStatus(): void
    {
        $this->expectExceptionMessage('Status must be PASS or FAIL');

        new HealthCheckSingleResponse('name', 'bad status');
    }

    public function test_toArray_returnsMappedArrayOfData(): void
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