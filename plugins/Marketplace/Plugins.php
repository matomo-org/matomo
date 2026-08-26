<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\NumberFormatter;
use Piwik\ProfessionalServices\Advertising;
use Piwik\Plugin\Dependency as PluginDependency;
use Piwik\Plugin;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;

class Plugins
{
    private Api\Client $marketplaceClient;

    private Consumer $consumer;

    private Advertising $advertising;

    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    /**
     * @var NumberFormatter
     */
    private $numberFormatter;

    /**
     * @internal for tests only
     * @var array
     */
    private $activatedPluginNames = array();

    private $pluginUpdateSummaryCache = null;

    public function __construct(Api\Client $marketplaceClient, Consumer $consumer, Advertising $advertising)
    {
        $this->marketplaceClient = $marketplaceClient;
        $this->consumer = $consumer;
        $this->advertising = $advertising;
        $this->pluginManager = Plugin\Manager::getInstance();
        $this->numberFormatter = NumberFormatter::getInstance();
    }

    public function getPluginInfo($pluginName)
    {
        $plugin = $this->marketplaceClient->getPluginInfo($pluginName);
        $plugin = $this->enrichPluginInformation($plugin);

        return $plugin;
    }

    /**
     * Returns one plugin's full information, preferring the lists the overview has already cached
     * over a request for that plugin on its own.
     *
     * The Marketplace answers both with the same payload — a list entry carries the same fields as
     * an info response, including the readme HTML the details modal renders — but the lists are
     * cached for {@link Api\Client::PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS} and refilled by a
     * scheduled task, where asking for a single plugin costs a round trip to the Marketplace the
     * first time each one is opened.
     *
     * @return array<string, mixed>
     */
    public function getPluginInfoPreferringList(string $pluginName): array
    {
        foreach ([false, true] as $themesOnly) {
            try {
                $listed = $themesOnly
                    ? $this->marketplaceClient->searchForThemes('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL)
                    : $this->marketplaceClient->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);
            } catch (\Exception $e) {
                // the list is the fast path, not the only one. It is a much larger response than a
                // single plugin's, so it can fail where the direct request below still answers.
                continue;
            }

            foreach ($listed as $plugin) {
                if (isset($plugin['name']) && $plugin['name'] === $pluginName) {
                    // the raw cached list, so only the plugin that was asked for is enriched.
                    // Going through searchPlugins() would enrich the whole catalogue to return one.
                    return $this->enrichPluginInformation($plugin);
                }
            }
        }

        // not in either list, so it is one the search filters out — ask for it directly
        return $this->getPluginInfo($pluginName);
    }

    public function getLicenseValidInfo($pluginName)
    {
        $plugin = $this->marketplaceClient->getPluginInfo($pluginName);
        $plugin = $this->enrichLicenseInformation($plugin);

        return array(
            'hasExceededLicense' => !empty($plugin['hasExceededLicense']),
            'isMissingLicense' => !empty($plugin['isMissingLicense']),
        );
    }

    public function getAvailablePluginNames($themesOnly)
    {
        if ($themesOnly) {
            // we do not use getAllThemes() or getAllPlugins() since those methods would apply a whitelist
            // github organization filter and here we actually want to get all plugin names.
            $plugins = $this->marketplaceClient->searchForThemes('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);
        } else {
            $plugins = $this->marketplaceClient->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);
        }

        $names = array();
        foreach ($plugins as $plugin) {
            $names[] = $plugin['name'];
        }

        return $names;
    }

    public function getAllAvailablePluginNames()
    {
        return array_merge(
            $this->getAvailablePluginNames(true),
            $this->getAvailablePluginNames(false)
        );
    }

    public function searchPlugins($query, $sort, $themesOnly, $purchaseType = '')
    {
        if ($themesOnly) {
            $plugins = $this->marketplaceClient->searchForThemes('', $query, $sort, $purchaseType);
        } else {
            $plugins = $this->marketplaceClient->searchForPlugins('', $query, $sort, $purchaseType);
        }

        foreach ($plugins as $index => $plugin) {
            $plugins[$index] = $this->enrichPluginInformation($plugin);
        }

        return array_values($plugins);
    }

