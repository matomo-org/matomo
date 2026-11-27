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
 * Settings of the weekly tracking failures email. The switch is a config setting, the excluded websites an option.
 */
class TrackingFailuresNotificationSettings
{
    private const OPTION_EXCLUDED_SITES = 'CoreAdminHome.trackingFailuresNotificationExcludedSites';

    public function isEnabled(): bool
    {
        return (bool) Config::getInstance()->General['enable_tracking_failures_notification'];
    }

    /** @return list<int> Deleted websites are left out. */
    public function getExcludedSiteIds(): array
    {
        return $this->keepExistingSiteIds($this->getStoredSiteIds());
    }

    /** @return list<array{id: int, name: string}> Names are HTML entity encoded, as stored. */
    public function getExcludedSites(): array
    {
        $sites = [];
        foreach ((new SitesModel())->getSitesFromIds($this->getStoredSiteIds()) as $site) {
            $sites[] = ['id' => (int) $site['idsite'], 'name' => (string) $site['name']];
        }

        return $sites;
    }

    /** @param array<mixed> $excludedIdSites Unknown IDs are dropped. */
    public function save(bool $enabled, array $excludedIdSites): void
    {
        if ($enabled !== $this->isEnabled()) {
            // config first, so a failed write saves nothing
            Config::setSetting('General', 'enable_tracking_failures_notification', $enabled ? 1 : 0);
            Config::getInstance()->forceSave();
        }

        Option::set(self::OPTION_EXCLUDED_SITES, json_encode($this->keepExistingSiteIds($excludedIdSites)));
    }

    /** @return list<int> */
    private function getStoredSiteIds(): array
    {
        $stored = json_decode((string) Option::get(self::OPTION_EXCLUDED_SITES), true);

        return is_array($stored) ? array_values(array_map('intval', array_filter($stored, 'is_numeric'))) : [];
    }

    /**
     * @param array<mixed> $idSites
     * @return list<int>
     */
    private function keepExistingSiteIds(array $idSites): array
    {
        $idSites = array_map('intval', array_filter($idSites, 'is_numeric'));
        if (empty($idSites)) {
            return [];
        }

        $existing = array_map('intval', array_column((new SitesModel())->getSitesFromIds($idSites), 'idsite'));

        return array_values(array_unique(array_intersect($idSites, $existing)));
    }
}
