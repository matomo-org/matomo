<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Storage\Backend;

use Piwik\Common;
use Piwik\Db;
use Exception;

/**
 * Plugin settings backend. Stores all settings in a "plugin_setting" database table.
 *
 * If a value that needs to be stored is an array, will insert a new row for each value of this array.
 */
class PluginSettingsTable extends BaseSettingsTable
{
    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var string
     */
    private $userLogin;

    public function __construct($pluginName, $userLogin)
    {
        parent::__construct();

        if (empty($pluginName)) {
            throw new Exception('No plugin name given for PluginSettingsTable backend');
        }

        if ($userLogin === false || $userLogin === null) {
            throw new Exception('Invalid user login name given for PluginSettingsTable backend');
        }

        $this->pluginName = $pluginName;
        $this->userLogin = $userLogin;
    }

    /**
     * @inheritdoc
     */
    public function getStorageId()
    {
        return 'PluginSettings_' . $this->pluginName . '_User_' . $this->userLogin;
    }

    /**
     * Saves (persists) the current setting values in the database.
     * @param array $values Key/value pairs of setting values to be written
     */
    public function save($values)
    {
        $this->initDbIfNeeded();

        $valuesKeep = array();

        foreach ($values as $name => $value) {
            if (!isset($value)) {
                continue;
            }
            if (is_array($value) || is_object($value)) {
                $jsonEncoded = 1;
                $value = json_encode($value);
            } else {
                $jsonEncoded = 0;
                if (is_bool($value)) {
                    // we are currently not storing booleans as json as it could result in trouble with the UI and regress
                    // preselecting the correct value
                    $value = (int) $value;
                }
            }

            $valuesKeep[] = array($this->pluginName, $this->userLogin, $name, $value, $jsonEncoded);
        }

        $columns = self::getColumns();

        $table = $this->getTableName();
        $lockKey = $this->getStorageId();
        $this->lock->execute($lockKey, function () use ($valuesKeep, $table, $columns) {
            $this->delete();
            // No values = nothing to save
            if (!empty($valuesKeep)) {
                Db\BatchInsert::tableInsertBatchSql($table, $columns, $valuesKeep);
            }
        });
    }

    /**
     * Save exactly one setting key atomically for the current plugin/user context
     *
     * if the value is null, the key is deleted.
     *
     * @throws Exception when param $settingName is empty
     */
    /**
     * @param mixed $value
     */
    public function saveValue(string $settingName, $value): void
    {
        $this->initDbIfNeeded();

        if (empty($settingName)) {
            throw new Exception('No setting name given for PluginSettingsTable backend');
        }

        $table = $this->getTableName();
        $lockKey = $this->getStorageId();
        $columns = self::getColumns();

        $this->lock->execute($lockKey, function () use ($table, $columns, $settingName, $value) {
            $this->deleteValueWithoutLock($settingName);

            if (is_null($value)) {
                return;
            }

            [$storedValue, $jsonEncoded] = $this->encodeValueForStorage($value);
            Db\BatchInsert::tableInsertBatchSql(
                $table,
                $columns,
                [[$this->pluginName, $this->userLogin, $settingName, $storedValue, $jsonEncoded]]
            );
        });
    }

    /**
     * @throws Exception when param $settingName is empty
     */
    /**
     * @param mixed $defaultValue
     * @return mixed
     */
    public function loadValue(string $settingName, $defaultValue = null)
    {
        $this->initDbIfNeeded();
        if (empty($settingName)) {
            throw new Exception('No setting name given for PluginSettingsTable backend');
        }

        $table = $this->getTableName();

        $sql = "SELECT `setting_name`, `setting_value`, `json_encoded`
                FROM `$table`
                WHERE plugin_name = ? and user_login = ? and setting_name = ?";
        $bind = [$this->pluginName, $this->userLogin, $settingName];

        try {
            $rows = $this->db->fetchAll($sql, $bind);
        } catch (\Exception $e) {
            if ($this->jsonEncodedMissingError($e)) {
                $sql = "SELECT `setting_name`, `setting_value`
                        FROM `$table`
                        WHERE plugin_name = ? and user_login = ? and setting_name = ?";
                $rows = $this->db->fetchAll($sql, $bind);
            } else {
                throw $e;
            }
        }

        if (empty($rows)) {
            return $defaultValue;
        }

        $flat = $this->flattenSettingsRows($rows);
        return array_key_exists($settingName, $flat) ? $flat[$settingName] : $defaultValue;
    }

    /**
     * @throws Exception when param $settingName is empty
     */
    public function deleteValue(string $settingName): void
    {
        $this->initDbIfNeeded();

        if (empty($settingName)) {
            throw new Exception('No setting name given for PluginSettingsTable backend');
        }

        $lockKey = $this->getStorageId();
        $this->lock->execute($lockKey, function () use ($settingName) {
            $this->deleteValueWithoutLock($settingName);
        });
    }


    private function jsonEncodedMissingError(Exception $e)
    {
        return strpos($e->getMessage(), 'json_encoded') !== false;
    }

