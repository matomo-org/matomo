<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage;

use Piwik\Settings\Storage\Backend;

/**
 * A storage stores values for multiple settings. Storing multiple settings here saves having to do
 * a "get" for each individual setting. A storage is usually stared between all individual setting instances
 * within a plugin.
 */
class Storage
{
    /**
     * Array containing all plugin settings values: Array( [setting-key] => [setting-value] ).
     *
     * @var array
     */
    protected $settingsValues = array();

    // for lazy loading of setting values
    private $settingValuesLoaded = false;

    /**
     * @var Backend\BackendInterface
     */
    private $backend;

    /**
     * Defines whether a value has changed since the settings were loaded or not.
     * @var bool
     */
    private $isDirty = false;

    public function __construct(Backend\BackendInterface $backend)
    {
        $this->backend = $backend;
    }

    /**
     * Get the currently used backend for this storage.
     * @return Backend\BackendInterface
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Saves (persists) the current setting values in the database if a value has actually changed.
     */
    public function save()
    {
        if ($this->isDirty) {
            $this->backend->save($this->settingsValues);

            $this->isDirty = false;

            Backend\Cache::clearCache();
        }
    }

    /**
     * Returns the current value for a setting. If no value is stored, the default value
     * is be returned.
     *
     * @param string $key  The name / key of a setting
     * @param mixed $defaultValue Default value that will be used in case no value for this setting exists yet
     * @param string $type The PHP internal type the value of the setting should have, see FieldConfig::TYPE_*
     *                     constants. Only an actual value of the setting will be converted to the given type, the
     *                     default value will not be converted.
     * @return mixed
     */
    public function getValue($key, $defaultValue, $type)
    {
        $this->loadSettingsIfNotDoneYet();

        if (array_key_exists($key, $this->settingsValues)) {
            settype($this->settingsValues[$key], $type);
            return $this->settingsValues[$key];
        }

        return $defaultValue;
    }

    /**
     * Sets (overwrites) the value of a setting in memory. To persist the change across requests, {@link save()} must be
     * called.
     *
     * @param string $key  The name / key of a setting
     * @param mixed $value The value that shall be set for the given setting.
     */
    public function setValue($key, $value)
    {
        $this->loadSettingsIfNotDoneYet();

        $this->isDirty = true;
        $this->settingsValues[$key] = $value;
    }

    private function loadSettingsIfNotDoneYet()
    {
        if ($this->settingValuesLoaded) {
            return;
        }

        $this->settingValuesLoaded = true;
        $this->settingsValues = $this->backend->load();
    }
}
