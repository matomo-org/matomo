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
use Piwik\Updates;

class Updates_3_0_0_b3 extends Updates
{
    private $marketplaceEnabledConfigSetting = 'enable_marketplace';

    public static function isMajorUpdate()
    {
        return true;
    }

    public function doUpdate(Updater $updater)
    {
        $general = $this->getConfig()->General;

        // need to check against int(0) value, as if the config setting is not set at all its value is null
        if (isset($general[$this->marketplaceEnabledConfigSetting])) {
            $isMarketplaceEnabled = 0 !== $general[$this->marketplaceEnabledConfigSetting];

            $this->removeOldMarketplaceEnabledConfig();

            if ($isMarketplaceEnabled) {
                $this->activateMarketplacePlugin();
            }
        } else {
            $this->activateMarketplacePlugin();
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

    public function activateMarketplacePlugin()
    {
        $pluginManager = Plugin\Manager::getInstance();
        $pluginName = 'Marketplace';

        if (!$pluginManager->isPluginActivated($pluginName)) {
            $pluginManager->activatePlugin($pluginName);
        }
    }
}
