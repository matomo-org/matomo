<?php

namespace Piwik\Plugins\UnifiedSettingsAccess;

use Piwik\Settings\Measurable\MeasurableSetting;

class MeasurableSettingGetter extends SettingGetter
{
    public function getSetting()
    {
        $setting = new MeasurableSetting(
            $this->settingName,
            $this->defaultValue,
            $this->type,
            $this->pluginName,
            $this->idSite
        );
        $this->myValue = $setting->getValue();

        $this->fallbackDefaultValue();
        $this->convertValue();
        $this->postUpdateEvent();

        return $this->myValue;
    }
}
