<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Config;
use Piwik\Option;
use Piwik\Plugins\SitesManager\Model as SitesModel;

/**
 * Settings for the weekly tracking failures email sent to super users.
 *
 * Whether the email is sent at all is the `[General] enable_tracking_failures_notification` config
 * setting. Websites whose failures should not count towards the email are stored as an option.
 */
class TrackingFailuresNotificationSettings
{
    private const OPTION_EXCLUDED_SITES = 'CoreAdminHome.trackingFailuresNotificationExcludedSites';

    public function isEnabled(): bool
    {
        return (bool) Config::getInstance()->General['enable_tracking_failures_notification'];
    }

    /**
     * Deleted websites are left out, so their ID counts towards the email again if it is still tracked.
     *
     * @return list<int>
     */
    public function getExcludedSiteIds(): array
    {
        $stored = json_decode((string) Option::get(self::OPTION_EXCLUDED_SITES), true);

        return is_array($stored) ? $this->keepExistingSiteIds($stored) : [];
    }

    /**
     * @param array<mixed> $excludedIdSites IDs of websites that no longer exist are dropped.
     * @throws \Piwik\Exception\MissingFilePermissionException if the email is switched on or off
     *                                                          and the config file is not writable.
     */
    public function save(bool $enabled, array $excludedIdSites): void
    {
        if ($enabled !== $this->isEnabled()) {
            // written first, so a config file that cannot be written does not leave the settings half saved
            Config::setSetting('General', 'enable_tracking_failures_notification', $enabled ? 1 : 0);
            Config::getInstance()->forceSave();
        }

        Option::set(self::OPTION_EXCLUDED_SITES, json_encode($this->keepExistingSiteIds($excludedIdSites)));
    }

    /**
     * @param array<mixed> $idSites
     * @return list<int>
     */
    private function keepExistingSiteIds(array $idSites): array
    {
        $idSites = array_map('intval', array_filter($idSites, 'is_numeric'));
        $existing = array_map('intval', (new SitesModel())->getSitesId());

        return array_values(array_unique(array_intersect($idSites, $existing)));
    }
}
