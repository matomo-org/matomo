<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UnifiedSettingsAccess\Getters;

use Piwik\Config;
use Piwik\Exception\Exception;

class ConfigSettingGetter extends SettingGetter
{
    public function getSetting()
    {
        try {
            $this->myValue = Config::getInstance()->{$this->pluginName}[$this->settingName];

            $this->fallbackDefaultValue();
            $this->convertValue();
            $this->postUpdateEvent();

            return $this->myValue;
        } catch (\Exception $e) {
            throw new Exception(sprintf("Config setting '%s' not supported. Error: %s", $this->settingName, $e->getMessage()));
        }
    }
}
