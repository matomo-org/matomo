<?php
/**
 * Created by PhpStorm.
 * User: thomassteur
 * Date: 25.10.13
 * Time: 13:33
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
    public function getSettingValue(Setting $setting);

    /**
     * Removes the value for the given setting. Make sure to call `save()` afterwards, otherwise the removal has no
     * effect.
     *
     * @param Setting $setting
     */
    public function removeSettingValue(Setting $setting);

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
    public function setSettingValue(Setting $setting, $value);

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save();
}
