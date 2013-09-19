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

use Piwik\Piwik;
use Piwik\PluginsManager;

/**
 *
 * @package CorePluginsAdmin
 */
class Marketplace
{

    /**
     * @param bool $themesOnly
     * @return array
     */
    public function getPluginsHavingUpdate($themesOnly)
    {
        $loadedPlugins = PluginsManager::getInstance()->getLoadedPlugins();

        $marketplace   = new MarketplaceApiClient();

        try {
            if ($themesOnly) {
                $pluginsHavingUpdate = $marketplace->getInfoOfThemesHavingUpdate($loadedPlugins);
            } else {
                $pluginsHavingUpdate = $marketplace->getInfoOfPluginsHavingUpdate($loadedPlugins);
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
