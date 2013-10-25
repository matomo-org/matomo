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

use Piwik\Option;
use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\StorageInterface;

/**
 * Settings class that plugins can extend in order to create settings for their plugins.
 *
 * @package Piwik\Plugin
 * @api
 */
abstract class Settings implements StorageInterface
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
    const FIELD_MULTI_SELECT  = 'multiselect';
    const FIELD_SINGLE_SELECT = 'select';

    /**
     * An array containing all available settings: Array ( [setting-name] => [setting] )
     *
     * @var Settings[]
     */
    private $settings = array();

    /**
     * Array containing all plugin settings values: Array( [setting-key] => [setting-value] ).
     *
     * @var array
     */
    private $settingsValues = array();

    private $introduction;
    private $pluginName;

    public function __construct($pluginName)
    {
        $this->pluginName = $pluginName;

        $this->init();
        $this->loadSettings();
    }

    /**
     * Define your settings and introduction here.
     */
    abstract protected function init();

    /**
     * Sets (overwrites) the plugin settings introduction.
     *
     * @param string $introduction
     */
    protected function setIntroduction($introduction)
    {
        $this->introduction = $introduction;
    }

    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * Returns only settings that can be displayed for current user. For instance a regular user won't see get
     * any settings that require super user permissions.
     *
     * @return Setting[]
     */
    public function getSettingsForCurrentUser()
    {
        $settings = array_filter($this->getSettings(), function (Setting $setting) {
            return $setting->canBeDisplayedForCurrentUser();
        });

        uasort($settings, function ($setting1, $setting2) use ($settings) {
            /** @var Setting $setting1 */ /** @var Setting $setting2 */
            if ($setting1->getOrder() == $setting2->getOrder()) {
                // preserve order for settings having same order
                foreach ($settings as $setting) {
                    if ($setting1 === $setting) {
                        return -1;
                    }
                    if ($setting2 === $setting) {
                        return 1;
                    }
                }

                return 0;
            }

            return $setting1->getOrder() > $setting2->getOrder() ? -1 : 1;
        });

        return $settings;
    }

    /**
     * Get all available settings without checking any permissions.
     *
     * @return Setting[]
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save()
    {
        Option::set($this->getOptionKey(), serialize($this->settingsValues));
    }

    /**
     * Removes all settings for this plugin. Useful for instance while uninstalling the plugin.
     */
    public function removeAllPluginSettings()
    {
        Piwik::checkUserIsSuperUser();

        Option::delete($this->getOptionKey());
        $this->settingsValues = array();
    }

    /**
     * Gets the current value for this setting. If no value is specified, the default value will be returned.
     *
     * @param Setting $setting
     *
     * @return mixed
     *
     * @throws \Exception In case the setting does not exist or if the current user is not allowed to change the value
     *                    of this setting.
     */
    public function getSettingValue(Setting $setting)
    {
        $this->checkIsValidSetting($setting->getName());

        if (array_key_exists($setting->getKey(), $this->settingsValues)) {

            return $this->settingsValues[$setting->getKey()];
        }

        return $setting->defaultValue;
    }

    /**
     * Sets (overwrites) the value for the given setting. Make sure to call `save()` afterwards, otherwise the change
     * has no effect. Before the value is saved a possibly define `validate` closure and `filter` closure will be
     * called. Alternatively the value will be casted to the specfied setting type.
     *
     * @param Setting $setting
     * @param string $value
     *
     * @throws \Exception In case the setting does not exist or if the current user is not allowed to change the value
     *                    of this setting.
     */
    public function setSettingValue(Setting $setting, $value)
    {
        $this->checkIsValidSetting($setting->getName());

        if ($setting->validate && $setting->validate instanceof \Closure) {
            call_user_func($setting->validate, $value, $setting);
        }

        if ($setting->filter && $setting->filter instanceof \Closure) {
            $value = call_user_func($setting->filter, $value, $setting);
        } elseif (isset($setting->type)) {
            settype($value, $setting->type);
        }

        $this->settingsValues[$setting->getKey()] = $value;
    }

    /**
     * Removes the value for the given setting. Make sure to call `save()` afterwards, otherwise the removal has no
     * effect.
     *
     * @param Setting $setting
     */
    public function removeSettingValue(Setting $setting)
    {
        $this->checkHasEnoughPermission($setting);

        $key = $setting->getKey();

        if (array_key_exists($key, $this->settingsValues)) {
            unset($this->settingsValues[$key]);
        }
    }

    /**
     * Adds a new setting.
     *
     * @param Setting $setting
     * @throws \Exception       In case a setting having the same name already exists.
     *                          In case the name contains non-alnum characters.
     */
    protected function addSetting(Setting $setting)
    {
        if (!ctype_alnum($setting->getName())) {
            $msg = sprintf('The setting name "%s" in plugin "%s" is not valid. Only alpha and numerical characters are allowed', $setting->getName(), $this->pluginName);
            throw new \Exception($msg);
        }

        if (array_key_exists($setting->getName(), $this->settings)) {
            throw new \Exception(sprintf('A setting with name "%s" does already exist for plugin "%s"', $setting->getName(), $this->pluginName));
        }

        $this->setDefaultTypeAndFieldIfNeeded($setting);
        $this->addValidatorIfNeeded($setting);

        $setting->setStorage($this);

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
            throw new \Exception(sprintf('The setting %s does not exist', $name));
        }

        $this->checkHasEnoughPermission($setting);
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

    private function getDefaultType($field)
    {
        $defaultTypes = array(
            static::FIELD_TEXT          => static::TYPE_STRING,
            static::FIELD_TEXTAREA      => static::TYPE_STRING,
            static::FIELD_PASSWORD      => static::TYPE_STRING,
            static::FIELD_CHECKBOX      => static::TYPE_BOOL,
            static::FIELD_MULTI_SELECT  => static::TYPE_ARRAY,
            static::FIELD_SINGLE_SELECT => static::TYPE_STRING,
        );

        return $defaultTypes[$field];
    }

    private function getDefaultField($type)
    {
        $defaultFields = array(
            static::TYPE_INT    => static::FIELD_TEXT,
            static::TYPE_FLOAT  => static::FIELD_TEXT,
            static::TYPE_STRING => static::FIELD_TEXT,
            static::TYPE_BOOL   => static::FIELD_CHECKBOX,
            static::TYPE_ARRAY  => static::FIELD_MULTI_SELECT,
        );

        return $defaultFields[$type];
    }

    /**
     * @param $setting
     * @throws \Exception
     */
    private function checkHasEnoughPermission(Setting $setting)
    {
        if (!$setting->canBeDisplayedForCurrentUser()) {
            $errorMsg = Piwik::translate('CoreAdminHome_PluginSettingChangeNotAllowed', array($setting->getName(), $this->pluginName));
            throw new \Exception($errorMsg);
        }
    }

    private function setDefaultTypeAndFieldIfNeeded(Setting $setting)
    {
        if (!is_null($setting->field) && is_null($setting->type)) {
            $setting->type = $this->getDefaultType($setting->field);
        } elseif (!is_null($setting->type) && is_null($setting->field)) {
            $setting->field = $this->getDefaultField($setting->type);
        } elseif (is_null($setting->field) && is_null($setting->type)) {
            $setting->type = static::TYPE_STRING;
            $setting->field = static::FIELD_TEXT;
        }
    }

    private function addValidatorIfNeeded(Setting $setting)
    {
        if (!is_null($setting->validate) || is_null($setting->fieldOptions)) {
            return;
        }

        $pluginName = $this->pluginName;

        $setting->validate = function ($value) use ($setting, $pluginName) {

            $errorMsg = Piwik::translate('CoreAdminHome_PluginSettingsValueNotAllowed',
                                         array($setting->title, $pluginName));

            if (is_array($value) && $setting->type == Settings::TYPE_ARRAY) {
                foreach ($value as $val) {
                    if (!array_key_exists($val, $setting->fieldOptions)) {
                        throw new \Exception($errorMsg);
                    }
                }
            } else {
                if (!array_key_exists($value, $setting->fieldOptions)) {
                    throw new \Exception($errorMsg);
                }
            }
        };
    }
}
