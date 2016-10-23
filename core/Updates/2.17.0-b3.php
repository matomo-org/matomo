<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Plugin;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 */
class Updates_2_17_0_b3 extends PiwikUpdates
{
    private $marketplaceEnabledConfigSetting = 'enable_marketplace';

    public function doUpdate(Updater $updater)
    {
        $general = $this->getConfig()->General;

        // need to check against int(0) value, as if the config setting is not set at all its value is null
        $isMarketplaceEnabled = 0 !== @$general[$this->marketplaceEnabledConfigSetting];

        $this->removeOldMarketplaceEnabledConfig();

        $pluginManager = Plugin\Manager::getInstance();
        $pluginName = 'Marketplace';

        if ($isMarketplaceEnabled &&
            !$pluginManager->isPluginActivated($pluginName)) {
            $pluginManager->activatePlugin($pluginName);
        }
    }

    private function getConfig()
    {
        return Config::getInstance();
    }

    private function removeOldMarketplaceEnabledConfig()
    {
        $config  = $this->getConfig();
        $general = $config->General;

        if (array_key_exists($this->marketplaceEnabledConfigSetting, $general)) {
            unset($general[$this->marketplaceEnabledConfigSetting]);

            $config->General = $general;
            $config->forceSave();
        }
    }
}
