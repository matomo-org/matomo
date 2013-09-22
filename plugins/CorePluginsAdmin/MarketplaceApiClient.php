<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CorePluginsAdmin
 */
namespace Piwik\Plugins\CorePluginsAdmin;
use Piwik\CacheFile;
use Piwik\Http;

/**
 *
 * @package CorePluginsAdmin
 */
class MarketplaceApiClient
{
    private $domain = 'http://plugins.piwik.org';

    /**
     * @var CacheFile
     */
    private $cache = null;

    public function __construct()
    {
        $this->cache = new CacheFile('marketplace', 1200);
    }

    public static function clearAllCacheEntries()
    {
        $cache = new CacheFile('marketplace');
        $cache->deleteAll();
    }

    public function getPluginInfo($name)
    {
        $action = sprintf('plugins/%s/info', $name);

        return $this->fetch($action, array());
    }

    public function download($pluginOrThemeName, $target)
    {
        $plugin = $this->getPluginInfo($pluginOrThemeName);

        if (empty($plugin->versions)) {
            return false;
        }

        $latestVersion = array_pop($plugin->versions);
        $downloadUrl   = $latestVersion->download;

        $success = Http::fetchRemoteFile($this->domain . $downloadUrl, $target);

        return $success;
    }

    /**
     * @param \Piwik\Plugin[] $plugins
     * @return array|mixed
     */
    public function checkUpdates($plugins)
    {
        $params = array();

        foreach ($plugins as $plugin) {
            $params[] = array('name' => $plugin->getPluginName(), 'version' => $plugin->getVersion());
        }

        $params = array('plugins' => $params);

        $hasUpdates = $this->fetch('plugins/checkUpdates', array('plugins' => json_encode($params)));

        if (empty($hasUpdates)) {
            return array();
        }

        return $hasUpdates;
    }

    /**
     * @param  \Piwik\Plugin[] $plugins
     * @param  bool            $themesOnly
     * @return array
     */
    public function getInfoOfPluginsHavingUpdate($plugins, $themesOnly)
    {
        $hasUpdates = $this->checkUpdates($plugins);

        $pluginDetails = array();

        foreach ($hasUpdates as $pluginHavingUpdate) {
            $plugin = $this->getPluginInfo($pluginHavingUpdate->name);

            if (!empty($plugin->isTheme) == $themesOnly) {
                $pluginDetails[] = $plugin;
            }
        }

        return $pluginDetails;
    }

    public function searchForPlugins($keywords, $query, $sort)
    {
        $response = $this->fetch('plugins', array('keywords' => $keywords, 'query' => $query, 'sort' => $sort));

        if (!empty($response->plugins)) {
            return $response->plugins;
        }

        return array();
    }

    public function searchForThemes($keywords, $query, $sort)
    {
        $response = $this->fetch('themes', array('keywords' => $keywords, 'query' => $query, 'sort' => $sort));

        if (!empty($response->plugins)) {
            return $response->plugins;
        }

        return array();
    }

    private function fetch($action, $params)
    {
        $query  = http_build_query($params);
        $result = $this->getCachedResult($action, $query);

        if (false === $result) {
            $endpoint = $this->domain . '/api/1.0/';
            $url      = sprintf('%s%s?%s', $endpoint, $action, $query);
            $result   = Http::sendHttpRequest($url, 5);
            $this->cacheResult($action, $query, $result);
        }

        $result = json_decode($result);

        if (is_null($result)) {
            throw new MarketplaceApiException('Failure during communication with marketplace, unable to read response');
        }

        if (!empty($result->error)) {
            throw new MarketplaceApiException($result->error);
        }

        return $result;
    }

    private function getCachedResult($action, $query)
    {
        $cacheKey = $this->getCacheKey($action, $query);

        return $this->cache->get($cacheKey);
    }

    private function cacheResult($action, $query, $result)
    {
        $cacheKey = $this->getCacheKey($action, $query);

        $this->cache->set($cacheKey, $result);
    }

    private function getCacheKey($action, $query)
    {
        return sprintf('api.1.0.%s.%s', str_replace('/', '.', $action), md5($query));
    }

}
