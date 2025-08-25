<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy\UnifiedSettingsAccess\Getters;

use Piwik\Config;
use Piwik\Exception\Exception;
use Piwik\Policy\SettingValues\GenericSettingValue;

class ConfigSettingGetter extends SettingGetter
{
    public function hasSetting(): bool
    {
        $config = Config::getInstance()->{$this->pluginName};
        return $config && array_key_exists($this->settingName, $config);
    }

    public function getSetting(): GenericSettingValue
    {
        try {
            $this->myValue = Config::getInstance()->{$this->pluginName}[$this->settingName];

            $this->processValue();

            return new GenericSettingValue($this->idSite, $this->myValue, '');
        } catch (\Exception $e) {
            throw new Exception(sprintf("Config setting '%s' not supported. Error: %s", $this->settingName, $e->getMessage()));
        }
    }
}
