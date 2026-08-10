<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleSettingsPlugin;

use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\UserSetting;
use Piwik\Settings\Setting;

/**
 * Defines Settings for ExampleSettingsPlugin.
 *
 * Usage like this:
 * $settings = new UserSettings();
 * $settings->autoRefresh->getValue();
 * $settings->color->getValue();
 */
class UserSettings extends \Piwik\Settings\Plugin\UserSettings
{
    public UserSetting $autoRefresh;

    public UserSetting $refreshInterval;

    public UserSetting $color;

    /**
     * @return void
     */
    protected function init()
    {
        // User setting --> checkbox converted to bool
        $this->autoRefresh = $this->createAutoRefreshSetting();

        // User setting --> textbox converted to int defining a validator and filter
        $this->refreshInterval = $this->createRefreshIntervalSetting();

        // User setting --> radio
        $this->color = $this->createColorSetting();
    }

    private function createAutoRefreshSetting(): UserSetting
    {
        return $this->makeSetting('autoRefresh', false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = 'Auto refresh';
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = 'If enabled, the value will be automatically refreshed depending on the specified interval';
        });
    }

    private function createRefreshIntervalSetting(): UserSetting
    {
        return $this->makeSetting('refreshInterval', '30', FieldConfig::TYPE_INT, function (FieldConfig $field) {
            $field->title = 'Refresh Interval';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->uiControlAttributes = ['size' => 3];
            $field->description = 'Defines how often the value should be updated';
            $field->inlineHelp  = 'Enter a number which is >= 15';
            // the validate callback receives the raw, not yet type casted request value
            $field->validate = function ($value, Setting $setting) {
                if (!is_numeric($value) || (int) $value < 15) {
                    throw new \Exception('Value is invalid');
                }
            };
        });
    }

    private function createColorSetting(): UserSetting
    {
        return $this->makeSetting('color', 'red', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Color';
            $field->uiControl = FieldConfig::UI_CONTROL_RADIO;
            $field->description = 'Pick your favourite color';
            $field->availableValues = ['red' => 'Red', 'blue' => 'Blue', 'green' => 'Green'];
        });
    }
}
