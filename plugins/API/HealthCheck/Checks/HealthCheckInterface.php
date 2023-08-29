<?php

namespace Piwik\Plugins\API\HealthCheck\Checks;

interface HealthCheckInterface
{
    public function getName(): string;
    public function test(): bool;
}
