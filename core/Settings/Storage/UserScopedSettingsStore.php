<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Storage;

use Piwik\Common;
use Piwik\Db;

class UserScopedSettingsStore
{
    /**
     * @var Factory
     */
    private $factory;

    public function __construct(?Factory $factory = null)
    {
        if ($factory === null) {
            $factory = new Factory();
        }

        $this->factory = $factory;
    }

    /**
     * @param string $pluginName
     * @param string $userLogin
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get(string $pluginName, string $userLogin, string $key, $defaultValue = null)
    {
        $settings = $this->getAll($pluginName, $userLogin);

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $defaultValue;
    }

    /**
     * @param string $pluginName
     * @param string $userLogin
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $pluginName, string $userLogin, string $key, $value): void
    {
        $settings = $this->getAll($pluginName, $userLogin);
        $settings[$key] = $value;
        $this->setAll($pluginName, $userLogin, $settings);
    }

    /**
     * @param string $pluginName
     * @param string $userLogin
     * @return array
     */
    public function getAll(string $pluginName, string $userLogin): array
    {
        $this->validatePluginAndLogin($pluginName, $userLogin);

        $storage = $this->factory->getPluginStorage($pluginName, $userLogin);
        $settings = $storage->getBackend()->load();

        if (is_array($settings)) {
            return $settings;
        }

        return [];
    }

    /**
     * @param string $pluginName
     * @param string $userLogin
     * @param array $settings
     * @return void
     */
    public function setAll(string $pluginName, string $userLogin, array $settings): void
    {
        $this->validatePluginAndLogin($pluginName, $userLogin);

        $storage = $this->factory->getPluginStorage($pluginName, $userLogin);
        $storage->getBackend()->save($settings);
    }

    /**
     * @param string $pluginName
     * @param string $userLogin
     * @param string $key
     * @return void
     */
    public function delete(string $pluginName, string $userLogin, string $key): void
    {
        $settings = $this->getAll($pluginName, $userLogin);
        unset($settings[$key]);
        $this->setAll($pluginName, $userLogin, $settings);
    }

    /**
     * @param string $pluginName
     * @param string $userLogin
     * @return void
     */
    public function deleteAll(string $pluginName, string $userLogin): void
    {
        $this->validatePluginAndLogin($pluginName, $userLogin);

        $storage = $this->factory->getPluginStorage($pluginName, $userLogin);
        $storage->getBackend()->delete();
    }

    /**
     * @param string $pluginName
     * @param string[] $settingNames
     * @return array<string, array<string, mixed>>
     */
    public function getValuesForAllUsers(string $pluginName, array $settingNames): array
    {
        if (empty($pluginName) || empty($settingNames)) {
            return [];
        }

        $placeholders = Common::getSqlStringFieldsArray($settingNames);
        $table = Common::prefixTable('plugin_setting');
        $sql = "SELECT user_login, setting_name, setting_value, json_encoded
                  FROM `$table`
                 WHERE plugin_name = ?
                   AND setting_name IN ($placeholders)
                   AND user_login <> ''";
        $bind = array_merge([$pluginName], $settingNames);

        $rows = Db::fetchAll($sql, $bind);
        $valuesByUser = [];

        foreach ($rows as $row) {
            $value = $row['setting_value'];
            if (!empty($row['json_encoded'])) {
                $value = json_decode($value, true);
            }
            $valuesByUser[$row['user_login']][$row['setting_name']] = $value;
        }

        return $valuesByUser;
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
}
