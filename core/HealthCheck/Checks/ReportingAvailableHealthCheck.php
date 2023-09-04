<?php

namespace Piwik\HealthCheck\Checks;

use Piwik\Config\GeneralConfig;

final class ReportingAvailableHealthCheck implements HealthCheckInterface
{
    public function getName(): string
    {
        return 'reportingAvailable';
    }

    public function test(): bool
    {
        $inMaintenanceMode = GeneralConfig::getConfigValue('maintenance_mode') === 1;

        if ($inMaintenanceMode) {
            return false;
        }

        return true;
    }
}
