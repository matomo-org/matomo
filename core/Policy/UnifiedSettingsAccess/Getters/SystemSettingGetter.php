<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy\UnifiedSettingsAccess\Getters;

use Piwik\Exception\Exception;
use Piwik\Settings\Plugin\SystemSetting;

class SystemSettingGetter extends SettingGetter
{
    private $systemSetting = null;

    private function getSystemSetting()
    {
        if ($this->systemSetting === null) {
            $this->systemSetting = new SystemSetting($this->settingName, $this->defaultValue, $this->type, $this->pluginName);
        }

        return $this->systemSetting;
    }

    public function hasSetting(): bool
    {
        return $this->getSystemSetting()->hasValue();
    }

    public function getSetting()
    {
        try {
            $setting = $this->getSystemSetting();
            $this->myValue = $setting->getValue();

            $this->processValue();

            return $this->myValue;
        } catch (\Exception $e) {
            throw new Exception(sprintf("System setting '%s' not supported. Error: %s", $this->settingName, $e->getMessage()));
        }
    }
}
