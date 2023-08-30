<?php

namespace Piwik\HealthCheck\HealthCheck\Checks;

use Piwik\Config\GeneralConfig;
use Piwik\Plugin\Manager;

final class DashboardAvailableHealthCheck implements HealthCheckInterface
{
    public function getName(): string
    {
        return 'dashboardAvailable';
    }

    public function test(): bool
    {
        $inMaintenanceMode = GeneralConfig::getConfigValue('maintenance_mode') === 1;

        if ($inMaintenanceMode) {
            return false;
        }

        $pluginManager = Manager::getInstance();
        $pluginsActivatedInConfig = $pluginManager->getActivatedPluginsFromConfig();

        return in_array('Dashboard', $pluginsActivatedInConfig);
    }
}
