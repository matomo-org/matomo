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
use Piwik\Plugin\Manager;

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

        return $marketplace->getPluginInfo($pluginName);
    }

    public function searchPlugins($query, $sort, $themesOnly)
    {
        if ($themesOnly) {
            $plugins = $this->client->searchForThemes('', $query, $sort);
        } else {
            $plugins = $this->client->searchForPlugins('', $query, $sort);
        }

        $dateFormat = Piwik::translate('CoreHome_ShortDateFormatWithYear');

        foreach ($plugins as &$plugin) {
            $plugin['canBeUpdated'] = $this->hasPluginUpdate($plugin);
            $plugin['isInstalled'] = \Piwik\Plugin\Manager::getInstance()->isPluginLoaded($plugin['name']);
            $plugin['lastUpdated'] = Date::factory($plugin['lastUpdated'])->getLocalized($dateFormat);
        }

        return $plugins;
    }

    private function hasPluginUpdate($plugin)
    {
        if (empty($plugin['name'])) {
            return false;
        }

        $pluginsHavingUpdate = $this->getPluginsHavingUpdate($plugin['isTheme']);

        foreach ($pluginsHavingUpdate as $pluginHavingUpdate) {
            if ($plugin['name'] == $pluginHavingUpdate['name']) {
                return true;
            }
        }

        return false;
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

}
