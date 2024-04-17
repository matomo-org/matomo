<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleSettingsPlugin;

use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

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
    /** @var Setting */
    public $autoRefresh;

    /** @var Setting */
    public $refreshInterval;

    /** @var Setting */
    public $color;

    protected function init()
    {
        // User setting --> checkbox converted to bool
        $this->autoRefresh = $this->createAutoRefreshSetting();

        // User setting --> textbox converted to int defining a validator and filter
        $this->refreshInterval = $this->createRefreshIntervalSetting();

        // User setting --> radio
        $this->color = $this->createColorSetting();
    }

    private function createAutoRefreshSetting()
    {
        return $this->makeSetting('autoRefresh', $default = false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = 'Auto refresh';
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = 'If enabled, the value will be automatically refreshed depending on the specified interval';
        });
    }

    private function createRefreshIntervalSetting()
    {
        return $this->makeSetting('refreshInterval', $default = '30', FieldConfig::TYPE_INT, function (FieldConfig $field) {
            $field->title = 'Refresh Interval';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->uiControlAttributes = array('size' => 3);
            $field->description = 'Defines how often the value should be updated';
            $field->inlineHelp  = 'Enter a number which is >= 15';
            $field->validate = function ($value, $setting) {
                if ($value < 15) {
                    throw new \Exception('Value is invalid');
                }
            };
        });
    }

    private function createColorSetting()
    {
        return $this->makeSetting('color', $default = 'red', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Color';
            $field->uiControl = FieldConfig::UI_CONTROL_RADIO;
            $field->description = 'Pick your favourite color';
            $field->availableValues = array('red' => 'Red', 'blue' => 'Blue', 'green' => 'Green');
        });
    }
}
