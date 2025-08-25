<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy\UnifiedSettingsAccess\Getters;

use Piwik\Exception\Exception;
use Piwik\Option;
use Piwik\Policy\SettingValues\GenericSettingValue;

class OptionSettingGetter extends SettingGetter
{
    public function hasSetting(): bool
    {
        return Option::get($this->settingName) === false;
    }

    public function getSetting(): GenericSettingValue
    {
        try {
            // problem
            $this->myValue = Option::get($this->settingName);

            $this->processValue(false);

            return new GenericSettingValue($this->idSite, $this->myValue, '');
        } catch (\Exception $e) {
            throw new Exception(sprintf("Option setting '%s' not supported. Error: %s", $this->settingName, $e->getMessage()));
        }
    }
}
