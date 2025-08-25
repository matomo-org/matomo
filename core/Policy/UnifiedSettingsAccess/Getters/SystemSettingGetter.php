<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy\UnifiedSettingsAccess\Getters;

use Piwik\Exception\Exception;
use Piwik\Policy\SettingValues\GenericSettingValue;
use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Policy\UnifiedSettingsAccess\Getters\CustomSystemSettings;

class SystemSettingGetter extends SettingGetter
{
    /** @var SystemSetting */
    private $systemSetting = null;

    private function getSystemSetting()
    {
        if ($this->systemSetting === null) {
            $systemSettings = new CustomSystemSettings();
            $setting = new SystemSetting($this->settingName, $this->defaultValue, $this->type, $this->pluginName);
            $systemSettings->addSetting($setting);
            $this->systemSetting = $systemSettings->getSetting($this->settingName);
        }

        return $this->systemSetting;
    }

    public function hasSetting(): bool
    {
        return $this->getSystemSetting()->hasValue();
    }

    public function getSetting(): GenericSettingValue
    {
        try {
            $setting = $this->getSystemSetting();
            $this->myValue = $setting->getValue();

            $this->processValue();

            return new GenericSettingValue($this->idSite, $this->myValue, '');
        } catch (\Exception $e) {
            throw new Exception(sprintf("System setting '%s' not supported. Error: %s", $this->settingName, $e->getMessage()));
        }
    }
}
