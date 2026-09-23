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
     * The Marketplace overview, as the campaign medium names it.
     *
     * The overview's plugin cards are fetched by its Vue app, so the request that builds a shop
     * link names the Ajax endpoint rather than the page the link is rendered on. The endpoints
     * serving that page pass this instead of leaving the medium to be derived; see
     * {@link getShopCampaignMedium()}.
     */
    public const CAMPAIGN_MEDIUM_OVERVIEW = self::CAMPAIGN_MEDIUM_PREFIX . 'marketplace.overview';

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

    private $pluginUpdateSummaryCache = null;

    public function __construct(Api\Client $marketplaceClient, Consumer $consumer, Advertising $advertising)
    {
        $this->marketplaceClient = $marketplaceClient;
        $this->consumer = $consumer;
        $this->advertising = $advertising;
        $this->pluginManager = Plugin\Manager::getInstance();
        $this->numberFormatter = NumberFormatter::getInstance();
    }

    /**
     * @param string      $pluginName
     * @param string|null $campaignMedium the page the plugin's shop links are rendered on, for
     *                                    callers whose request does not name it - see
     *                                    {@link CAMPAIGN_MEDIUM_OVERVIEW}
     */
    public function getPluginInfo($pluginName, ?string $campaignMedium = null)
    {
        $plugin = $this->marketplaceClient->getPluginInfo($pluginName);
        $plugin = $this->enrichPluginInformation($plugin, $campaignMedium);

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
     * Only an already cached list is used. Fetching one to answer for a single plugin would download
     * the whole catalogue where the info request downloads one plugin.
     *
     * @param string|null $campaignMedium the page the plugin's shop links are rendered on, for
     *                                    callers whose request does not name it - see
     *                                    {@link CAMPAIGN_MEDIUM_OVERVIEW}
     *
     * @return array<string, mixed>
     */
    public function getPluginInfoPreferringList(string $pluginName, ?string $campaignMedium = null): array
    {
        $plugin = $this->marketplaceClient->findInCachedOverviewLists($pluginName);

        if (null !== $plugin) {
            // the raw cached list entry, so only the plugin that was asked for is enriched. Going
            // through searchPlugins() would enrich the whole catalogue to return one.
            return $this->enrichPluginInformation($plugin, $campaignMedium);
        }

        // either the lists are cold or this is a plugin they filter out — ask for it directly
        return $this->getPluginInfo($pluginName, $campaignMedium);
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

    /**
     * @param string|null $campaignMedium the page the shop links are rendered on, for callers whose
     *                                    request does not name it - see {@link CAMPAIGN_MEDIUM_OVERVIEW}
     */
    public function searchPlugins($query, $sort, $themesOnly, $purchaseType = '', ?string $campaignMedium = null)
    {
        if ($themesOnly) {
            $plugins = $this->marketplaceClient->searchForThemes('', $query, $sort, $purchaseType);
        } else {
            $plugins = $this->marketplaceClient->searchForPlugins('', $query, $sort, $purchaseType);
        }

        foreach ($plugins as $index => $plugin) {
            $plugins[$index] = $this->enrichPluginInformation($plugin, $campaignMedium);
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
        // an activated plugin is installed by definition, and this saves reading the directory
        if (in_array($pluginName, $this->activatedPluginNames, true)) {
            return true;
        }

        return $this->pluginManager->isPluginInstalled($pluginName, true);
    }

    private function enrichPluginInformation($plugin, ?string $campaignMedium = null)
    {
        if (empty($plugin)) {
            return $plugin;
        }

        $plugin['isInstalled']  = $this->isPluginInstalled($plugin['name']);
        $plugin['isActivated']  = $this->isPluginActivated($plugin['name']);
        $plugin['isInvalid']    = $this->pluginManager->isPluginThirdPartyAndBogus($plugin['name']);
        $plugin['canBeUpdated'] = $plugin['isInstalled'] && $this->hasPluginUpdate($plugin);
        $plugin['lastUpdatedRaw'] = $plugin['lastUpdated'] ?? null;
        $plugin['lastUpdated']  = $this->toShortDate($plugin['lastUpdated']);
        $plugin['categories']   = $this->normaliseCategories($plugin);
        $plugin['promotions']   = $this->normalisePromotions($plugin);
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

        // the marketplace decides which bundles are sold outright; without the flag a bundle keeps
        // the free-trial flow it had before. The core check stays in front of it so an older
        // Matomo is never switched over by the flag alone.
        $plugin['isNewBundle'] = self::supportsNewBundles() && !empty($plugin['isNewBundle']);

        $plugin['isEligibleForFreeTrial'] =
            $plugin['canBePurchased']
            && !$plugin['isNewBundle']
            && empty($plugin['missingRequirements'])
            && empty($this->getCurrentLicenseFor($plugin));

        $this->addCampaignParametersToShopUrls($plugin, $campaignMedium);
        $this->addPriceFrom($plugin);
        $this->addBundleSeats($plugin);
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
        $license = $this->getCurrentLicenseFor($plugin);

        $plugin['hasExceededLicense'] = $isPremiumFeature
            && !empty($license['isValid'])
            && !empty($license['isExceeded']);
        $plugin['isMissingLicense'] = $isPremiumFeature
            && (
                empty($license)
                || (!empty($license['status']) && $license['status'] === 'Cancelled')
            );

        // and replace the copy the Marketplace embedded in the cached list with the one the flags
        // were just derived from, so the plugin does not carry two answers to the same question.
        // Plugins\InvalidLicenses reads this to classify the admin-page license banners, and read
        // the stale copy while the cards read the fresh one until this was written back.
        if (array_key_exists('consumer', $plugin) && is_array($plugin['consumer'])) {
            $plugin['consumer']['license'] = $license;
        }

        return $plugin;
    }

    /**
     * Returns the consumer's license for the given plugin, preferring the consumer response over the
     * copy the plugin carries.
     *
     * A plugin's own copy comes from a plugin list that is cached for far longer than the consumer
     * response, so on its own it can keep showing a license the consumer has just bought as missing.
     * Where the consumer response says nothing about the plugin we keep using that copy, so an
     * instance that cannot reach the Marketplace behaves exactly as it did before.
     *
     * @param array<string, mixed> $plugin
     * @return array<string, mixed>|scalar|null
     */
    private function getCurrentLicenseFor(array $plugin)
    {
        /** @var array<string, mixed>|scalar|null $embedded */
        $embedded = isset($plugin['consumer']['license']) ? $plugin['consumer']['license'] : null;

        if (!empty($embedded) && !is_array($embedded)) {
            // the Marketplace sets a scalar here for the plugins whose trial and purchase calls to
            // action it suppresses - BusinessBundle and EnterpriseBundle - and the consumer response
            // has no way to say that, since it only ever carries real license rows. Honouring it
            // keeps those bundles out of the free trial flow.
            return $embedded;
        }

        $licenses = $this->consumer->getConsumerPluginLicenses();

        if ($licenses === null) {
            // the Marketplace could not be reached, so the copy the plugin carries is all we have
            return $embedded;
        }

        // an answer that lists no license for this plugin is an answer: the consumer does not hold
        // one, whatever the plugin list cached earlier still says
        return isset($licenses[$plugin['name']]) ? $licenses[$plugin['name']] : null;
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
     *
     * @param string|null $campaignMedium the page the links are rendered on; derived from the
     *                                    request when the caller does not name one
     */
    private function addCampaignParametersToShopUrls(&$plugin, ?string $campaignMedium = null): void
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
                $campaignMedium ?? $this->getShopCampaignMedium(),
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
     * The page the current request renders, eg. app.corepluginsadmin.plugins. Returns null outside
     * a request, where there is no page to name and the link is left untagged.
     *
     * Only correct where the page itself is the request. A page whose links are built by an Ajax
     * endpoint names its placement with $campaignMedium instead, or this reports the endpoint.
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
     * The category slugs a plugin is filed under, always as a clean list of strings, so the client
     * cannot tell an unclassified plugin from a response cached before the field existed.
     *
     * The singular `category` the Marketplace also sends is stale - it reports `uncategorised` for
     * most paid plugins - and nothing reads it.
     *
     * @param array<string, mixed> $plugin
     * @return string[]
     */
    private function normaliseCategories(array $plugin): array
    {
        $categories = $plugin['categories'] ?? [];

        if (!is_array($categories)) {
            return [];
        }

        return array_values(array_filter(
            $categories,
            static fn ($slug) => is_string($slug) && '' !== $slug
        ));
    }

    /**
     * The promotion lists a plugin appears in, as slug => position, so the overview can build a
     * section for each and order it the way the Marketplace does.
     *
     * The position is the plugin's index in the list the Marketplace keeps, so reordering a
     * promotion is a reordering there and nothing here. A plugin in no list arrives with an empty
     * map; so does a response cached before the field existed, which is why a missing field is not
     * distinguished from an empty one.
     *
     * @param array<string, mixed> $plugin
     * @return array<string, int>
     */
    private function normalisePromotions(array $plugin): array
    {
        $promotions = $plugin['promotions'] ?? [];

        if (!is_array($promotions)) {
            return [];
        }

        $normalised = [];

        foreach ($promotions as $slug => $position) {
            if (is_string($slug) && '' !== $slug && is_numeric($position)) {
                $normalised[$slug] = (int) $position;
            }
        }

        return $normalised;
    }

    /**
     * The seat tier a bundle is licensed for, which the Marketplace spells into each shop
     * variation's name ("Up to 20 users"). Resolved here rather than by parsing a display string.
     *
     * Only when every variation names the same tier, which not every bundle manages. The older
     * bundles are one product per tier - Team, Business and Enterprise - so their variations are
     * billing periods times currencies and all repeat that product's tier, and the card reads it.
     * A newer bundle is one product sold at all three tiers, six variations over "Up to 4 users",
     * "5 to 15 users" and "Unlimited users", and there no single count describes the card: it
     * carries no price to say which tier it is quoting, so taking the one addPriceFrom() picked
     * would label every such bundle "Up to 4 users". Leaving the field unset drops the label
     * instead. A name with no number ("Unlimited users") is a tier of its own and counts here.
     * Bundles only: a paid plugin offers all three tiers at once, so it has no single count.
     *
     * The number is read whole, group separators and all: matching digits alone reads "Up to 1,000
     * users" as 0, and a 0 renders nothing, so the wrong answer would never show up on screen.
     *
     * @param array<string, mixed> $plugin
     */
    private function addBundleSeats(&$plugin): void
    {
        if (empty($plugin['isBundle'])) {
            return;
        }

        $tiers = [];

        foreach ($plugin['shop']['variations'] ?? [] as $variation) {
            $tiers[] = $this->readSeatTier($variation['name'] ?? '');
        }

        if (!count($tiers) || count(array_unique($tiers, SORT_REGULAR)) > 1) {
            return;
        }

        if (null !== $tiers[0]) {
            $plugin['bundleSeats'] = $tiers[0];
        }
    }

    /**
     * The seat count a variation name spells out, or null when it names an unnumbered tier.
     */
    private function readSeatTier(string $variationName): ?int
    {
        if (!preg_match('/(\d[\d,.\x{00A0}\x{202F} ]*)\s*users/iu', $variationName, $matches)) {
            return null;
        }

        $seats = (int) preg_replace('/\D/', '', $matches[1]);

        return $seats > 0 ? $seats : null;
    }

    /**
     * If plugin provides a cover image via Marketplace, we use that.
     *
     * If there's no cover image from the marketplace (e.g. for plugins not yet categorised or not providing a custom
     * cover image), we fall back to one generic image for every plugin, whoever owns it. The Marketplace's own
     * category stand-ins count as no cover image here - see {@link isCategoryCoverImage()}.
     *
     * @param $plugin
     */
    private function addPluginCoverImage(&$plugin): void
    {
        $coverImage = $plugin['coverImage'] ?? '';

        if ('' !== $coverImage && !$this->isCategoryCoverImage($coverImage)) {
            return;
        }

        $plugin['coverImage'] = 'plugins/Marketplace/images/categories/uncategorised.png';
    }

    /**
     * Whether a cover image is one of the Marketplace's own stand-ins rather than a screenshot. A
     * plugin with no screenshot still arrives with one, filled in from its category or the generic
     * `uncategorised` image; both are placeholders, so both fall through to ours.
     *
     * Matched on the trailing path, not the host, so it also catches the local copies the UI tests
     * rewrite these URLs to and the paths this method's caller writes.
     */
    private function isCategoryCoverImage(string $coverImage): bool
    {
        return 1 === preg_match('@(^|/)categories/[^/]+\.png$@i', $coverImage);
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

    /**
     * Adds the status of the consumer's license for the plugin, or '' where it holds none.
     *
     * Where the Marketplace could not be reached this falls back to the license the plugin carries,
     * as {@link getCurrentLicenseFor()} does, rather than reading the missing answer as "no license"
     * and showing every licensed plugin as unowned. That method is not reused because it also
     * returns the scalar the Marketplace sets to suppress a bundle's trial, which is not a license
     * and would hide the consumer's real status for that bundle.
     */
    private function addConsumerLicenseStatus($plugin): array
    {
        $licenses = $this->consumer->getConsumerPluginLicenses();
        $license = $licenses === null
            ? ($plugin['consumer']['license'] ?? null)
            : ($licenses[$plugin['name']] ?? null);

        $plugin['licenseStatus'] = is_array($license) ? (string) ($license['status'] ?? '') : '';

        return $plugin;
    }
}
