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
        if (empty($healthChecks)) {
            throw new \RuntimeException('At least 1 health check is required to operate the health check service');
        }

        foreach ($healthChecks as $healthCheck) {
            $this->healthChecks[$healthCheck->getName()] = $healthCheck;
        }
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
