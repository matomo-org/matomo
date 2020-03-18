<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Marketplace\Api;

use Piwik\API\Request;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Plugin;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\SettingsServer;
use Exception as PhpException;
use Psr\Log\LoggerInterface;

/**
 *
 */
class Client
{
    const CACHE_TIMEOUT_IN_SECONDS = 3600;
    const HTTP_REQUEST_TIMEOUT = 60;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var Cache\Lazy
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

    public function __construct(Service $service, Cache\Lazy $cache, LoggerInterface $logger, Environment $environment)
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

    public function getPluginInfo($name)
    {
        $action = sprintf('plugins/%s/info', $name);

        $plugin = $this->fetch($action, array());

        if (!empty($plugin) && $this->shouldIgnorePlugin($plugin)) {
            return;
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
        try {
            $licenses = $this->fetch('consumer', array());
        } catch (Exception $e) {
            $licenses = null;
        }

        return $licenses;
    }

    public function isValidConsumer()
    {
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
        // care of and we always generate a random download filename.
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
                $params[] = array('name' => $plugin->getPluginName(), 'version' => $plugin->getVersion(), 'activated' => (int) $isActivated);
            }
        }

        if (empty($params)) {
            return array();
        }

        $params = array('plugins' => $params);

        if ($this->service->hasAccessToken() && Plugin\Manager::getInstance()->isPluginActivated('MultiSites')) {
            $multiSites = Request::processRequest('MultiSites.getAll', array(
                'period' => 'month',
                'date' => 'previous1',
                'showColumns' => 'nb_pageviews'
            ));
            /** @var DataTable $multiSites */
            $numPagviews = 0;
            if ($multiSites && $multiSites->getRowsCount()) {
                foreach ($multiSites->getRows() as $row) {
                    $pageviews = $row->getColumn('nb_pageviews');
                    if ($pageviews) {
                        $numPagviews += $pageviews;
                    }
                }
            }
            $numPagviews = $numPagviews / 1000;
            if ($numPagviews < 50) {
                $params['bucket'] = 1;
            } elseif ($numPagviews < 100) {
                $params['bucket'] = 2;
            }elseif ($numPagviews < 300) {
                $params['bucket'] = 3;
            }elseif ($numPagviews < 600) {
                $params['bucket'] = 4;
            }elseif ($numPagviews < 1000) {
                $params['bucket'] = 5;
            }elseif ($numPagviews < 2000) {
                $params['bucket'] = 6;
            }elseif ($numPagviews < 5000) {
                $params['bucket'] = 7;
            }elseif ($numPagviews < 10000) {
                $params['bucket'] = 8;
            }elseif ($numPagviews < 25000) {
                $params['bucket'] = 9;
            }elseif ($numPagviews < 50000) {
                $params['bucket'] = 10;
            } else {
                $params['bucket'] = 11;
            }
        }

        $hasUpdates = $this->fetch('plugins/checkUpdates', array('plugins' => json_encode($params)));

        if (empty($hasUpdates)) {
            return array();
        }

        return $hasUpdates;
    }

    /**
     * @param  \Piwik\Plugin[] $plugins
     * @return array
     */
    public function getInfoOfPluginsHavingUpdate($plugins)
    {
        $hasUpdates = $this->checkUpdates($plugins);

        $pluginDetails = array();

        foreach ($hasUpdates as $pluginHavingUpdate) {
            if (empty($pluginHavingUpdate)) {
                continue;
            }

            try {
                $plugin = $this->getPluginInfo($pluginHavingUpdate['name']);
            } catch (PhpException $e) {
                $this->logger->error($e->getMessage());
                $plugin = null;
            }

            if (!empty($plugin)) {
                $plugin['repositoryChangelogUrl'] = $pluginHavingUpdate['repositoryChangelogUrl'];
                $pluginDetails[] = $plugin;
            }

        }

        return $pluginDetails;
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

        $params['prefer_stable'] = (int) $this->environment->doesPreferStable();
        $params['piwik'] = $this->environment->getPiwikVersion();
        $params['php'] = $this->environment->getPhpVersion();
        $params['mysql'] = $this->environment->getMySQLVersion();
        $params['num_users'] = $this->environment->getNumUsers();
        $params['num_websites'] = $this->environment->getNumWebsites();

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

        $this->cache->save($cacheId, $result, self::CACHE_TIMEOUT_IN_SECONDS);

        return $result;
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
     * @throws Exception
     * @return string
     */
    public function getDownloadUrl($pluginOrThemeName)
    {
        $plugin = $this->getPluginInfo($pluginOrThemeName);

        if (empty($plugin['versions'])) {
            throw new Exception('Plugin has no versions.');
        }

        $latestVersion = array_pop($plugin['versions']);
        $downloadUrl = $latestVersion['download'];

        return $this->service->getDomain() . $downloadUrl . '?coreVersion=' . $this->environment->getPiwikVersion();
    }

}
