<?php

namespace Piwik\HealthCheck\Responses;

final class HealthCheckResponse
{
    /**
     * @var HealthCheckSingleResponse[]
     */
    private $checkSingleResponses;

    /**
     * @param HealthCheckSingleResponse[] $checkSingleResponses
     */
    public function __construct(array $checkSingleResponses)
    {
        $this->checkSingleResponses = $checkSingleResponses;
    }

    public function hasPassed(): bool
    {
        foreach ($this->checkSingleResponses as $checkSingleResponse) {
            if ($checkSingleResponse->hasPassed() === false) {
                return false;
            }
        }

        return true;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->hasPassed() ? HealthCheckStatus::HEALTH_CHECK_PASSED : HealthCheckStatus::HEALTH_CHECK_FAILED,
            'checks' => array_map(function (HealthCheckSingleResponse $checkSingleResponse): array {
                return $checkSingleResponse->toArray();
            }, $this->checkSingleResponses)
        ];
    }
}
