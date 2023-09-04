<?php

namespace Piwik\HealthCheck\Responses;

use RuntimeException;

final class HealthCheckSingleResponse {
    public const HEALTH_CHECK_PASSED = 'PASS';
    public const HEALTH_CHECK_FAILED = 'FAIL';

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
        if ($status !== self::HEALTH_CHECK_FAILED && $status !== self::HEALTH_CHECK_PASSED) {
            throw new RuntimeException(sprintf("Status must be %s or %s", self::HEALTH_CHECK_PASSED, self::HEALTH_CHECK_FAILED));
        }

        $this->name = $name;
        $this->status = $status;
    }

    public function hasPassed(): bool
    {
        return $this->status === self::HEALTH_CHECK_PASSED;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'status' => $this->status
        ];
    }
}
