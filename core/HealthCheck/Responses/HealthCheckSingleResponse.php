<?php

namespace Piwik\HealthCheck\Responses;

use RuntimeException;

final class HealthCheckSingleResponse {
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $status;

    public function __construct(string $name, string $status)
    {
        if ($status !== HealthCheckStatus::HEALTH_CHECK_FAILED && $status !== HealthCheckStatus::HEALTH_CHECK_PASSED) {
            throw new RuntimeException(sprintf("Status must be %s or %s", HealthCheckStatus::HEALTH_CHECK_PASSED, HealthCheckStatus::HEALTH_CHECK_FAILED));
        }

        $this->name = $name;
        $this->status = $status;
    }

    public function hasPassed(): bool
    {
        return $this->status === HealthCheckStatus::HEALTH_CHECK_PASSED;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'status' => $this->status
        ];
    }
}
