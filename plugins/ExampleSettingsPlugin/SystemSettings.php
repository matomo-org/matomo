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
use Piwik\Validators\NotEmpty;

/**
 * Defines Settings for ExampleSettingsPlugin.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $metric;

    /** @var Setting */
    public $browsers;

    /** @var Setting */
    public $description;

    /** @var Setting */
    public $password;

    protected function init()
    {
        // System setting --> allows selection of a single value
        $this->metric = $this->createMetricSetting();

        // System setting --> allows selection of multiple values
        $this->browsers = $this->createBrowsersSetting();

        // System setting --> textarea
        $this->description = $this->createDescriptionSetting();

        // System setting --> textarea
        $this->password = $this->createPasswordSetting();
    }

    private function createMetricSetting()
    {
        return $this->makeSetting('metric', $default = 'nb_visits', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Metric to display';
            $field->uiControl = FieldConfig::UI_CONTROL_SINGLE_SELECT;
            $field->availableValues = array('nb_visits' => 'Visits', 'nb_actions' => 'Actions', 'visitors' => 'Visitors');
            $field->description = 'Choose the metric that should be displayed in the browser tab';
            $field->validators[] = new NotEmpty();
        });
    }

    private function createBrowsersSetting()
    {
        $default = array('firefox', 'chromium', 'safari');

        return $this->makeSetting('browsers', $default, FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->title = 'Supported Browsers';
            $field->uiControl = FieldConfig::UI_CONTROL_MULTI_SELECT;
            $field->availableValues = array('firefox' => 'Firefox', 'chromium' => 'Chromium', 'safari' => 'safari');
            $field->description = 'The value will be only displayed in the following browsers';
        });
    }

    private function createDescriptionSetting()
    {
        $default = "This is the value: \nAnother line";

        return $this->makeSetting('description', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Description for value';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXTAREA;
            $field->description = 'This description will be displayed next to the value';
            $field->validators[] = new NotEmpty();
        });
    }

    private function createPasswordSetting()
    {
        return $this->makeSetting('password', $default = null, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'API password';
            $field->uiControl = FieldConfig::UI_CONTROL_PASSWORD;
            $field->description = 'Password for the 3rd API where we fetch the value';
            $field->transform = function ($value) {
                return password_hash($value, PASSWORD_DEFAULT);
            };
        });
    }
}
