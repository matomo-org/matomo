<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Settings\Storage\Backend;

/**
 * Interface for a storage backend. Any new storage backend must implement this interface.
 */
interface BackendInterface
{

    /**
     * Get an id that identifies the current storage. Eg `Plugin_$pluginName_Settings` could be a storage id
     * for plugin settings. It's kind of like a cache key and the value will be actually used for this by a cache
     * decorator.
     *
     * @return string
     */
    public function getStorageId();

    /**
     * Saves (persists) the current setting values in the database. Always all values that belong to a group of
     * settings or backend needs to be passed. Usually existing values will be deleted and new values will be saved
     * @param array $values An array of key value pairs where $settingName => $settingValue.
     *                      Eg array('settingName1' > 'settingValue1')
     */
    public function save($values);

    /**
     * Deletes all saved settings.
     * @return void
     */
    public function delete();

    /**
     * Loads previously saved setting values and returns them (if some were saved)
     *
     * @return array An array of key value pairs where $settingName => $settingValue.
     *               Eg array('settingName1' > 'settingValue1')
     */
    public function load();
}
