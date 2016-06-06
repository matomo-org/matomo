<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Settings;

/**
 * Base type of all Setting storage implementations.
 */
interface StorageInterface
{
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
    public function getValue(Setting $setting);

    /**
     * Removes the value for the given setting. Make sure to call `save()` afterwards, otherwise the removal has no
     * effect.
     *
     * @param Setting $setting
     */
    public function deleteValue(Setting $setting);

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
    public function setValue(Setting $setting, $value);

    /**
     * Removes all settings for this plugin from the database. Useful when uninstalling
     * a plugin.
     */
    public function deleteAllValues();

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save();
}
