<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Api;

use Exception as PhpException;
use Matomo\Cache\Lazy;
use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Plugin;
use Piwik\Plugins\Marketplace\BackgroundWarmer;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;
use Piwik\SettingsServer;
use Piwik\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Client
{
    public const CACHE_TIMEOUT_IN_SECONDS = 3600;

    /**
     * Long enough to outlive {@link PLUGIN_LIST_REFRESH_IN_REQUEST_AFTER_SECONDS}, so that a list
     * can still be served while it is refreshed in the background. The prices and requirements
     * shown from the lists can therefore be a few hours old, a trade made to spread the refreshes
     * over time rather than send them to the Marketplace on the hour.
     */
    public const PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS = 25200;
    public const PLUGIN_LIST_REFRESH_AFTER_SECONDS = 3600;
    public const PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS = 14400;

    /**
     * Past the latest a working periodic refresh lands: the 4 hours it waits for, up to an hour
     * until the hourly check sees that, up to an hour of random delay, and the run. A list this old
     * means background refreshes are failing, so a visit refetches it itself.
     */
    public const PLUGIN_LIST_REFRESH_IN_REQUEST_AFTER_SECONDS = 22500;
    public const HTTP_REQUEST_TIMEOUT = 60;

    private const IN_REQUEST_REFRESH_HOLD_SECONDS = 300;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var Lazy
     */
    private $cache;

    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var BackgroundWarmer|null
     */
    private $backgroundWarmer = null;

    public function __construct(Service $service, Lazy $cache, LoggerInterface $logger, Environment $environment)
    {
        $this->service = $service;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->pluginManager = Plugin\Manager::getInstance();
        $this->environment = $environment;
    }

    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * for tests only
     * @internal
     * @ignore
     */
    public function setBackgroundWarmer(BackgroundWarmer $backgroundWarmer): void
    {
        $this->backgroundWarmer = $backgroundWarmer;
    }

    private function getBackgroundWarmer(): BackgroundWarmer
    {
        // resolved on first use rather than injected: this client is built from inside the updater,
        // where constructing the warmer's graph - the scheduler among it - changes what other
        // plugins see, and only a stale list read needs it
        if (null === $this->backgroundWarmer) {
            $this->backgroundWarmer = StaticContainer::get(BackgroundWarmer::class);
        }

        return $this->backgroundWarmer;
    }

    /**
     * @param string $name
     * @return array
     * @throws Exception
     */
    public function getPluginInfo($name)
    {
        $action = sprintf('plugins/%s/info', $name);

        $plugin = $this->fetch($action, array());

        if (empty($plugin['name']) || $this->shouldIgnorePlugin($plugin)) {
            return [];
        }

        return $plugin;
    }

    public function getInfo()
    {
        try {
            $info = $this->fetch('info', array());
        } catch (Exception $e) {
            $info = null;
        }

        return $info;
    }

    public function getConsumer()
    {
        if (!$this->service->hasAccessToken()) {
            // without a license key the Marketplace answers 403, so there is no consumer to ask for
            return null;
        }

        try {
            $licenses = $this->fetch('consumer', array());
        } catch (Exception $e) {
            $licenses = null;
        }

        return $licenses;
    }

    public function isValidConsumer()
    {
        if (!$this->service->hasAccessToken()) {
            return false;
        }

        try {
            $consumer = $this->fetch('consumer/validate', array());
        } catch (Exception $e) {
            $consumer = null;
        }

        return !empty($consumer['isValid']);
    }

    private function getRandomTmpPluginDownloadFilename()
    {
        $tmpPluginPath = StaticContainer::get('path.tmp') . '/latest/plugins/';

        // we generate a random unique id as filename to prevent any user could possibly download zip directly by
        // opening $piwikDomain/tmp/latest/plugins/$pluginName.zip in the browser. Instead we make it harder here
        // and try to make sure to delete file in case of any error.
        $tmpPluginFolder = Common::generateUniqId();

        return $tmpPluginPath . $tmpPluginFolder . '.zip';
    }

    public function download($pluginOrThemeName)
    {
        @ignore_user_abort(true);
        SettingsServer::setMaxExecutionTime(0);

        $downloadUrl = $this->getDownloadUrl($pluginOrThemeName);

        if (empty($downloadUrl)) {
            return false;
        }

        // in the beginning we allowed to specify a download path but this way we make sure security is always taken
        // care of and we always generate a random download filename.Marketplace/Api/Client.php
        $target = $this->getRandomTmpPluginDownloadFilename();

        Filesystem::deleteFileIfExists($target);

        $success = $this->service->download($downloadUrl, $target, static::HTTP_REQUEST_TIMEOUT);

        if ($success) {
            return $target;
        }

        return false;
    }

    /**
     * @param \Piwik\Plugin[] $plugins
     * @return array|mixed
     */
    public function checkUpdates($plugins)
    {
        $params = array();

        foreach ($plugins as $plugin) {
            $pluginName = $plugin->getPluginName();
            if (!$this->pluginManager->isPluginBundledWithCore($pluginName)) {
                $isActivated = $this->pluginManager->isPluginActivated($pluginName);
                $params[] = array('name' => $plugin->getPluginName(), 'version' => $plugin->getVersion(), 'activated' => (int)$isActivated);
            }
        }

        if (empty($params)) {
            return array();
        }

        $params = array('plugins' => $params);
        $params = array('plugins' => json_encode($params));

        $hasUpdates = $this->fetch('plugins/checkUpdates', $params);

        if (empty($hasUpdates)) {
            return array();
        }

        return $hasUpdates;
    }

    /**
     * @param \Piwik\Plugin[] $plugins
     * @return array (pluginName => pluginDetails)
     */
    public function getInfoOfPluginsHavingUpdate($plugins): array
    {
        $hasUpdates = $this->checkUpdates($plugins);

        if (empty($hasUpdates)) {
            return [];
        }

        $listed = $this->getListedPluginsByName();

        $pluginDetails = [];

        foreach ($hasUpdates as $pluginHavingUpdate) {
            if (empty($pluginHavingUpdate)) {
                continue;
            }

            $name = $pluginHavingUpdate['name'];

            // the lists are kept for hours longer than the update check, so shortly after a release
            // the listed entry can still describe the version that is installed
            if (isset($listed[$name]) && ($listed[$name]['latestVersion'] ?? null) === ($pluginHavingUpdate['version'] ?? null)) {
                $plugin = $listed[$name];
            } else {
                try {
                    $plugin = $this->getPluginInfo($name);
                } catch (PhpException $e) {
                    $this->logger->error($e->getMessage());
                    $plugin = null;
                }
            }

            if (!empty($plugin)) {
                $plugin['repositoryChangelogUrl'] = $pluginHavingUpdate['repositoryChangelogUrl'];
                $pluginDetails[$name] = $plugin;
            }
        }

        return $pluginDetails;
    }

    /**
     * Returns whatever the already cached overview lists hold, keyed by plugin name.
     *
     * Those lists are kept refilled in the background and a list entry carries the same fields as an
     * info response, so resolving an update out of them costs nothing where asking about each plugin
     * in turn cost one request per plugin having an update. Returns an empty array when none of them
     * is cached, leaving the caller to fall back to those individual requests - fetching a whole
     * catalogue to answer for a handful of plugins would cost more than it saves.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getListedPluginsByName(): array
    {
        $listed = [];

        foreach (self::getWarmedOverviewLists() as list($action, $purchaseType)) {
            // cached only: when nothing is warm the caller falls back to one info request per
            // plugin having an update, which is far cheaper than downloading whole catalogues
            $list = $this->searchFor($action, '', '', Sort::DEFAULT_SORT, $purchaseType, true);

            foreach ($list ?: [] as $plugin) {
                if (!empty($plugin['name']) && !isset($listed[$plugin['name']])) {
                    $listed[$plugin['name']] = $plugin;
                }
            }
        }

        return $listed;
    }

    /**
     * Returns what the Marketplace reports about the given plugins' updates, keyed by plugin name.
     *
     * Unlike {@link getInfoOfPluginsHavingUpdate()} this issues a single request instead of one more
     * per plugin having an update, so it suits callers that only need to know whether an update
     * exists and where its changelog lives.
     *
     * @param \Piwik\Plugin[] $plugins
     * @return array<string, array<string, mixed>> pluginName => update info
     */
    public function getUpdateSummariesOfPluginsHavingUpdate($plugins): array
    {
        $summaries = [];

        foreach ($this->checkUpdates($plugins) as $pluginHavingUpdate) {
            if (empty($pluginHavingUpdate['name'])) {
                continue;
            }

            $summaries[$pluginHavingUpdate['name']] = $pluginHavingUpdate;
        }

        return $summaries;
    }

    /**
     * Refetches the plugin and theme lists the Marketplace overview asks for, ignoring what is
     * cached, and stores them for {@link PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS}.
     *
     * The warm command cannot do this by calling {@link searchForPlugins()}: a still-valid entry
     * is served without being extended, so warming would be a no-op until the entry had already
     * expired, and the cache would sit cold from each expiry until a later run happened to find it
     * missing. Refetching outright is what keeps it continuously warm.
     */
    public function refreshOverviewListCaches(): void
    {
        $this->tryRefreshOverviewListCaches();
    }

    /**
     * Does what {@link refreshOverviewListCaches()} does, and returns whether every list was
     * refreshed. Separate so that method keeps the return type extensions may override it with.
     *
     * @param string $failureLogLevel A scheduled run passes {@link LogLevel::INFO}: a warning logged
     *                                from the console makes the command exit 1, and the Marketplace
     *                                being unreachable is no failure of `core:archive`.
     */
    public function tryRefreshOverviewListCaches(string $failureLogLevel = LogLevel::WARNING): bool
    {
        $allRefreshed = true;

        foreach (self::getWarmedOverviewLists() as list($action, $purchaseType)) {
            try {
                $this->fetch($action, [
                    'keywords' => '',
                    'query' => '',
                    'sort' => Sort::DEFAULT_SORT,
                    'purchase_type' => $purchaseType,
                ], true);
            } catch (PhpException $e) {
                // per list, so one that cannot be reached does not leave the others cold too
                $this->logger->log($failureLogLevel, 'Could not refresh the Marketplace {list} list: {message}', [
                    'list' => self::describeWarmedList($action, $purchaseType),
                    'message' => $e->getMessage(),
                    'ignoreInScreenWriter' => true,
                ]);
                $allRefreshed = false;
            }
        }

        return $allRefreshed;
    }

    /**
     * Returns how many seconds ago the oldest of the lists {@link refreshOverviewListCaches()}
     * refills was fetched, or null when any of them is not cached at all.
     */
    public function getOverviewListsAge(): ?int
    {
        $oldestFetchedAt = null;

        foreach ($this->getWarmedListCacheIds() as $cacheId) {
            $entry = $this->cache->fetch($cacheId);

            if (false === $entry) {
                return null;
            }

            $oldestFetchedAt = min($oldestFetchedAt ?? $entry['fetchedAt'], $entry['fetchedAt']);
        }

        return Date::getNowTimestamp() - $oldestFetchedAt;
    }

    public function searchForPlugins($keywords, $query, $sort, $purchaseType)
    {
        return $this->searchFor('plugins', $keywords, $query, $sort, $purchaseType);
    }

    /**
     * The overview list queries {@link refreshOverviewListCaches()} refills, which are also the ones
     * the overview page asks for and the only ones held for the longer timeout.
     *
     * Everything that has to agree on this set reads it from here: warming them, deciding how long
     * to keep them, and resolving a plugin out of them. Encoding it a second time somewhere else
     * would fail silently - a warmed entry nothing reads back, or a read that never hits.
     *
     * @return array[] list of [action, purchase type]
     */
    private static function getWarmedOverviewLists(): array
    {
        return [
            ['plugins', PurchaseType::TYPE_ALL],
            // the premium filter is its own cache entry
            ['plugins', PurchaseType::TYPE_PAID],
            ['themes', PurchaseType::TYPE_ALL],
        ];
    }

    /**
     * Names a warmed list in a log line: "plugins", "paid plugins" or "themes".
     */
    private static function describeWarmedList(string $action, string $purchaseType): string
    {
        return trim($purchaseType . ' ' . $action);
    }

    /**
     * Returns the given plugin out of whichever warmed overview list already holds it, and null
     * when none of them is cached or none lists it.
     *
     * Lets a caller that only wants one plugin prefer the cached copy without a cold cache turning
     * that into a download of a whole catalogue, which is far more expensive than the single
     * {@link getPluginInfo()} request it would otherwise make.
     *
     * @return array<string, mixed>|null
     */
    public function findInCachedOverviewLists(string $pluginName): ?array
    {
        foreach (self::getWarmedOverviewLists() as list($action, $purchaseType)) {
            $listed = $this->searchFor($action, '', '', Sort::DEFAULT_SORT, $purchaseType, true);

            if (null === $listed) {
                continue;
            }

            foreach ($listed as $plugin) {
                if (isset($plugin['name']) && $plugin['name'] === $pluginName) {
                    return $plugin;
                }
            }
        }

        return null;
    }

    private function searchFor($action, $keywords, $query, $sort, $purchaseType, bool $cachedOnly = false): ?array
    {
        $params = ['keywords' => $keywords, 'query' => $query, 'sort' => $sort, 'purchase_type' => $purchaseType];

        $response = $this->fetch($action, $params, false, $cachedOnly);

        if ($cachedOnly && null === $response) {
            return null;
        }

        if (!empty($response['plugins'])) {
            return $this->removeNotNeededPluginsFromResponse($response);
        }

        return [];
    }

    private function removeNotNeededPluginsFromResponse($response)
    {
        foreach ($response['plugins'] as $index => $plugin) {
            if ($this->shouldIgnorePlugin($plugin)) {
                unset($response['plugins'][$index]);
                continue;
            }
        }
        return array_values($response['plugins']);
    }

    private function shouldIgnorePlugin($plugin)
    {
        return !empty($plugin['isCustomPlugin']);
    }

    public function searchForThemes($keywords, $query, $sort, $purchaseType)
    {
        return $this->searchFor('themes', $keywords, $query, $sort, $purchaseType);
    }

    /**
     * @param bool $cachedOnly Return null instead of requesting the action when it is not cached.
     */
    private function fetch($action, $params, bool $forceRefresh = false, bool $cachedOnly = false)
    {
        $params = $this->withEnvironmentParams($params);
        $isWarmedList = $this->isWarmedList($action, $params);
        $cacheId = $this->getCacheKey($action, Http::buildQuery($params), $isWarmedList);

        $cached = $forceRefresh ? false : $this->cache->fetch($cacheId);

        if ($cached !== false && !$isWarmedList) {
            return $cached;
        }

        $list = $isWarmedList ? self::describeWarmedList($action, $params['purchase_type'] ?? PurchaseType::TYPE_ALL) : '';

        if ($cached !== false && !$this->shouldRefreshInRequest($cached['fetchedAt'], $cachedOnly, $cacheId, $list)) {
            return $cached['response'];
        }

        if ($cached === false && $cachedOnly) {
            return null;
        }

        try {
            $result = $this->service->fetch($action, $params);
        } catch (PhpException $e) {
            // not only Service\Exception: core/Http.php reports an unreachable Marketplace as a
            // plain \Exception
            if ($cached !== false) {
                $this->logger->warning('Could not refresh the Marketplace {list} list, serving the cached one: {message}', [
                    'list' => $list,
                    'message' => $e->getMessage(),
                    'ignoreInScreenWriter' => true,
                ]);

                return $cached['response'];
            }

            if (!$e instanceof Service\Exception) {
                throw $e;
            }

            throw new Exception($e->getMessage(), $e->getCode());
        }

        if ($isWarmedList) {
            $this->cache->save(
                $cacheId,
                ['fetchedAt' => Date::getNowTimestamp(), 'response' => $result],
                self::PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS
            );
        } else {
            $this->cache->save($cacheId, $result, self::CACHE_TIMEOUT_IN_SECONDS);
        }

        return $result;
    }

    /**
     * Decides whether a warmed list fetched at the given time has to be refetched by the current
     * request rather than served as it is.
     *
     * A visit that finds the list older than {@link PLUGIN_LIST_REFRESH_AFTER_SECONDS} is served it
     * anyway, and the refetch is left to a detached process. Only where no process can be spawned,
     * or once the list is older than {@link PLUGIN_LIST_REFRESH_IN_REQUEST_AFTER_SECONDS}, does the
     * visit refetch it itself, and a visit that does so holds the rest off for
     * {@link IN_REQUEST_REFRESH_HOLD_SECONDS}. Console commands and scheduler runs never refetch
     * here: across installations they run at the same minutes of the hour, and
     * {@link \Piwik\Plugins\Marketplace\Tasks::warmCacheEntries()} refreshes the lists away from those.
     */
    private function shouldRefreshInRequest(int $fetchedAt, bool $cachedOnly, string $cacheId, string $list): bool
    {
        $now = Date::getNowTimestamp();
        $age = $now - $fetchedAt;

        if ($age < self::PLUGIN_LIST_REFRESH_AFTER_SECONDS) {
            return false;
        }

        try {
            $warmer = $this->getBackgroundWarmer();

            if (!$warmer->isServingVisit()) {
                return false;
            }

            if ($cachedOnly) {
                $warmer->refreshNow(self::PLUGIN_LIST_REFRESH_AFTER_SECONDS);

                return false;
            }

            if ($this->isInRequestRefreshHeld($cacheId, $now)) {
                return false;
            }

            if ($age >= self::PLUGIN_LIST_REFRESH_IN_REQUEST_AFTER_SECONDS) {
                $this->logger->warning('The Marketplace {list} list is {age} seconds old, refreshing it in this request', [
                    'list' => $list,
                    'age' => $age,
                    'ignoreInScreenWriter' => true,
                ]);
            } elseif ($warmer->refreshNow(self::PLUGIN_LIST_REFRESH_AFTER_SECONDS)) {
                return false;
            }

            $this->cache->save($cacheId . '.refreshedInRequestAt', $now, self::IN_REQUEST_REFRESH_HOLD_SECONDS);

            return true;
        } catch (\Throwable $e) {
            // without knowing whether this is a visit, refetching could land on the scheduler's minutes
            $this->logger->warning('Could not refresh the Marketplace lists in the background: {message}', [
                'message' => $e->getMessage(),
                'ignoreInScreenWriter' => true,
            ]);

            return false;
        }
    }

    /**
     * Whether a visit refetched the list in the request recently. The most likely reason a visit
     * has to is that the Marketplace cannot be reached, and then every visit that tried would wait
     * out the request timeout.
     */
    private function isInRequestRefreshHeld(string $cacheId, int $now): bool
    {
        $refreshedAt = $this->cache->fetch($cacheId . '.refreshedInRequestAt');

        return false !== $refreshedAt && $now - (int) $refreshedAt < self::IN_REQUEST_REFRESH_HOLD_SECONDS;
    }

    /**
     * Adds what the Marketplace is told about this installation to a request's own parameters.
     */
    private function withEnvironmentParams(array $params): array
    {
        ksort($params); // sort params so cache is reused more often even if param order is different

        $releaseChannel = $this->environment->getReleaseChannel();

        if (!empty($releaseChannel)) {
            $params['release_channel'] = $releaseChannel;
        }

        $params['prefer_stable'] = (int)$this->environment->doesPreferStable();
        $params['piwik'] = $this->environment->getPiwikVersion();
        $params['php'] = $this->environment->getWebPhpVersion();
        $params['mysql'] = $this->environment->getMySQLVersion();
        $params['num_users'] = $this->environment->getNumUsers();
        $params['num_websites'] = $this->environment->getNumWebsites();

        $uid = $this->environment->getUniqueId();
        if (!empty($uid)) {
            $params['uid'] = $uid;
        }

        return $params;
    }

    /**
     * Whether the given request is one of {@link getWarmedOverviewLists()}, which are cached along
     * with when they were fetched and kept for {@link PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS}.
     *
     * Everything else keeps the short timeout, so update detection, a plugin's own details and the
     * consumer's licenses are no more stale than before. Each of those is also cleared outright by
     * the events that change it, see the callers of {@link clearAllCacheEntries()}.
     */
    private function isWarmedList(string $action, array $params): bool
    {
        // anything outside the warmed set - a search, another sort, a purchase type nobody warms -
        // would only be made staler by a longer timeout, never faster
        $warmedPurchaseTypes = [];

        foreach (self::getWarmedOverviewLists() as list($warmedAction, $warmedPurchaseType)) {
            $warmedPurchaseTypes[$warmedAction][] = $warmedPurchaseType;
        }

        if (!isset($warmedPurchaseTypes[$action])) {
            return false;
        }

        $purchaseType = isset($params['purchase_type'])
            ? $params['purchase_type']
            : PurchaseType::TYPE_ALL;

        // compared against '' rather than empty(), which would treat a search for "0" as unfiltered
        return (!isset($params['keywords']) || $params['keywords'] === '')
            && (!isset($params['query']) || $params['query'] === '')
            && isset($params['sort'])
            && $params['sort'] === Sort::DEFAULT_SORT
            && in_array($purchaseType, $warmedPurchaseTypes[$action], true);
    }

    public function clearAllCacheEntries()
    {
        $this->cache->flushAll();
    }

    /**
     * Clears everything {@link clearAllCacheEntries()} does except the overview lists, which carry
     * when they were fetched and are refreshed by their age anyway. Every installation flushes at
     * the same minute, and flushing them too would leave anything reading them before the next
     * refill, such as the plugin update check, to ask the Marketplace about each plugin in turn.
     */
    public function clearCacheEntriesExceptOverviewLists(): void
    {
        $kept = [];

        foreach ($this->getWarmedListCacheIds() as $cacheId) {
            $entry = $this->cache->fetch($cacheId);

            if (false !== $entry) {
                $kept[$cacheId] = $entry;
            }
        }

        $this->cache->flushAll();

        $now = Date::getNowTimestamp();

        foreach ($kept as $cacheId => $entry) {
            // only for what was left of their timeout, so a flush never keeps a list for longer
            $timeout = self::PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS - ($now - (int) $entry['fetchedAt']);

            if ($timeout > 0) {
                $this->cache->save($cacheId, $entry, $timeout);
            }
        }
    }

    /**
     * @return string[]
     */
    private function getWarmedListCacheIds(): array
    {
        $cacheIds = [];

        foreach (self::getWarmedOverviewLists() as list($action, $purchaseType)) {
            $params = $this->withEnvironmentParams([
                'keywords' => '',
                'query' => '',
                'sort' => Sort::DEFAULT_SORT,
                'purchase_type' => $purchaseType,
            ]);
            $cacheIds[] = $this->getCacheKey($action, Http::buildQuery($params), true);
        }

        return $cacheIds;
    }

    private function getCacheKey($action, $query, bool $isWarmedList = false)
    {
        $version = $this->service->getVersion();

        // a warmed list is stored along with when it was fetched, so it needs a key that an entry
        // cached before this change, holding the bare response, can never be read back from
        return sprintf(
            'marketplace.api.%s.%s.%s%s',
            $version,
            str_replace('/', '.', $action),
            $isWarmedList ? 'stamped.' : '',
            md5($query)
        );
    }

    /**
     * @param  $pluginOrThemeName
     * @return string
     * @throws Exception
     */
    public function getDownloadUrl($pluginOrThemeName)
    {
        $plugin = $this->getPluginInfo($pluginOrThemeName);

        if (empty($plugin['isDownloadable'])) {
            throw new Exception('Plugin is not downloadable. License may be missing or expired.');
        }

        if (empty($plugin['versions'])) {
            throw new Exception('Plugin has no versions.');
        }

        $latestVersion = array_pop($plugin['versions']);
        $downloadUrl = $latestVersion['download'];

        $url = $this->service->getDomain() . $downloadUrl . '?coreVersion=' . $this->environment->getPiwikVersion();

        $uid = $this->environment->getUniqueId();
        if (!empty($uid)) {
            $url .= '&uid=' . $uid;
        }

        return $url;
    }

    /**
     * Return the api.matomo.org URL with the correct protocol prefix
     */
    public static function getApiServiceUrl(): ?string
    {
        // Default is now https://
        $url = GeneralConfig::getConfigValue('api_service_url');

        if (GeneralConfig::getConfigValue('force_matomo_http_request')) {
            // http is being forced, downgrade the protocol to http
            $url = str_replace('https', 'http', $url);
        }

        return $url;
    }
}