    public function getAllPaidPlugins()
    {
        return $this->searchPlugins($query = '', Sort::DEFAULT_SORT, $themes = false, PurchaseType::TYPE_PAID);
    }

    public function getAllFreePlugins()
    {
        return $this->searchPlugins($query = '', Sort::DEFAULT_SORT, $themes = false, PurchaseType::TYPE_FREE);
    }

    public function getAllThemes()
    {
        return $this->searchPlugins($query = '', Sort::DEFAULT_SORT, $themes = true, PurchaseType::TYPE_ALL);
    }

    public function getAllPlugins()
    {
        return $this->searchPlugins($query = '', Sort::DEFAULT_SORT, $themes = false, PurchaseType::TYPE_ALL);
    }

    private function getPluginUpdateInformation($plugin)
    {
        if (empty($plugin['name'])) {
            return;
        }

        if (!isset($this->pluginUpdateSummaryCache)) {
            $this->pluginUpdateSummaryCache = $this->getPluginUpdateSummaries();
        }

        return isset($this->pluginUpdateSummaryCache[$plugin['name']])
            ? $this->pluginUpdateSummaryCache[$plugin['name']]
            : null;
    }

    /**
     * Returns the update information used to enrich a plugin, keyed by plugin name.
     *
     * This deliberately does not go through {@link getPluginsHavingUpdate()}: enrichment only needs
     * to know that an update exists, plus its changelog URL and the installed version, and that all
     * comes back from a single checkUpdates request. Resolving each plugin's full info instead costs
     * one extra Marketplace request per plugin having an update.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getPluginUpdateSummaries(): array
    {
        $forcedResult = StaticContainer::get('dev.forced_plugin_update_result');
        if ($forcedResult !== null) {
            return $forcedResult;
        }

        $this->pluginManager->loadAllPluginsAndGetTheirInfo();
        $loadedPlugins = $this->pluginManager->getLoadedPlugins();

        try {
            $summaries = $this->marketplaceClient->getUpdateSummariesOfPluginsHavingUpdate($loadedPlugins);
        } catch (\Exception $e) {
            return [];
        }

        foreach (array_keys($summaries) as $pluginName) {
            if (!isset($loadedPlugins[$pluginName])) {
                // an update for a plugin that is not loaded cannot be applied, so ignore it
                unset($summaries[$pluginName]);
                continue;
            }

            $summaries[$pluginName]['currentVersion'] = $loadedPlugins[$pluginName]->getVersion();
        }

        return $summaries;
    }

    /**
     * for tests only
     * @internal
     * @ignore
     * @param $plugins
     */
    public function setPluginsHavingUpdateCache($plugins)
    {
        $this->pluginUpdateSummaryCache = $plugins;
    }

    private function hasPluginUpdate($plugin)
    {
        $update = $this->getPluginUpdateInformation($plugin);

        return !empty($update);
    }

    /**
     * @return array (pluginName => pluginDetails)
     */
    public function getPluginsHavingUpdate(): array
    {
        $forcedResult = StaticContainer::get('dev.forced_plugin_update_result');
        if ($forcedResult !== null) {
            return $forcedResult;
        }

        $this->pluginManager->loadAllPluginsAndGetTheirInfo();
        $loadedPlugins = $this->pluginManager->getLoadedPlugins();

        try {
            $pluginsHavingUpdate = $this->marketplaceClient->getInfoOfPluginsHavingUpdate($loadedPlugins);
        } catch (\Exception $e) {
            $pluginsHavingUpdate = array();
        }

        foreach ($pluginsHavingUpdate as $pluginName => $updatePlugin) {
            foreach ($loadedPlugins as $loadedPlugin) {
                if (
                    !empty($updatePlugin['name'])
                    && $loadedPlugin->getPluginName() == $updatePlugin['name']
                ) {
                    $updatePlugin['currentVersion'] = $loadedPlugin->getVersion();
                    $updatePlugin['isActivated'] = $this->pluginManager->isPluginActivated($updatePlugin['name']);
                    $pluginsHavingUpdate[$pluginName] = $this->addMissingRequirements($updatePlugin);
                    break;
                }
            }
        }

        // remove plugins that have updates but for some reason are not loaded
        foreach ($pluginsHavingUpdate as $pluginName => $updatePlugin) {
            if (empty($updatePlugin['currentVersion'])) {
                unset($pluginsHavingUpdate[$pluginName]);
            }
        }

        return $pluginsHavingUpdate;
    }

