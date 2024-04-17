<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleSettingsPlugin;

use Piwik\Plugins\MobileAppMeasurable\Type as MobileAppType;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings for ExampleSettingsPlugin.
 *
 * Usage like this:
 * // require Piwik\Plugin\SettingsProvider via Dependency Injection eg in constructor of your class
 * $settings = $settingsProvider->getMeasurableSettings('ExampleSettingsPlugin', $idSite);
 * $settings->appId->getValue();
 * $settings->contactEmails->getValue();
 */
class MeasurableSettings extends \Piwik\Settings\Measurable\MeasurableSettings
{
    /** @var Setting|null */
    public $appId;

    /** @var Setting */
    public $contactEmails;

    protected function init()
    {
        if ($this->hasMeasurableType(MobileAppType::ID)) {
            // this setting will be only shown for mobile apps
            $this->appId = $this->makeAppIdSetting();
        }

        $this->contactEmails = $this->makeContactEmailsSetting();
    }

    private function makeAppIdSetting()
    {
        $defaultValue = '';
        $type = FieldConfig::TYPE_STRING;

        return $this->makeSetting('mobile_app_id', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = 'App ID';
            $field->inlineHelp = 'Enter the id of the mobile app eg "org.domain.example"';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
        });
    }

    private function makeContactEmailsSetting()
    {
        $defaultValue = array();
        $type = FieldConfig::TYPE_ARRAY;

        return $this->makeSetting('contact_email', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = 'Contact email addresses';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXTAREA;
        });
    }
}
