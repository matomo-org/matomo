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
use Piwik\PluginsManager;

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

    public function searchPlugins($query, $sort, $themesOnly)
    {
        if ($themesOnly) {
            $plugins    = $this->client->searchForThemes('', $query, $sort);
        } else {
            $plugins    = $this->client->searchForPlugins('', $query, $sort);
        }

        $dateFormat = Piwik_Translate('CoreHome_ShortDateFormatWithYear');

        foreach ($plugins as $plugin) {
            $plugin->isInstalled = PluginsManager::getInstance()->isPluginLoaded($plugin->name);
            $plugin->lastUpdated = Date::factory($plugin->lastUpdated)->getLocalized($dateFormat);
        }

        return $plugins;
    }

    /**
     * @param bool $themesOnly
     * @return array
     */
    public function getPluginsHavingUpdate($themesOnly)
    {
        $loadedPlugins = PluginsManager::getInstance()->getLoadedPlugins();

        try {
            if ($themesOnly) {
                $pluginsHavingUpdate = $this->client->getInfoOfThemesHavingUpdate($loadedPlugins);
            } else {
                $pluginsHavingUpdate = $this->client->getInfoOfPluginsHavingUpdate($loadedPlugins);
            }
        } catch (\Exception $e) {
            $pluginsHavingUpdate = array();
        }

        foreach ($pluginsHavingUpdate as $updatePlugin) {
            foreach ($loadedPlugins as $loadedPlugin) {

                if ($loadedPlugin->getPluginName() == $updatePlugin->name) {
                    $updatePlugin->currentVersion = $loadedPlugin->getVersion();
                    $updatePlugin->isActivated = PluginsManager::getInstance()->isPluginActivated($updatePlugin->name);
                    break;

                }
            }

        }
        return $pluginsHavingUpdate;
    }

}