    /**
     * for tests only
     * @param array $pluginNames
     * @internal
     * @ignore
     */
    public function setActivatedPluginNames($pluginNames)
    {
        $this->activatedPluginNames = $pluginNames;
    }

    private function isPluginActivated($pluginName)
    {
        if (in_array($pluginName, $this->activatedPluginNames)) {
            return true;
        }

        return $this->pluginManager->isPluginActivated($pluginName);
    }

    private function isPluginInstalled($pluginName)
    {
        if (in_array($pluginName, $this->activatedPluginNames)) {
            return true;
        }

        return $this->pluginManager->isPluginInstalled($pluginName, true);
    }

    private function enrichPluginInformation($plugin)
    {
        if (empty($plugin)) {
            return $plugin;
        }

        $plugin['isInstalled']  = $this->isPluginInstalled($plugin['name']);
        $plugin['isActivated']  = $this->isPluginActivated($plugin['name']);
        $plugin['isInvalid']    = $this->pluginManager->isPluginThirdPartyAndBogus($plugin['name']);
        $plugin['canBeUpdated'] = $plugin['isInstalled'] && $this->hasPluginUpdate($plugin);
        $plugin['lastUpdated']  = $this->toShortDate($plugin['lastUpdated']);
        $plugin['canBePurchased'] = !$plugin['isDownloadable'] && !empty($plugin['shop']['url']);

        if ($plugin['isInstalled']) {
            $plugin = $this->enrichLicenseInformation($plugin);
        } else {
            $plugin['hasExceededLicense'] = false;
            $plugin['isMissingLicense'] = false;
        }

        if (
            !empty($plugin['owner'])
            && strtolower($plugin['owner']) === 'piwikpro'
            && !empty($plugin['homepage'])
            && strpos($plugin['homepage'], 'pk_campaign') === false
        ) {
            $plugin['homepage'] = $this->advertising->addPromoCampaignParametersToUrl($plugin['homepage'], Advertising::CAMPAIGN_NAME_PROFESSIONAL_SERVICES, 'Marketplace', $plugin['name']);
        }

        if ($plugin['canBeUpdated']) {
            $pluginUpdate = $this->getPluginUpdateInformation($plugin);
            $plugin['repositoryChangelogUrl'] = isset($pluginUpdate['repositoryChangelogUrl'])
                ? $pluginUpdate['repositoryChangelogUrl']
                : null;
            $plugin['currentVersion']         = isset($pluginUpdate['currentVersion'])
                ? $pluginUpdate['currentVersion']
                : null;
        }

        if (
            !empty($plugin['activity']['lastCommitDate'])
            && false === strpos($plugin['activity']['lastCommitDate'], '0000')
            && false === strpos($plugin['activity']['lastCommitDate'], '1970')
        ) {
            $plugin['activity']['lastCommitDate'] = $this->toLongDate($plugin['activity']['lastCommitDate']);
        } else {
            $plugin['activity']['lastCommitDate'] = null;
        }

        if (!empty($plugin['versions'])) {
            foreach ($plugin['versions'] as $index => $version) {
                $plugin['versions'][$index]['release'] = $this->toLongDate($version['release']);
            }
        }

        $hasDownloadLink = false;
        if (!empty($plugin['versions'])) {
            $latestVersion = end($plugin['versions']);
            $hasDownloadLink = !empty($latestVersion['download']);
        }
        $plugin['hasDownloadLink'] = $hasDownloadLink;

        $plugin = $this->addMissingRequirements($plugin);
        $plugin = $this->addConsumerLicenseStatus($plugin);

        $plugin['isEligibleForFreeTrial'] =
            $plugin['canBePurchased']
            && empty($plugin['missingRequirements'])
            && empty($plugin['consumer']['license']);

        $this->addPriceFrom($plugin);
        $this->addPluginCoverImage($plugin);
        $this->prettifyNumberOfDownloads($plugin);

        return $plugin;
    }

