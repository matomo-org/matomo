<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleSettingsPlugin;

use Piwik\Settings\SystemSetting;
use Piwik\Settings\UserSetting;

/**
 * Defines Settings for ExampleSettingsPlugin.
 *
 * Usage like this:
 * $settings = new Settings('ExampleSettingsPlugin');
 * $settings->autoRefresh->getValue();
 * $settings->metric->getValue();
 */
class Settings extends \Piwik\Plugin\Settings
{
    /** @var UserSetting */
    public $autoRefresh;

    /** @var UserSetting */
    public $refreshInterval;

    /** @var UserSetting */
    public $color;

    /** @var SystemSetting */
    public $metric;

    /** @var SystemSetting */
    public $browsers;

    /** @var SystemSetting */
    public $description;

    /** @var SystemSetting */
    public $password;

    protected function init()
    {
        $this->setIntroduction('Here you can specify the settings for this plugin.');

        // User setting --> checkbox converted to bool
        $this->createAutoRefreshSetting();

        // User setting --> textbox converted to int defining a validator and filter
        $this->createRefreshIntervalSetting();

        // User setting --> radio
        $this->createColorSetting();

        // System setting --> allows selection of a single value
        $this->createMetricSetting();

        // System setting --> allows selection of multiple values
        $this->createBrowsersSetting();

        // System setting --> textarea
        $this->createDescriptionSetting();

        // System setting --> textarea
        $this->createPasswordSetting();
    }

    private function createAutoRefreshSetting()
    {
        $this->autoRefresh        = new UserSetting('autoRefresh', 'Auto refresh');
        $this->autoRefresh->type  = static::TYPE_BOOL;
        $this->autoRefresh->uiControlType = static::CONTROL_CHECKBOX;
        $this->autoRefresh->description   = 'If enabled, the value will be automatically refreshed depending on the specified interval';
        $this->autoRefresh->defaultValue  = false;

        $this->addSetting($this->autoRefresh);
    }

    private function createRefreshIntervalSetting()
    {
        $this->refreshInterval        = new UserSetting('refreshInterval', 'Refresh Interval');
        $this->refreshInterval->type  = static::TYPE_INT;
        $this->refreshInterval->uiControlType = static::CONTROL_TEXT;
        $this->refreshInterval->uiControlAttributes = array('size' => 3);
        $this->refreshInterval->description     = 'Defines how often the value should be updated';
        $this->refreshInterval->inlineHelp      = 'Enter a number which is >= 15';
        $this->refreshInterval->defaultValue    = '30';
        $this->refreshInterval->validate = function ($value, $setting) {
            if ($value < 15) {
                throw new \Exception('Value is invalid');
            }
        };

        $this->addSetting($this->refreshInterval);
    }

    private function createColorSetting()
    {
        $this->color        = new UserSetting('color', 'Color');
        $this->color->uiControlType = static::CONTROL_RADIO;
        $this->color->description   = 'Pick your favourite color';
        $this->color->availableValues  = array('red' => 'Red', 'blue' => 'Blue', 'green' => 'Green');

        $this->addSetting($this->color);
    }

    private function createMetricSetting()
    {
        $this->metric        = new SystemSetting('metric', 'Metric to display');
        $this->metric->type  = static::TYPE_STRING;
        $this->metric->uiControlType = static::CONTROL_SINGLE_SELECT;
        $this->metric->availableValues  = array('nb_visits' => 'Visits', 'nb_actions' => 'Actions', 'visitors' => 'Visitors');
        $this->metric->introduction  = 'Only Super Users can change the following settings:';
        $this->metric->description   = 'Choose the metric that should be displayed in the browser tab';
        $this->metric->defaultValue  = 'nb_visits';
        $this->metric->readableByCurrentUser = true;

        $this->addSetting($this->metric);
    }

    private function createBrowsersSetting()
    {
        $this->browsers        = new SystemSetting('browsers', 'Supported Browsers');
        $this->browsers->type  = static::TYPE_ARRAY;
        $this->browsers->uiControlType = static::CONTROL_MULTI_SELECT;
        $this->browsers->availableValues  = array('firefox' => 'Firefox', 'chromium' => 'Chromium', 'safari' => 'safari');
        $this->browsers->description   = 'The value will be only displayed in the following browsers';
        $this->browsers->defaultValue  = array('firefox', 'chromium', 'safari');
        $this->browsers->readableByCurrentUser = true;

        $this->addSetting($this->browsers);
    }

    private function createDescriptionSetting()
    {
        $this->description = new SystemSetting('description', 'Description for value');
        $this->description->readableByCurrentUser = true;
        $this->description->uiControlType = static::CONTROL_TEXTAREA;
        $this->description->description   = 'This description will be displayed next to the value';
        $this->description->defaultValue  = "This is the value: \nAnother line";

        $this->addSetting($this->description);
    }

    private function createPasswordSetting()
    {
        $this->password = new SystemSetting('password', 'API password');
        $this->password->readableByCurrentUser = true;
        $this->password->uiControlType = static::CONTROL_PASSWORD;
        $this->password->description   = 'Password for the 3rd API where we fetch the value';
        $this->password->transform     = function ($value) {
            return sha1($value . 'salt');
        };

        $this->addSetting($this->password);
    }
}
