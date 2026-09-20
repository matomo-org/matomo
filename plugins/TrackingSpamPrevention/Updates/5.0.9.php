<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

class Updates_5_0_9 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        $config = Config::getInstance();
        $pluginConfig = $config->TrackingSpamPrevention;
        $existingProviders = [];

        if (!empty($pluginConfig[Configuration::KEY_GEOIP_MATCH_PROVIDERS]) && is_array($pluginConfig[Configuration::KEY_GEOIP_MATCH_PROVIDERS])) {
            $existingProviders = $pluginConfig[Configuration::KEY_GEOIP_MATCH_PROVIDERS];
        }

        $normalizedProviders = [];
        foreach ($existingProviders as $provider) {
            $provider = mb_strtolower(trim((string) $provider));
            if ($provider !== '') {
                $normalizedProviders[] = $provider;
            }
        }

        $mergedProviders = array_values(array_unique(array_merge(
            $normalizedProviders,
            Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS
        )));

        if ($existingProviders === $mergedProviders) {
            return;
        }

        $pluginConfig[Configuration::KEY_GEOIP_MATCH_PROVIDERS] = $mergedProviders;
        $config->TrackingSpamPrevention = $pluginConfig;
        $config->forceSave();
    }
}
