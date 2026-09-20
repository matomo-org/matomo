<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Validators\IpRanges;

/**
 * 5.1.0 migrations:
 *  - Migrates the `iprange_allowlist` config value to the `ip_allow_list` system setting.
 *  - Adds the newly identified hosting/datacenter organisations to the block list of existing installs
 *    (new entries live in Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS).
 */
class Updates_5_1_0 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        $this->addNewDefaultBlockedProviders();
        $this->migrateIpAllowList();
    }

    /**
     * Merge the default provider block-list into an existing install's config, preserving any custom
     * entries the admin added. Idempotent.
     */
    private function addNewDefaultBlockedProviders(): void
    {
        $config = Config::getInstance();
        $pluginConfig = $config->TrackingSpamPrevention;
        $existingProviders = [];

        if (
            !empty($pluginConfig[Configuration::KEY_GEOIP_MATCH_PROVIDERS])
            && is_array($pluginConfig[Configuration::KEY_GEOIP_MATCH_PROVIDERS])
        ) {
            $existingProviders = $pluginConfig[Configuration::KEY_GEOIP_MATCH_PROVIDERS];
        }

        $normalizedProviders = [];
        foreach ($existingProviders as $provider) {
            $provider = mb_strtolower(trim((string) $provider));
            if ($provider !== '') {
                $normalizedProviders[] = $provider;
            }
        }

        // preserve custom entries, append any default entries not already present
        $mergedProviders = array_values(array_unique(array_merge(
            $normalizedProviders,
            Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS
        )));

        if ($normalizedProviders === $mergedProviders) {
            return;
        }

        $pluginConfig[Configuration::KEY_GEOIP_MATCH_PROVIDERS] = $mergedProviders;
        $config->TrackingSpamPrevention = $pluginConfig;
        $config->forceSave();
    }

    private function migrateIpAllowList(): void
    {
        $config = Config::getInstance();
        $pluginConfig = $config->TrackingSpamPrevention;

        if (!is_array($pluginConfig) || !array_key_exists(Configuration::KEY_RANGE_ALLOW_LIST, $pluginConfig)) {
            return;
        }

        $ranges = $this->getValidRanges($pluginConfig[Configuration::KEY_RANGE_ALLOW_LIST]);

        if (!empty($ranges)) {
            $settings = StaticContainer::get(SystemSettings::class);
            // updates run as super user, but the setting also reports as unwritable when an `ip_allow_list`
            // config override already exists, so force writability to keep the migration from failing
            $settings->ipAllowList->setIsWritableByCurrentUser(true);

            if (empty($settings->ipAllowList->getValue())) {
                $settings->ipAllowList->setValue($ranges);
                $settings->save();
            }
        }

        unset($pluginConfig[Configuration::KEY_RANGE_ALLOW_LIST]);
        $config->TrackingSpamPrevention = $pluginConfig;

        try {
            $config->forceSave();
        } catch (\Exception $e) {
            // the config file might not be writable, the leftover key is no longer read anywhere
        }
    }

    private function getValidRanges($configRanges): array
    {
        if (!is_array($configRanges)) {
            return [];
        }

        $validator = new IpRanges();

        $ranges = [];
        foreach ($configRanges as $range) {
            $range = trim((string) $range);
            if ($range === '') {
                continue;
            }
            try {
                $validator->validate([$range]);
                $ranges[] = $range;
            } catch (\Exception $e) {
                // drop invalid entries, they never matched any request anyway
            }
        }

        return array_values(array_unique($ranges));
    }
}
