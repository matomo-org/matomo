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

use Piwik\Date;
use Piwik\Piwik;

/**
 *
 * @package CorePluginsAdmin
 */
class Marketplace
{
    /**
     * @var MarketplaceApiClient
     */
    private $client;

    public function __construct()
    {
        $this->client = new MarketplaceApiClient();
    }

    public function getPluginInfo($pluginName)
    {
        $marketplace = new MarketplaceApiClient();

        $plugin = $marketplace->getPluginInfo($pluginName);
        $plugin = $this->enrichPluginInformation($plugin);

        return $plugin;
    }

    public function getAvailablePluginNames($themesOnly)
    {
        if ($themesOnly) {
            $plugins = $this->client->searchForThemes('', '', '');
        } else {
            $plugins = $this->client->searchForPlugins('', '', '');
        }

        $names = array();
        foreach ($plugins as $plugin) {
            $names[] = $plugin['name'];
        }

        return $names;
    }

    public function searchPlugins($query, $sort, $themesOnly)
    {
        if ($themesOnly) {
            $plugins = $this->client->searchForThemes('', $query, $sort);
        } else {
            $plugins = $this->client->searchForPlugins('', $query, $sort);
        }

        foreach ($plugins as $key => $plugin) {
            $plugins[$key] = $this->enrichPluginInformation($plugin);
        }

        return $plugins;
    }

    private function getPluginUpdateInformation($plugin)
    {
        if (empty($plugin['name'])) {
            return;
        }

        $pluginsHavingUpdate = $this->getPluginsHavingUpdate($plugin['isTheme']);

        foreach ($pluginsHavingUpdate as $pluginHavingUpdate) {
            if ($plugin['name'] == $pluginHavingUpdate['name']) {
                return $pluginHavingUpdate;
            }
        }
    }

    private function hasPluginUpdate($plugin)
    {
        $update = $this->getPluginUpdateInformation($plugin);

        return !empty($update);
    }

    /**
     * @param bool $themesOnly
     * @return array
     */
    public function getPluginsHavingUpdate($themesOnly)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();
        $pluginManager->returnLoadedPluginsInfo();
        $loadedPlugins = $pluginManager->getLoadedPlugins();

        try {
            $pluginsHavingUpdate = $this->client->getInfoOfPluginsHavingUpdate($loadedPlugins, $themesOnly);

        } catch (\Exception $e) {
            $pluginsHavingUpdate = array();
        }

        foreach ($pluginsHavingUpdate as &$updatePlugin) {
            foreach ($loadedPlugins as $loadedPlugin) {

                if (!empty($updatePlugin['name'])
                    && $loadedPlugin->getPluginName() == $updatePlugin['name']
                ) {

                    $updatePlugin['currentVersion'] = $loadedPlugin->getVersion();
                    $updatePlugin['isActivated'] = $pluginManager->isPluginActivated($updatePlugin['name']);
                    break;
                }
            }
        }

        return $pluginsHavingUpdate;
    }

    private function enrichPluginInformation($plugin)
    {
        $dateFormat = Piwik::translate('CoreHome_ShortDateFormatWithYear');

        $plugin['canBeUpdated'] = $this->hasPluginUpdate($plugin);
        $plugin['isInstalled']  = \Piwik\Plugin\Manager::getInstance()->isPluginLoaded($plugin['name']);
        $plugin['lastUpdated']  = Date::factory($plugin['lastUpdated'])->getLocalized($dateFormat);

        if ($plugin['canBeUpdated']) {
            $pluginUpdate = $this->getPluginUpdateInformation($plugin);
            $plugin['repositoryChangelogUrl'] = $pluginUpdate['repositoryChangelogUrl'];
            $plugin['currentVersion']         = $pluginUpdate['currentVersion'];
        }

        if (!empty($plugin['activity']['lastCommitDate'])
            && false === strpos($plugin['activity']['lastCommitDate'], '0000')) {

            $dateFormat = Piwik::translate('CoreHome_DateFormat');
            $plugin['activity']['lastCommitDate'] = Date::factory($plugin['activity']['lastCommitDate'])->getLocalized($dateFormat);
        } else {
            $plugin['activity']['lastCommitDate'] = null;
        }

        if (!empty($plugin['versions'])) {

            $dateFormat = Piwik::translate('CoreHome_DateFormat');
            
            foreach ($plugin['versions'] as $index => $version) {
                $plugin['versions'][$index]['release'] = Date::factory($version['release'])->getLocalized($dateFormat);
            }
        }

        return $plugin;
    }

}
