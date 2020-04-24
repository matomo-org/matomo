<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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

        $columns = array('plugin_name', 'user_login', 'setting_name', 'setting_value', 'json_encoded');

        $table = $this->getTableName();
        $lockKey = $this->getStorageId();
        $this->lock->execute($lockKey, function() use ($valuesKeep, $table, $columns) {
            $this->delete();
            // No values = nothing to save
            if (!empty($valuesKeep)) {
                Db\BatchInsert::tableInsertBatchSql($table, $columns, $valuesKeep);
            }
        });
    }

    private function jsonEncodedMissingError(Exception $e)
    {
        return strpos($e->getMessage(), 'json_encoded') !== false;
    }

    public function load()
    {
        $this->initDbIfNeeded();

        $sql  = "SELECT `setting_name`, `setting_value`, `json_encoded` FROM " . $this->getTableName() . " WHERE plugin_name = ? and user_login = ?";
        $bind = array($this->pluginName, $this->userLogin);

        try {
            $settings = $this->db->fetchAll($sql, $bind);
        } catch (\Exception $e) {
            // we catch an exception since json_encoded might not be present before matomo is updated to 3.5.0+ but the updater
            // may run this query
            if ($this->jsonEncodedMissingError($e)) {
                $sql  = "SELECT `setting_name`, `setting_value` FROM " . $this->getTableName() . " WHERE plugin_name = ? and user_login = ?";
                $settings = $this->db->fetchAll($sql, $bind);
            } else {
                throw $e;
            }
        }

        $flat = array();
        foreach ($settings as $setting) {
            $name = $setting['setting_name'];

            if (!empty($setting['json_encoded'])) {
                $flat[$name] = json_decode($setting['setting_value'], true);
            } elseif (array_key_exists($name, $flat)) {
                if (!is_array($flat[$name])) {
                    $flat[$name] = array($flat[$name]);
                }
                $flat[$name][] = $setting['setting_value'];
            } else {
                $flat[$name] = $setting['setting_value'];
            }
        }

        return $flat;
    }

    protected function getTableName()
    {
        return Common::prefixTable('plugin_setting');
    }

    public function delete()
    {
        $this->initDbIfNeeded();

        $table = $this->getTableName();
        $sql   = "DELETE FROM $table WHERE `plugin_name` = ? and `user_login` = ?";
        $bind  = array($this->pluginName, $this->userLogin);

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
            Db::get()->query(sprintf('DELETE FROM %s WHERE user_login = ?', $table), array($userLogin));
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
            Db::get()->query(sprintf('DELETE FROM %s WHERE plugin_name = ?', $table), array($pluginName));
        } catch (Exception $e) {
            if ($e->getCode() != 42) {
                // ignore table not found error, which might occur when updating from an older version of Piwik
                throw $e;
            }
        }
    }
}
