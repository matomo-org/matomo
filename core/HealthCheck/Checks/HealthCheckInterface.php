<?php

namespace Piwik\HealthCheck\Checks;

interface HealthCheckInterface
{
    public function getName(): string;
    public function test(): bool;
}
