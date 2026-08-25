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
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Plugin;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;
use Piwik\SettingsServer;
use Piwik\Log\LoggerInterface;

class Client
{
    public const CACHE_TIMEOUT_IN_SECONDS = 3600;
    public const PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS = 5400;
    public const HTTP_REQUEST_TIMEOUT = 60;

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

            if (isset($listed[$name])) {
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
     * Returns the whole plugin and theme catalogue keyed by plugin name.
     *
     * Both lists are already cached for {@link PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS} and refilled by
     * a scheduled task, and a list entry carries the same fields as an info response, so resolving
     * an update out of them costs nothing where asking about each plugin in turn cost one request
     * per plugin having an update. Returns an empty array if either list cannot be fetched, leaving
     * the caller to fall back to those individual requests.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getListedPluginsByName(): array
    {
        $listed = [];

        try {
            $lists = [
                $this->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL),
                $this->searchForThemes('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL),
            ];
        } catch (PhpException $e) {
            $this->logger->error($e->getMessage());
            return [];
        }

        foreach ($lists as $list) {
            foreach ($list as $plugin) {
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

    public function searchForPlugins($keywords, $query, $sort, $purchaseType)
    {
        $response = $this->fetch('plugins', array('keywords' => $keywords, 'query' => $query, 'sort' => $sort, 'purchase_type' => $purchaseType));

        if (!empty($response['plugins'])) {
            return $this->removeNotNeededPluginsFromResponse($response);
        }

        return array();
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
        $response = $this->fetch('themes', array('keywords' => $keywords, 'query' => $query, 'sort' => $sort, 'purchase_type' => $purchaseType));

        if (!empty($response['plugins'])) {
            return $this->removeNotNeededPluginsFromResponse($response);
        }

        return array();
    }

    private function fetch($action, $params)
    {
        ksort($params); // sort params so cache is reused more often even if param order is different

        $releaseChannel = $this->environment->getReleaseChannel();

        if (!empty($releaseChannel)) {
            $params['release_channel'] = $releaseChannel;
        }

        $params['prefer_stable'] = (int)$this->environment->doesPreferStable();
        $params['piwik'] = $this->environment->getPiwikVersion();
        $params['php'] = $this->environment->getPhpVersion();
        $params['mysql'] = $this->environment->getMySQLVersion();
        $params['num_users'] = $this->environment->getNumUsers();
        $params['num_websites'] = $this->environment->getNumWebsites();

        $uid = $this->environment->getUniqueId();
        if (!empty($uid)) {
            $params['uid'] = $uid;
        }

        $query = Http::buildQuery($params);
        $cacheId = $this->getCacheKey($action, $query);

        $result = $this->cache->fetch($cacheId);

        if ($result !== false) {
            return $result;
        }

        try {
            $result = $this->service->fetch($action, $params);
        } catch (Service\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $this->cache->save($cacheId, $result, $this->getCacheTimeout($action, $params));

        return $result;
    }

    /**
     * Returns how long a response for the given action may be served from the cache.
     *
     * The plugin and theme lists only have to outlive the interval the scheduled task refills them
     * at, which is what keeps the next visitor from paying for the requests the overview page needs.
     * Ninety minutes clears that hourly task without holding prices and requirements — both derived
     * from these responses — much beyond the hour they were held before. A longer window would only
     * buy tolerance for repeatedly missed scheduled runs, at the cost of staler prices.
     *
     * Everything else keeps the short timeout, so update detection, a plugin's own details and the
     * consumer's licenses are no more stale than before. Each of those is also cleared outright by
     * the events that change it, see the callers of {@link clearAllCacheEntries()}.
     */
    private function getCacheTimeout(string $action, array $params): int
    {
        // exactly the queries Tasks::warmCacheEntries() refills, which are also the ones the
        // overview asks for. Anything else - a search, another sort, a purchase type nobody warms -
        // would only be made staler by a longer timeout, never faster.
        $warmedPurchaseTypes = [
            'plugins' => [PurchaseType::TYPE_ALL, PurchaseType::TYPE_PAID],
            'themes' => [PurchaseType::TYPE_ALL],
        ];

        if (!isset($warmedPurchaseTypes[$action])) {
            return self::CACHE_TIMEOUT_IN_SECONDS;
        }

        $purchaseType = isset($params['purchase_type'])
            ? $params['purchase_type']
            : PurchaseType::TYPE_ALL;

        $isWarmedQuery = empty($params['keywords'])
            && empty($params['query'])
            && isset($params['sort'])
            && $params['sort'] === Sort::DEFAULT_SORT
            && in_array($purchaseType, $warmedPurchaseTypes[$action], true);

        return $isWarmedQuery
            ? self::PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS
            : self::CACHE_TIMEOUT_IN_SECONDS;
    }

    public function clearAllCacheEntries()
    {
        $this->cache->flushAll();
    }

    private function getCacheKey($action, $query)
    {
        $version = $this->service->getVersion();

        return sprintf('marketplace.api.%s.%s.%s', $version, str_replace('/', '.', $action), md5($query));
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
