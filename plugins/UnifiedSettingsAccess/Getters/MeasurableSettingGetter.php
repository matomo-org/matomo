<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UnifiedSettingsAccess\Getters;

use Piwik\Exception\Exception;
use Piwik\Settings\Measurable\MeasurableSetting;

class MeasurableSettingGetter extends SettingGetter
{
    private $measurableSetting = null;

    private function getMeasurableSetting()
    {
        if ($this->measurableSetting === null) {
            $this->measurableSetting = new MeasurableSetting(
                $this->settingName,
                $this->defaultValue,
                $this->type,
                $this->pluginName,
                $this->idSite
            );
        }

        return $this->measurableSetting;
    }

    public function hasSetting(): bool
    {
        return $this->getMeasurableSetting()->hasValue();
    }

    public function getSetting()
    {
        try {
            $setting = $this->getMeasurableSetting();
            $this->myValue = $setting->getValue();

            $this->processValue();

            return $this->myValue;
        } catch (\Exception $e) {
            throw new Exception(sprintf("Measurable setting '%s' not supported. Error: %s", $this->settingName, $e->getMessage()));
        }
    }
}
