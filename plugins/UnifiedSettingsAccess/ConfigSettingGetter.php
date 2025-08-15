<?php

namespace Piwik\Plugins\UnifiedSettingsAccess;

use Piwik\Config;

class ConfigSettingGetter extends SettingGetter
{
    public function getSetting()
    {
        $this->myValue = Config::getInstance()->{$this->pluginName}[$this->settingName];

        $this->fallbackDefaultValue();
        $this->convertValue();
        $this->postUpdateEvent();

        return $this->myValue;
    }
}
