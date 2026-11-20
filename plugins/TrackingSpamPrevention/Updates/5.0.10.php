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

class Updates_5_0_10 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        // 5.0.9 force-added this to existing block lists; it should not be blocked by default.
        $config = Config::getInstance();
        $pluginConfig = $config->TrackingSpamPrevention;
        $providers = $pluginConfig[Configuration::KEY_GEOIP_MATCH_PROVIDERS] ?? [];

        $key = array_search('verein zur foerderung eines deutschen forschungsnetzes', $providers, true);

        if ($key !== false) {
            unset($providers[$key]);
            $pluginConfig[Configuration::KEY_GEOIP_MATCH_PROVIDERS] = array_values($providers);
            $config->TrackingSpamPrevention = $pluginConfig;
            $config->forceSave();
        }
    }
}
