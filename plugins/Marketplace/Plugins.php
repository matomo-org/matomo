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
use Piwik\Piwik;
use Piwik\Url;
use Piwik\Version;

class Plugins
{
    /**
     * Campaign dimensions for links that leave the app for the Matomo shop, so purchases can be
     * attributed back to the marketplace. The campaign separates the product families; the exact
     * product is carried by mtm_content.
     */
    private const CAMPAIGN_PREMIUM_PLUGINS = 'app_premiumplugins';
    private const CAMPAIGN_PREMIUM_THEMES = 'app_premiumthemes';
    private const CAMPAIGN_BUNDLES = 'app_bundles';
    private const CAMPAIGN_GROUP = 'in_app_marketplace';
    private const CAMPAIGN_PLACEMENT_ADD_TO_CART = 'add_to_cart';
    // the Marketplace is never activated on Cloud, so the source is always the on-premise app
    private const CAMPAIGN_SOURCE = 'matomo_app_onpremise';
    private const CAMPAIGN_MEDIUM_PREFIX = 'app.';

    /**
     * Bundles are only sold directly from this core version onwards; before it they go through
     * the free-trial flow.
     *
     * The -alpha suffix matters: PHP orders pre-releases alpha < beta < rc < release, so a plain
     * '5.14.0' — or even '5.14.0-b1' — would exclude 5.14.0-alpha, which is what the 5.x branch
     * this ships in reports while in development.
     */
    private const MIN_CORE_VERSION_FOR_NEW_BUNDLES = '5.14.0-alpha';
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

    private $pluginsHavingUpdateCache = null;

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

        if (!isset($this->pluginsHavingUpdateCache)) {
            $this->pluginsHavingUpdateCache = $this->getPluginsHavingUpdate();
        }

        foreach ($this->pluginsHavingUpdateCache as $pluginHavingUpdate) {
            if ($plugin['name'] == $pluginHavingUpdate['name']) {
                return $pluginHavingUpdate;
            }
        }
    }

    /**
     * for tests only
     * @internal
     * @ignore
     * @param $plugins
     */
    public function setPluginsHavingUpdateCache($plugins)
    {
        $this->pluginsHavingUpdateCache = $plugins;
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
            $plugin['repositoryChangelogUrl'] = $pluginUpdate['repositoryChangelogUrl'];
            $plugin['currentVersion']         = $pluginUpdate['currentVersion'];
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

        // the marketplace decides which bundles are sold outright; without the flag a bundle keeps
        // the free-trial flow it had before. The core check stays in front of it so an older
        // Matomo is never switched over by the flag alone.
        $plugin['isNewBundle'] = self::supportsNewBundles() && !empty($plugin['isNewBundle']);

        $plugin['isEligibleForFreeTrial'] =
            $plugin['canBePurchased']
            && !$plugin['isNewBundle']
            && empty($plugin['missingRequirements'])
            && empty($plugin['consumer']['license']);

        $this->addCampaignParametersToShopUrls($plugin);
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
     * Whether this Matomo is new enough to sell bundles directly rather than through a free trial.
     *
     * @param string|null $coreVersion defaults to the running core; pass a version to check another
     */
    public static function supportsNewBundles(?string $coreVersion = null): bool
    {
        return version_compare(
            $coreVersion ?? Version::VERSION,
            self::MIN_CORE_VERSION_FOR_NEW_BUNDLES,
            '>='
        );
    }

    /**
     * Tags every add-to-cart link with the campaign dimensions the shop reports on, so a purchase
     * can be traced back to the product and the placement it was started from.
     *
     * Runs before addPriceFrom() so the variation it picks carries the tagged link too.
     */
    private function addCampaignParametersToShopUrls(&$plugin): void
    {
        if (empty($plugin['shop']['variations']) || !is_array($plugin['shop']['variations'])) {
            return;
        }

        $campaign = $this->getShopCampaignName($plugin);
        $content = $this->toCampaignSlug($plugin['name'] ?? '');

        foreach ($plugin['shop']['variations'] as $index => $variation) {
            if (empty($variation['addToCartUrl'])) {
                continue;
            }

            $plugin['shop']['variations'][$index]['addToCartUrl'] = Url::addCampaignParametersToMatomoLink(
                $variation['addToCartUrl'],
                $campaign,
                self::CAMPAIGN_SOURCE,
                $this->getShopCampaignMedium(),
                self::CAMPAIGN_GROUP,
                $content,
                self::CAMPAIGN_PLACEMENT_ADD_TO_CART
            );
        }
    }

    /**
     * Bundles and themes are reported on separately from single premium plugins.
     */
    private function getShopCampaignName($plugin): string
    {
        if (!empty($plugin['isBundle'])) {
            return self::CAMPAIGN_BUNDLES;
        }

        if (!empty($plugin['isTheme'])) {
            return self::CAMPAIGN_PREMIUM_THEMES;
        }

        return self::CAMPAIGN_PREMIUM_PLUGINS;
    }

    /**
     * The page the link was rendered on, eg. app.marketplace.overview. Returns null outside a
     * request, where there is no page to name and the link is left untagged.
     */
    private function getShopCampaignMedium(): ?string
    {
        $module = Piwik::getModule();
        $action = Piwik::getAction();

        if (empty($module) || empty($action)) {
            return null;
        }

        return strtolower(self::CAMPAIGN_MEDIUM_PREFIX . $module . '.' . $action);
    }

    /**
     * Turns a plugin name into the campaign wording used for it, eg. HeatmapSessionRecording
     * becomes heatmap_session_recording. Runs of capitals are kept together so an acronym does
     * not split into single letters.
     */
    private function toCampaignSlug(string $name): string
    {
        $spaced = preg_replace(
            ['/([a-z\d])([A-Z])/', '/([A-Z]+)([A-Z][a-z])/'],
            '$1_$2',
            $name
        );

        return strtolower($spaced);
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
