<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UnifiedSettingsAccess\Getters;

use Piwik\Exception\Exception;
use Piwik\Option;

class OptionSettingGetter extends SettingGetter
{
    public function hasSetting(): bool
    {
        return Option::get($this->settingName) === false;
    }

    public function getSetting()
    {
        try {
            $this->myValue = Option::get($this->settingName);

            $this->processValue(false);

            return $this->myValue;
        } catch (\Exception $e) {
            throw new Exception(sprintf("Option setting '%s' not supported. Error: %s", $this->settingName, $e->getMessage()));
        }
    }
}
