<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\Storage;
use Piwik\Settings\StorageInterface;
use Piwik\Tracker\SettingsStorage;

/**
 * Base class of all plugin settings providers. Plugins that define their own configuration settings
 * can extend this class to easily make their settings available to Piwik users.
 *
 * Descendants of this class should implement the {@link init()} method and call the
 * {@link addSetting()} method for each of the plugin's settings.
 *
 * For an example, see the {@link Piwik\Plugins\ExampleSettingsPlugin\ExampleSettingsPlugin} plugin.
 *
 * @api
 */
abstract class Settings
{
    const TYPE_INT    = 'integer';
    const TYPE_FLOAT  = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOL   = 'boolean';
    const TYPE_ARRAY  = 'array';

    const CONTROL_RADIO    = 'radio';
    const CONTROL_TEXT     = 'text';
    const CONTROL_TEXTAREA = 'textarea';
    const CONTROL_CHECKBOX = 'checkbox';
    const CONTROL_PASSWORD = 'password';
    const CONTROL_MULTI_SELECT  = 'multiselect';
    const CONTROL_SINGLE_SELECT = 'select';

    /**
     * An array containing all available settings: Array ( [setting-name] => [setting] )
     *
     * @var Settings[]
     */
    private $settings = array();

    private $introduction;
    protected $pluginName;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Constructor.
     */
    public function __construct($pluginName = null)
    {
        if (!empty($pluginName)) {
            $this->pluginName = $pluginName;
        } else {
            $classname = get_class($this);
            $parts     = explode('\\', $classname);

            if (3 <= count($parts)) {
                $this->pluginName = $parts[2];
            }
        }

        $this->storage = Storage\Factory::make($this->pluginName);

        $this->init();
    }

    /**
     * @ignore
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * @ignore
     * @return Setting
     */
    public function getSetting($name)
    {
        if (array_key_exists($name, $this->settings)) {
            return $this->settings[$name];
        }
    }

    /**
     * Implemented by descendants. This method should define plugin settings (via the
     * {@link addSetting()}) method and set the introduction text (via the
     * {@link setIntroduction()}).
     */
    abstract protected function init();

    /**
     * Sets the text used to introduce this plugin's settings in the _Plugin Settings_ page.
     *
     * @param string $introduction
     */
    protected function setIntroduction($introduction)
    {
        $this->introduction = $introduction;
    }

    /**
     * Returns the introduction text for this plugin's settings.
     *
     * @return string
     */
    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * Returns the settings that can be displayed for the current user.
     *
     * @return Setting[]
     */
    public function getSettingsForCurrentUser()
    {
        $settings = array_filter($this->getSettings(), function (Setting $setting) {
            return $setting->isWritableByCurrentUser();
        });

        $settings2 = $settings;

        uasort($settings, function ($setting1, $setting2) use ($settings2) {

            /** @var Setting $setting1 */ /** @var Setting $setting2 */
            if ($setting1->getOrder() == $setting2->getOrder()) {
                // preserve order for settings having same order
                foreach ($settings2 as $setting) {
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
     * Returns all available settings. This will include settings that are not available
     * to the current user (such as settings available only to the Super User).
     *
     * @return Setting[]
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Makes a new plugin setting available.
     *
     * @param Setting $setting
     * @throws \Exception       If there is a setting with the same name that already exists.
     *                          If the name contains non-alphanumeric characters.
     */
    protected function addSetting(Setting $setting)
    {
        $name = $setting->getName();

        if (!ctype_alnum(str_replace('_', '', $name))) {
            $msg = sprintf('The setting name "%s" in plugin "%s" is not valid. Only underscores, alpha and numerical characters are allowed', $setting->getName(), $this->pluginName);
            throw new \Exception($msg);
        }

        if (array_key_exists($name, $this->settings)) {
            throw new \Exception(sprintf('A setting with name "%s" does already exist for plugin "%s"', $setting->getName(), $this->pluginName));
        }

        $this->setDefaultTypeAndFieldIfNeeded($setting);
        $this->addValidatorIfNeeded($setting);

        $setting->setStorage($this->storage);
        $setting->setPluginName($this->pluginName);

        $this->settings[$name] = $setting;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save()
    {
        $this->storage->save();

        SettingsStorage::clearCache();

        /**
         * Triggered after a plugin settings have been updated.
         *
         * **Example**
         *
         *     Piwik::addAction('Settings.MyPlugin.settingsUpdated', function (Settings $settings) {
         *         $value = $settings->someSetting->getValue();
         *         // Do something with the new setting value
         *     });
         *
         * @param Settings $settings The plugin settings object.
         */
        Piwik::postEvent(sprintf('Settings.%s.settingsUpdated', $this->pluginName), array($this));
    }

    /**
     * Removes all settings for this plugin from the database. Useful when uninstalling
     * a plugin.
     */
    public function removeAllPluginSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->storage->deleteAllValues();

        SettingsStorage::clearCache();
    }

    private function getDefaultType($controlType)
    {
        $defaultTypes = array(
            static::CONTROL_TEXT          => static::TYPE_STRING,
            static::CONTROL_TEXTAREA      => static::TYPE_STRING,
            static::CONTROL_PASSWORD      => static::TYPE_STRING,
            static::CONTROL_CHECKBOX      => static::TYPE_BOOL,
            static::CONTROL_MULTI_SELECT  => static::TYPE_ARRAY,
            static::CONTROL_RADIO         => static::TYPE_STRING,
            static::CONTROL_SINGLE_SELECT => static::TYPE_STRING,
        );

        return $defaultTypes[$controlType];
    }

    private function getDefaultCONTROL($type)
    {
        $defaultControlTypes = array(
            static::TYPE_INT    => static::CONTROL_TEXT,
            static::TYPE_FLOAT  => static::CONTROL_TEXT,
            static::TYPE_STRING => static::CONTROL_TEXT,
            static::TYPE_BOOL   => static::CONTROL_CHECKBOX,
            static::TYPE_ARRAY  => static::CONTROL_MULTI_SELECT,
        );

        return $defaultControlTypes[$type];
    }

    private function setDefaultTypeAndFieldIfNeeded(Setting $setting)
    {
        $hasControl = !is_null($setting->uiControlType);
        $hasType    = !is_null($setting->type);

        if ($hasControl && !$hasType) {
            $setting->type = $this->getDefaultType($setting->uiControlType);
        } elseif ($hasType && !$hasControl) {
            $setting->uiControlType = $this->getDefaultCONTROL($setting->type);
        } elseif (!$hasControl && !$hasType) {
            $setting->type = static::TYPE_STRING;
            $setting->uiControlType = static::CONTROL_TEXT;
        }
    }

    private function addValidatorIfNeeded(Setting $setting)
    {
        if (!is_null($setting->validate) || is_null($setting->availableValues)) {
            return;
        }

        $pluginName = $this->pluginName;

        $setting->validate = function ($value) use ($setting, $pluginName) {

            $errorMsg = Piwik::translate('CoreAdminHome_PluginSettingsValueNotAllowed',
                array($setting->title, $pluginName));

            if (is_array($value) && $setting->type == Settings::TYPE_ARRAY) {
                foreach ($value as $val) {
                    if (!array_key_exists($val, $setting->availableValues)) {
                        throw new \Exception($errorMsg);
                    }
                }
            } else {
                if (!array_key_exists($value, $setting->availableValues)) {
                    throw new \Exception($errorMsg);
                }
            }
        };
    }
}
