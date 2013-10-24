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

        // System setting --> allows selection of a value
        $this->addSetting($this->getMetricSetting());
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
        $metric->introduction = 'Only super users can change this setting.';
        $metric->description  = Piwik::translate('LiveTab_MetricDescription');
        $metric->defaultValue = 'nb_visits';

        return $metric;
    }
}
