<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
class PluginSettingsTable implements BackendInterface
{
    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var string
     */
    private $userLogin;

    /**
     * @var Db\AdapterInterface
     */
    private $db;

    public function __construct($pluginName, $userLogin)
    {
        if (empty($pluginName)) {
            throw new Exception('No plugin name given for PluginSettingsTable backend');
        }

        if ($userLogin === false || $userLogin === null) {
            throw new Exception('Invalid user login name given for PluginSettingsTable backend');
        }

        $this->pluginName = $pluginName;
        $this->userLogin = $userLogin;
    }

    private function initDbIfNeeded()
    {
        if (!isset($this->db)) {
            // we do not want to create a db connection on backend creation
            $this->db = Db::get();
        }
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
     */
    public function save($values)
    {
        $this->initDbIfNeeded();

        $table = $this->getTableName();

        $this->delete();

        foreach ($values as $name => $value) {

            if (!is_array($value)) {
                $value = array($value);
            }

            foreach ($value as $val) {
                if (!isset($val)) {
                    continue;
                }

                if (is_bool($val)) {
                    $val = (int) $val;
                }

                $sql  = "INSERT INTO $table (`plugin_name`, `user_login`, `setting_name`, `setting_value`) VALUES (?, ?, ?, ?)";
                $bind = array($this->pluginName, $this->userLogin, $name, $val);

                $this->db->query($sql, $bind);
            }
        }
    }

    public function load()
    {
        $this->initDbIfNeeded();

        $sql  = "SELECT `setting_name`, `setting_value` FROM " . $this->getTableName() . " WHERE plugin_name = ? and user_login = ?";
        $bind = array($this->pluginName, $this->userLogin);

        $settings = $this->db->fetchAll($sql, $bind);

        $flat = array();
        foreach ($settings as $setting) {
            $name = $setting['setting_name'];

            if (array_key_exists($name, $flat)) {
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

    private function getTableName()
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
