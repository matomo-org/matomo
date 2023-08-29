<?php

namespace Piwik\Plugins\API\HealthCheck\Responses;

final class HealthCheckResponse
{
    private const HEALTH_CHECK_PASSED = 'PASS';
    private const HEALTH_CHECK_FAILED = 'FAIL';

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
            'status' => $this->hasPassed() ? self::HEALTH_CHECK_PASSED : self::HEALTH_CHECK_FAILED,
            'checks' => array_map(function (HealthCheckSingleResponse $checkSingleResponse): array {
                return $checkSingleResponse->toArray();
            }, $this->checkSingleResponses)
        ];
    }
}
