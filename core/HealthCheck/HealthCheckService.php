<?php

namespace Piwik\HealthCheck;

use Piwik\HealthCheck\Checks\HealthCheckInterface;
use Piwik\HealthCheck\Responses\HealthCheckResponse;
use Piwik\HealthCheck\Responses\HealthCheckSingleResponse;

final class HealthCheckService
{
    /**
     * @var HealthCheckInterface[]
     */
    private $healthChecks = [];

    /**
     * @param HealthCheckInterface[] $healthChecks
     */
    public function __construct(array $healthChecks)
    {
        $this->healthChecks = $healthChecks;
    }

    public function performChecks(): HealthCheckResponse
    {
        $healthCheckResponses = [];

        foreach ($this->healthChecks as $healthCheck) {
            $healthCheckResponses[] = new HealthCheckSingleResponse(
                $healthCheck->getName(),
                $healthCheck->test() ? HealthCheckSingleResponse::HEALTH_CHECK_PASSED : HealthCheckSingleResponse::HEALTH_CHECK_FAILED
            );
        }

        return new HealthCheckResponse($healthCheckResponses);
    }
}
