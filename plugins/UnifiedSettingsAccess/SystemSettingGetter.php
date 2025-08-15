<?php

namespace Piwik\Plugins\UnifiedSettingsAccess;

use Piwik\Settings\Plugin\SystemSetting;

class SystemSettingGetter extends SettingGetter
{
    public function getSetting()
    {
        $setting = new SystemSetting($this->settingName, $this->defaultValue, $this->type, $this->pluginName);
        $this->myValue = $setting->getValue();

        $this->fallbackDefaultValue();
        $this->convertValue();
        $this->postUpdateEvent();

        return $this->myValue;
    }
}