    private function enrichLicenseInformation($plugin)
    {
        if (empty($plugin)) {
            return $plugin;
        }

        $isPremiumFeature = !empty($plugin['shop']) && empty($plugin['isFree']) && empty($plugin['isDownloadable']);
        $plugin['hasExceededLicense'] = $isPremiumFeature && !empty($plugin['consumer']['license']['isValid']) && !empty($plugin['consumer']['license']['isExceeded']);
        $plugin['isMissingLicense'] = $isPremiumFeature && (empty($plugin['consumer']['license']) || (!empty($plugin['consumer']['license']['status']) && $plugin['consumer']['license']['status'] === 'Cancelled'));

        return $plugin;
    }

    private function toLongDate($date)
    {
        if (!empty($date)) {
            $date = Date::factory($date)->getLocalized(Date::DATE_FORMAT_LONG);
        }

        return $date;
    }

    private function toShortDate($date)
    {
        if (!empty($date)) {
            $date = Date::factory($date)->getLocalized(Date::DATE_FORMAT_SHORT);
        }

        return $date;
    }

    /**
     * Determine if there are any missing requirements/dependencies for the plugin
     *
     * @param $plugin
     * @return array
     */
    private function addMissingRequirements($plugin): array
    {
        $plugin['missingRequirements'] = [];

        if (empty($plugin['versions']) || !is_array($plugin['versions'])) {
            return $plugin;
        }

        $latestVersion = $plugin['versions'][count($plugin['versions']) - 1];

        if (empty($latestVersion['requires'])) {
            return $plugin;
        }

        $requires = $latestVersion['requires'];

        $dependency = new PluginDependency();
        $plugin['missingRequirements'] = $dependency->getMissingDependencies($requires);

        return $plugin;
    }

    /**
     * Find the cheapest shop variant, and if none is found specified, return the first variant.
     *
     * @param $plugin
     */
    private function addPriceFrom(&$plugin): void
    {
        $variations = $plugin['shop']['variations'] ?? [];

        if (!count($variations)) {
            $plugin['priceFrom'] = null;
            return;
        }

        $plugin['priceFrom'] = array_shift($variations); // use first as the default

        foreach ($variations as $variation) {
            if ($variation['cheapest'] ?? false) {
                $plugin['priceFrom'] = $variation;
                return;
            }
        }
    }

    /**
     * If plugin provides a cover image via Marketplace, we use that.
     *
     * If there's no cover image from the marketplace (e.g. for plugins not yet categorised or not providing a custom
     * cover image), we use Matomo image for Matomo plugins and a generic cover image otherwise.
     *
     * @param $plugin
     */
    private function addPluginCoverImage(&$plugin): void
    {
        // if plugin provides cover image (either from the screenshots or based on its category, we use that
        if (!empty($plugin['coverImage'])) {
            return;
        }

        $coverImage = 'uncategorised';

        // use Matomo image for paid plugins, i.e. plugins without the isFree flag and with shop info
        if (
            in_array(strtolower($plugin['owner']), ['piwik', 'matomo-org'])
            && empty($plugin['isFree'])
            && !empty($plugin['shop'])
        ) {
            $coverImage = 'matomo';
        }

        $plugin['coverImage'] = 'plugins/Marketplace/images/categories/' . $coverImage . '.png';
    }

    /**
     * Add prettified number of downloads to plugin info to shorten large numbers to 1k or 1m format.
     *
     * @param $plugin
     */
    private function prettifyNumberOfDownloads(&$plugin): void
    {
        $num = $plugin['numDownloads'] ?? 0;

        $plugin['numDownloadsPretty'] = $this->numberFormatter->formatNumberCompact($num);
    }

    private function addConsumerLicenseStatus($plugin): array
    {
        $consumerPluginLicenseInfo = $this->consumer->getConsumerPluginLicenseStatus();
        $plugin['licenseStatus'] = $consumerPluginLicenseInfo[$plugin['name']] ?? '';

        return $plugin;
    }
}
