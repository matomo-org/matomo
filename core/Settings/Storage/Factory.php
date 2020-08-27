<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage;

use Piwik\Settings\Storage\Backend\BackendInterface;
use Piwik\SettingsServer;

/**
 * Factory to create an instance of a storage. The storage can be created with different backends depending on the need.
 */
class Factory
{
    // cache prevents multiple loading of storage
    private $cache = array();

    /**
     * Get a storage instance for plugin settings.
     *
     * The storage will hold values that belong to the given plugin name and user login. Be aware that instances
     * for a specific plugin and login will be cached during one request for better performance.
     *
     * @param string $pluginName
     * @param string $userLogin Use an empty string if settings should be not for a specific login
     * @return Storage
     */
    public function getPluginStorage($pluginName, $userLogin)
    {
        $id = $pluginName . '#' . $userLogin;

        if (!isset($this->cache[$id])) {
            $backend = new Backend\PluginSettingsTable($pluginName, $userLogin);
            $this->cache[$id] = $this->makeStorage($backend);
        }

        return $this->cache[$id];
    }

    /**
     * @param string $section
     * @return mixed
     */
    public function getConfigStorage($section)
    {
        $id = 'config' . $section;
        if (!isset($this->cache[$id])) {
            $backend = new Backend\Config($section);
            $this->cache[$id] = $this->makeStorage($backend);
        }

        return $this->cache[$id];
    }

    /**
     * Get a storage instance for measurable settings.
     *
     * The storage will hold values that belong to the given idSite and plugin name. Be aware that a storage instance
     * for a specific site and plugin will be cached during one request for better performance.
     *
     * @param int $idSite   If idSite is empty it will use a backend that never actually persists any value. Pass
     *                      $idSite = 0 to create a storage for a site that is about to be created.
     * @param string $pluginName
     * @return Storage
     */
    public function getMeasurableSettingsStorage($idSite, $pluginName)
    {
        $id = 'measurableSettings' . (int) $idSite . '#' . $pluginName;

        if (empty($idSite)) {
            return $this->getNonPersistentStorage($id . '#nonpersistent');
        }

        if (!isset($this->cache[$id])) {
            $backend = new Backend\MeasurableSettingsTable($idSite, $pluginName);
            $this->cache[$id] = $this->makeStorage($backend);
        }

        return $this->cache[$id];
    }

    /**
     * Get a storage instance for settings that will be saved in the "site" table.
     *
     * The storage will hold values that belong to the given idSite. Be aware that a storage instance for a specific
     * site will be cached during one request for better performance.
     *
     * @param int $idSite   If idSite is empty it will use a backend that never actually persists any value. Pass
     *                      $idSite = 0 to create a storage for a site that is about to be created.
     *
     * @param int $idSite
     * @return Storage
     */
    public function getSitesTable($idSite)
    {
        $id = 'sitesTable#' . $idSite;

        if (empty($idSite)) {
            return $this->getNonPersistentStorage($id . '#nonpersistent');
        }

        if (!isset($this->cache[$id])) {
            $backend = new Backend\SitesTable($idSite);
            $this->cache[$id] = $this->makeStorage($backend);
        }

        return $this->cache[$id];
    }

    /**
     * Get a storage with a backend that will never persist or load any value.
     *
     * @param string $key
     * @return Storage
     */
    public function getNonPersistentStorage($key)
    {
        return new Storage(new Backend\NullBackend($key));
    }

    /**
     * Makes a new storage object based on a custom backend interface.
     *
     * @param BackendInterface $backend
     * @return Storage
     */
    public function makeStorage(BackendInterface $backend)
    {
        if (SettingsServer::isTrackerApiRequest()) {
            $backend = new Backend\Cache($backend);
        }

        return new Storage($backend);
    }
}
