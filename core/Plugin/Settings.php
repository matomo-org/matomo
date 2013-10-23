<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Plugin;

use Piwik\Common;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\SystemSetting;
use Piwik\Settings\UserSetting;

class Settings
{
    const TYPE_INT    = 'integer';
    const TYPE_FLOAT  = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOL   = 'boolean';
    const TYPE_ARRAY  = 'array';

    const FIELD_TEXT     = 'text';
    const FIELD_TEXTAREA = 'textarea';
    const FIELD_CHECKBOX = 'checkbox';
    const FIELD_PASSWORD = 'password';
    const FIELD_MULTI_SELECT   = 'multiselect';
    const FIELD_SINGLE_SELECT  = 'select';

    /**
     * @var Settings[]
     */
    private $settings       = array();
    private $settingsValues = array();

    private $introduction;
    private $pluginName;

    public function __construct($pluginName)
    {
        $this->pluginName = $pluginName;

        $this->init();
        $this->loadSettings();
    }

    protected function init()
    {
    }

    protected function addIntroduction($introduction)
    {
        $this->introduction = $introduction;
    }

    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * @return Setting[]
     */
    public function getSettingsForCurrentUser()
    {
        return array_values(array_filter($this->getSettings(), function (Setting $setting) {
            return $setting->canBeDisplayedForCurrentUser();
        }));
    }

    /**
     * @return Setting[]
     */
    public function getSettings()
    {
        return $this->settings;
    }

    public function save()
    {
        Option::set($this->getOptionKey(), serialize($this->settingsValues));
    }

    public function removeAllPluginSettings()
    {
        Option::delete($this->getOptionKey());
    }

    public function removeValue(Setting $setting)
    {
        $key = $setting->getKey();

        if (array_key_exists($key, $this->settingsValues)) {
            unset($this->settingsValues[$key]);
        }
    }

    public function getSettingValue(Setting $setting)
    {
        $this->checkIsValidSetting($setting->getName());

        if (array_key_exists($setting->getKey(), $this->settingsValues)) {

            return $this->settingsValues[$setting->getKey()];
        }

        return $setting->defaultValue;
    }

    public function setSettingValue(Setting $setting, $value)
    {
        $this->checkIsValidSetting($setting->getName());

        if ($setting->validate && $setting->validate instanceof \Closure) {
            call_user_func($setting->validate, $value, $setting);
        }

        if ($setting->filter && $setting->filter instanceof \Closure) {
            $value = call_user_func($setting->filter, $value, $setting);
        } else {
            settype($value, $setting->type);
        }

        $this->settingsValues[$setting->getKey()] = $value;
    }

    protected function addSetting(Setting $setting)
    {
        if (array_key_exists($setting->getName(), $this->settings)) {
            throw new \Exception(sprintf('A setting with name %s does already exist', $setting->getName()));
        }

        if (!is_null($setting->field) && is_null($setting->type)) {
            $setting->type = $setting->getDefaultType($setting->field);
        } elseif (!is_null($setting->type) && is_null($setting->field)) {
            $setting->field = $setting->getDefaultField($setting->type);
        }

        if (is_null($setting->validate) && !is_null($setting->fieldOptions)) {
            $pluginName = $this->pluginName;
            $setting->validate = function ($value) use ($setting, $pluginName) {
                if (!array_key_exists($value, $setting->fieldOptions)) {
                    throw new \Exception(sprintf('The selected value for field "%s" and plugin "%s" is not allowed.', $setting->getTitle(), $pluginName));
                }
            };
        }

        $this->settings[$setting->getName()] = $setting;
    }

    private function getOptionKey()
    {
        return 'Plugin_' . $this->pluginName . '_Settings';
    }

    private function loadSettings()
    {
        $values = Option::get($this->getOptionKey());

        if (!empty($values)) {
            $this->settingsValues = unserialize($values);
        }
    }

    private function checkIsValidSetting($name)
    {
        $setting = $this->getSetting($name);

        if (empty($setting)) {
            // TODO escape $name? or is it automatically escaped?
            throw new \Exception(sprintf('The setting %s does not exist', $name));
        }

        if (!$setting->canBeDisplayedForCurrentUser()) {
            throw new \Exception(sprintf('You are not allowed to change the value of the setting %s', $name));
        }
    }

    /**
     * @param  $name
     * @return Setting|null
     */
    private function getSetting($name)
    {
        if (array_key_exists($name, $this->settings)) {
            return $this->settings[$name];
        }
    }

}
