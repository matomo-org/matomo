<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ExampleSettingsPlugin
 */
namespace Piwik\Plugins\ExampleSettingsPlugin;

use Piwik\Piwik;
use Piwik\Plugin\Settings as PluginSettings;
use Piwik\Settings\SystemSetting;
use Piwik\Settings\UserSetting;

/**
 * Settings
 *
 * @package ExampleSettingsPlugin
 */
class Settings extends PluginSettings
{
    protected function init()
    {
        $this->setIntroduction('Here you can specify the settings for this plugin.');

        // User setting --> checkbox converted to bool
        $this->addSetting($this->getAutoRefreshSetting());

        // User setting --> textbox converted to int defining a validator and filter
        $this->addSetting($this->getRefreshIntervalSetting());

        // System setting --> allows selection of a single value
        $this->addSetting($this->getMetricSetting());

        // System setting --> allows selection of multiple values
        $this->addSetting($this->getBrowsersSetting());

        // System setting --> textarea
         $this->addSetting($this->getDescriptionSetting());

        // System setting --> textarea
         $this->addSetting($this->getPasswordSetting());
    }

    public function isAutoRefreshEnabled()
    {
        return $this->getSettingValue($this->getAutoRefreshSetting());
    }

    public function getRefreshInterval()
    {
        return $this->getSettingValue($this->getRefreshIntervalSetting());
    }

    public function getMetric()
    {
        return $this->getSettingValue($this->getMetricSetting());
    }

    private function getAutoRefreshSetting()
    {
        $autoRefresh        = new UserSetting('autoRefresh', 'Auto refresh');
        $autoRefresh->type  = static::TYPE_BOOL;
        $autoRefresh->field = static::FIELD_CHECKBOX;
        $autoRefresh->description  = 'If enabled, the value will be automatically refreshed depending on the specified interval';
        $autoRefresh->defaultValue = false;

        return $autoRefresh;
    }

    private function getRefreshIntervalSetting()
    {
        $refreshInterval        = new UserSetting('refreshInterval', 'Refresh Interval');
        $refreshInterval->type  = static::TYPE_INT;
        $refreshInterval->field = static::FIELD_TEXT;
        $refreshInterval->fieldAttributes = array('size' => 3);
        $refreshInterval->description     = 'Defines how often the value should be updated';
        $refreshInterval->inlineHelp      = 'Enter a number which is >= 15';
        $refreshInterval->defaultValue    = '30';
        $refreshInterval->validate = function ($value, $setting) {
            if ($value < 15) {
                throw new \Exception('Value is invalid');
            }
        };
        $refreshInterval->filter = function ($value, $setting) {
            if ($value > 30) {
                $value = 30;
            }

            return $value;
        };

        return $refreshInterval;
    }

    private function getMetricSetting()
    {
        $metric        = new SystemSetting('metric', 'Metric to display');
        $metric->type  = static::TYPE_STRING;
        $metric->field = static::FIELD_SINGLE_SELECT;
        $metric->fieldOptions = array('nb_visits' => 'Visits', 'nb_actions' => 'Actions', 'visitors' => 'Visitors');
        $metric->introduction = 'Only super users can change the following settings:';
        $metric->description  = 'Choose the metric that should be displayed in the browser tab';
        $metric->defaultValue = 'nb_visits';

        return $metric;
    }

    private function getBrowsersSetting()
    {
        $browsers        = new SystemSetting('browsers', 'Supported Browsers');
        $browsers->type  = static::TYPE_ARRAY;
        $browsers->field = static::FIELD_MULTI_SELECT;
        $browsers->fieldOptions = array('firefox' => 'Firefox', 'chromium' => 'Chromium', 'safari' => 'safari');
        $browsers->description  = 'The value will be only displayed in the following browsers';
        $browsers->defaultValue = array('firefox', 'chromium', 'safari');

        return $browsers;
    }

    private function getDescriptionSetting()
    {
        $description        = new SystemSetting('description', 'Description for value');
        $description->field = static::FIELD_TEXTAREA;
        $description->description  = 'This description will be displayed next to the value';
        $description->defaultValue = "This is the value: \nAnother line";

        return $description;
    }

    private function getPasswordSetting()
    {
        $description        = new SystemSetting('password', 'API password');
        $description->field = static::FIELD_PASSWORD;
        $description->description = 'Password for the 3rd API where we fetch the value';
        $description->filter = function ($value) {
            return sha1($value . 'salt');
        };

        return $description;
    }
}
