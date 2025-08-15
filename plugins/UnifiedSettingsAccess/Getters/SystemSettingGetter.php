<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UnifiedSettingsAccess\Getters;

use Piwik\Exception\Exception;
use Piwik\Settings\Plugin\SystemSetting;

class SystemSettingGetter extends SettingGetter
{
    public function getSetting()
    {
        try {
            $setting = new SystemSetting($this->settingName, $this->defaultValue, $this->type, $this->pluginName);
            $this->myValue = $setting->getValue();

            $this->fallbackDefaultValue();
            $this->convertValue();
            $this->postUpdateEvent();

            return $this->myValue;
        } catch (\Exception $e) {
            throw new Exception(sprintf("System setting '%s' not supported. Error: %s", $this->settingName, $e->getMessage()));
        }
    }
}