    public function load()
    {
        $this->initDbIfNeeded();

        $sql = "SELECT `setting_name`, `setting_value`, `json_encoded` FROM `" . $this->getTableName() . "` WHERE plugin_name = ? and user_login = ?";
        $bind = array($this->pluginName, $this->userLogin);

        try {
            $settings = $this->db->fetchAll($sql, $bind);
        } catch (\Exception $e) {
            // we catch an exception since json_encoded might not be present before matomo is updated to 3.5.0+ but the updater
            // may run this query
            if ($this->jsonEncodedMissingError($e)) {
                $sql = "SELECT `setting_name`, `setting_value` FROM `" . $this->getTableName() . "` WHERE plugin_name = ? and user_login = ?";
                $settings = $this->db->fetchAll($sql, $bind);
            } else {
                throw $e;
            }
        }

        return $this->flattenSettingsRows($settings);
    }

    /**
     * Returns selected setting names for all users in one plugin
     *
     * @param string[] $settingNames
     * @return array<string, array<string, mixed>>
     */
    public function loadValuesForUsers(array $settingNames): array
    {
        $this->initDbIfNeeded();
        if (empty($settingNames)) {
            return [];
        }

        $table = $this->getTableName();
        $placeholders = Common::getSqlStringFieldsArray($settingNames);
        $sql = "SELECT user_login, setting_name, setting_value, json_encoded
                FROM `$table`
                WHERE plugin_name = ?
                    AND setting_name IN ($placeholders)
                    AND user_login <> ''";
        $bind = array_merge([$this->pluginName], $settingNames);

        try {
            $rows = $this->db->fetchAll($sql, $bind);
        } catch (\Exception $e) {
            if ($this->jsonEncodedMissingError($e)) {
                $sql = "SELECT user_login, setting_name, setting_value
                        FROM `$table`
                        WHERE plugin_name = ?
                            AND setting_name IN ($placeholders)
                            AND user_login <> ''";
                $rows = $this->db->fetchAll($sql, $bind);
            } else {
                throw $e;
            }
        }

        $valuesByUser = [];
        foreach ($rows as $row) {
            $jsonEncoded = isset($row['json_encoded']) ? (bool) $row['json_encoded'] : false;
            $value = $this->decodeValueFromStorage(
                $row['setting_value'],
                $jsonEncoded
            );
            $valuesByUser[$row['user_login']][$row['setting_name']] = $value;
        }
        return $valuesByUser;
    }

    protected function getTableName()
    {
        return Common::prefixTable('plugin_setting');
    }

    public function delete()
    {
        $this->initDbIfNeeded();

        $table = $this->getTableName();
        $sql = "DELETE FROM `$table` WHERE `plugin_name` = ? and `user_login` = ?";
        $bind  = array($this->pluginName, $this->userLogin);

        $this->db->query($sql, $bind);
    }

    private function deleteValueWithoutLock(string $settingName): void
    {
        $this->initDbIfNeeded();

        $table = $this->getTableName();
        $sql = "DELETE FROM `$table`
                WHERE `plugin_name` = ? and `user_login` = ? and `setting_name` = ?";
        $bind = [$this->pluginName, $this->userLogin, $settingName];

        $this->db->query($sql, $bind);
    }

    /**
     * Unsets all settings for a user. The settings will be removed from the database. Used when
     * a user is deleted.
     *
     * @internal
     * @param string $userLogin
     * @throws \Exception If the `$userLogin` is empty. Otherwise we would delete most plugin settings
     */
    public static function removeAllUserSettingsForUser($userLogin)
    {
        if (empty($userLogin)) {
            throw new Exception('No userLogin specified. Cannot remove all settings for this user');
        }

        try {
            $table = Common::prefixTable('plugin_setting');
            Db::get()->query(sprintf('DELETE FROM `%s` WHERE user_login = ?', $table), array($userLogin));
        } catch (Exception $e) {
            if ($e->getCode() != 42) {
                // ignore table not found error, which might occur when updating from an older version of Piwik
                throw $e;
            }
        }
    }

    /**
     * Unsets all settings for a plugin. The settings will be removed from the database. Used when
     * a plugin is uninstalled.
     *
     * @internal
     * @param string $pluginName
     * @throws \Exception If the `$userLogin` is empty.
     */
    public static function removeAllSettingsForPlugin($pluginName)
    {
        try {
            $table = Common::prefixTable('plugin_setting');
            Db::get()->query(sprintf('DELETE FROM `%s` WHERE plugin_name = ?', $table), array($pluginName));
        } catch (Exception $e) {
            if ($e->getCode() != 42) {
                // ignore table not found error, which might occur when updating from an older version of Piwik
                throw $e;
            }
        }
    }

    /**
     * @return string[]
     */
    private static function getColumns(): array
    {
        return ['plugin_name', 'user_login', 'setting_name', 'setting_value', 'json_encoded'];
    }

    /**
     * @param mixed $value
     * @return array{0: mixed, 1: int}
     */
    private function encodeValueForStorage($value): array
    {
        if (is_array($value) || is_object($value)) {
            return [json_encode($value), 1];
        }

        if (is_bool($value)) {
            return [(int) $value, 0];
        }

        return [$value, 0];
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function decodeValueFromStorage($value, bool $jsonEncoded)
    {
        if ($jsonEncoded) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function flattenSettingsRows(array $settings): array
    {
        $flat = [];
        foreach ($settings as $setting) {
            $name = $setting['setting_name'];
            $jsonEncoded = isset($setting['json_encoded']) ? (bool) $setting['json_encoded'] : false;
            $value = $this->decodeValueFromStorage($setting['setting_value'], $jsonEncoded);
            if (array_key_exists($name, $flat) && !$jsonEncoded) {
                $flat[$name] = (array) $flat[$name];
                $flat[$name][] = $value;
            } else {
                $flat[$name] = $value;
            }
        }
        return $flat;
    }
}
