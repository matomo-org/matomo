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
use Piwik\Http;

/**
 *
 * @package CorePluginsAdmin
 */
class MarketplaceApiClient
{
    private $domain = 'http://plugins.piwik.org/';

    /**
     * @var array   array(pluginName => stdClass pluginInfo)
     */
    private static $pluginCache = array();

    private function fetch($action, $params)
    {
        $endpoint = $this->domain . '/api/1.0/';
        $query    = http_build_query($params);

        $url = sprintf('%s%s?%s', $endpoint, $action, $query);

        $result = Http::sendHttpRequest($url, 5);
        $result = json_decode($result);

        if (!empty($result->error)) {
            // TODO create own exception
            throw new \Exception($result->error);
        }

        return $result;
    }

    public function getPluginInfo($name)
    {
        if (array_key_exists($name, static::$pluginCache)) {
            return static::$pluginCache[$name];
        }

        $action = sprintf('plugins/%s/info', $name);
        static::$pluginCache[$name] = $this->fetch($action, array());

        return static::$pluginCache[$name];
    }

    public function download($pluginOrThemeName, $target)
    {
        $plugin = $this->getPluginInfo($pluginOrThemeName);

        if (empty($plugin)) {
            // TODO throw exception notExistingPlugin
            return;
        }

        $latestVersion = array_pop($plugin->versions);

        $downloadUrl = $latestVersion->download;

        $success = Http::fetchRemoteFile($this->domain . $downloadUrl, $target);

        return $success;
    }

    /**
     * @param \Piwik\Plugin[] $plugins
     */
    public function checkUpdates($plugins)
    {
        $params = array();

        foreach ($plugins as $plugin) {
            $pluginName = $plugin->getPluginName();

            $params[] = array('name' => $pluginName, 'version' => $plugin->getVersion());
        }

        $params = array('plugins' => $params);

        $hasUpdates = $this->fetch('plugins/checkUpdates', array('plugins' => json_encode($params)));

        if (empty($hasUpdates)) {
            return array();
        }

        return $hasUpdates;
    }

    /**
     * @param \Piwik\Plugin[] $plugins
     */
    public function getInfoOfPluginsHavingUpdate($plugins)
    {
        $hasUpdates = $this->checkUpdates($plugins);

        $pluginDetails = array();
        foreach ($hasUpdates as $pluginHavingUpdate) {
            $plugin = $this->getPluginInfo($pluginHavingUpdate->name);
            if (!$plugin->isTheme) {
                $pluginDetails[] = $plugin;
            }
        }

        return $pluginDetails;
    }

    /**
     * @param \Piwik\Plugin[] $plugins
     */
    public function getInfoOfThemesHavingUpdate($plugins)
    {
        $hasUpdates = $this->checkUpdates($plugins);

        $pluginDetails = array();
        foreach ($hasUpdates as $pluginHavingUpdate) {
            $plugin = $this->getPluginInfo($pluginHavingUpdate->name);
            if ($plugin->isTheme) {
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

}
