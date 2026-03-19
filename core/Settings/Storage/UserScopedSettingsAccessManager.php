<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Storage;

use Exception;
use Piwik\Settings\Storage\Backend\PluginSettingsTable;

class UserScopedSettingsAccessManager
{
    /**
     * @var array<string, PluginSettingsTable>
     */
    private $backends = [];

    /**
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get(string $pluginName, string $userLogin, string $key, $defaultValue = null)
    {
        return $this->getBackend($pluginName, $userLogin)->loadValue($key, $defaultValue);
    }

    /**
     * @param mixed $value
     */
    public function set(string $pluginName, string $userLogin, string $key, $value): void
    {
        $this->getBackend($pluginName, $userLogin)->saveValue($key, $value);
    }

    public function getAll(string $pluginName, string $userLogin): array
    {
        $this->validatePluginAndLogin($pluginName, $userLogin);
        return $this->getBackend($pluginName, $userLogin)->load();
    }

    public function setAll(string $pluginName, string $userLogin, array $settings): void
    {
        $this->validatePluginAndLogin($pluginName, $userLogin);
        $this->getBackend($pluginName, $userLogin)->save($settings);
    }

    public function delete(string $pluginName, string $userLogin, string $key): void
    {
        $this->getBackend($pluginName, $userLogin)->deleteValue($key);
    }

    public function deleteAll(string $pluginName, string $userLogin): void
    {
        $this->validatePluginAndLogin($pluginName, $userLogin);
        $this->getBackend($pluginName, $userLogin)->delete();
    }

    /**
     * @param string[] $settingNames
     * @return array<string, array<string, mixed>>
     */
    public function getValuesForAllUsers(string $pluginName, array $settingNames): array
    {
        if (empty($pluginName) || empty($settingNames)) {
            return [];
        }

        // login is not used by loadValuesForUsers(), value is irrelevant
        return $this->getBackend($pluginName, $userLogin = '')->loadValuesForUsers($settingNames);
    }

    private function validatePluginAndLogin(string $pluginName, string $userLogin): void
    {
        if (empty($pluginName)) {
            throw new \Exception('No plugin name specified for user scoped settings store');
        }

        if (empty($userLogin)) {
            throw new \Exception('No username specified for user scoped settings store');
        }
    }

    /**
     * @throws Exception when $pluginName is empty
     */
    private function getBackend(string $pluginName, string $userLogin): PluginSettingsTable
    {
        if (empty($pluginName)) {
            throw new \Exception('No plugin name specified for user scoped settings store');
        }

        $id = $pluginName . '#' . $userLogin;
        if (empty($this->backends[$id])) {
            $this->backends[$id] = new PluginSettingsTable($pluginName, $userLogin);
        }
        return $this->backends[$id];
    }
}
