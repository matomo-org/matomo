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
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Storage\Factory as StorageFactory;
use Piwik\Settings\Storage\Storage;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 * 6.0.0-b2 migrations:
 *  - Splits what the `block_clouds` setting used to do in two. It keeps driving the cloud provider
 *    IP range sync, and the new `cloud_blocking_mode` setting decides whether GeoIP organisation
 *    names are matched against nothing, the default list, or a custom list.
 *
 * Every install ends up with the blocking it had before the update. That holds from the moment this
 * update runs, not from the moment the new code is deployed: both new settings default to blocking,
 * so an install that had cloud blocking off matches against the default provider list in the window
 * between deploying and running core:update.
 */
class Updates_6_0_0_b2 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        $this->migrateCloudBlocking();
    }

    private function migrateCloudBlocking(): void
    {
        $storage = StaticContainer::get(StorageFactory::class)->getPluginStorage('TrackingSpamPrevention', '');

        $pluginConfig = Config::getInstance()->TrackingSpamPrevention;
        $pluginConfig = is_array($pluginConfig) ? $pluginConfig : [];

        // `block_clouds` defaults to on from this release, so an install that never saved it reads
        // as on from here on. A config file override shadows the stored value, so it is what the
        // install was actually doing; without one, only the raw storage still separates "never
        // saved" - which used to mean off, and so casts to false here - from a stored on.
        $wasBlockingClouds = array_key_exists('block_clouds', $pluginConfig)
            ? (bool) $pluginConfig['block_clouds']
            : (bool) $storage->getValue('block_clouds', null, FieldConfig::TYPE_BOOL);

        $settings = StaticContainer::get(SystemSettings::class);

        // Stored even when a config file override shadows it: the override wins while it is there, but
        // the setting now defaults to on, so leaving nothing stored would turn IP range blocking on
        // the day someone removes the override to manage it from the UI.
        //
        // Written straight to the storage rather than through the setting because SystemSettings::save()
        // treats a change here as the admin having just toggled the tickbox, and on an install that
        // never saved it even the unchanged value reads as a change - the setting's own default is now
        // on - which would clear the IPs banned for exceeding max_actions_allowed.
        $storage->setValue('block_clouds', $wasBlockingClouds);

        if (
            !array_key_exists('cloud_blocking_mode', $pluginConfig)
            && $storage->getValue('cloud_blocking_mode', null, FieldConfig::TYPE_STRING) === null
        ) {
            $settings->cloudBlockingMode->setIsWritableByCurrentUser(true);
            $settings->cloudBlockingMode->setValue($this->resolveMode($wasBlockingClouds, $storage));
        }

        $settings->save();
    }

    private function resolveMode(bool $wasBlockingClouds, Storage $storage): string
    {
        if (!$wasBlockingClouds) {
            return SystemSettings::CLOUD_BLOCKING_OFF;
        }

        $organisations = $storage->getValue('organisation_block_list', null, FieldConfig::TYPE_ARRAY);

        // nothing stored means the install was following the default list already
        if (!is_array($organisations) || $this->equalsDefaultList($organisations)) {
            return SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST;
        }

        // an emptied list already meant "no organisation blocking, IP ranges still on" before this
        // update, and the custom mode holding an empty list is what reproduces that
        return SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST;
    }

    private function equalsDefaultList(array $organisations): bool
    {
        $defaults = Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS;

        // compare order-insensitively: a list merged by older updates need not hold the entries in
        // the order the constant lists them
        sort($organisations);
        sort($defaults);

        return $organisations === $defaults;
    }
}
