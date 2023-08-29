<?php

namespace Piwik\Plugins\API\HealthCheck\Checks;

use Piwik\Config\GeneralConfig;

final class DashboardAvailableHealthCheck implements HealthCheckInterface
{
    public function getName(): string
    {
        return 'dashboardAvailable';
    }

    public function test(): bool
    {
        return GeneralConfig::getConfigValue('maintenance_mode') !== 1;
    }
}
