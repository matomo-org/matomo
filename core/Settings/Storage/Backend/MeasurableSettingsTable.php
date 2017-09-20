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
 * Measurable settings backend. Stores all settings in a "site_setting" database table.
 *
 * If a value that needs to be stored is an array, will insert a new row for each value of this array.
 */
class MeasurableSettingsTable implements BackendInterface
{
    /**
     * @var int
     */
    private $idSite;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var Db\AdapterInterface
     */
    private $db;

    public function __construct($idSite, $pluginName)
    {
        if (empty($pluginName)) {
            throw new Exception('No plugin name given for MeasurableSettingsTable backend');
        }

        if (empty($idSite)) {
            throw new Exception('No idSite given for MeasurableSettingsTable backend');
        }

        $this->idSite = (int) $idSite;
        $this->pluginName = $pluginName;
    }

    private function initDbIfNeeded()
    {
        if (!isset($this->db)) {
            // we need to avoid db creation on instance creation, especially important in tracker mode
            // the db might be never actually used when values are eg fetched from a cache
            $this->db = Db::get();
        }
    }

    /**
     * @inheritdoc
     */
    public function getStorageId()
    {
        return 'MeasurableSettings_' . $this->idSite . '_' . $this->pluginName;
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

                $sql  = "INSERT INTO $table (`idsite`, `plugin_name`, `setting_name`, `setting_value`) VALUES (?, ?, ?, ?)";
                $bind = array($this->idSite, $this->pluginName, $name, $val);

                $this->db->query($sql, $bind);
            }
        }
    }

    public function load()
    {
        $this->initDbIfNeeded();

        $table = $this->getTableName();

        $sql  = "SELECT `setting_name`, `setting_value` FROM " . $table . " WHERE idsite = ? and plugin_name = ?";
        $bind = array($this->idSite, $this->pluginName);

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
        return Common::prefixTable('site_setting');
    }

    public function delete()
    {
        $this->initDbIfNeeded();

        $table = $this->getTableName();
        $sql   = "DELETE FROM $table WHERE `idsite` = ? and plugin_name = ?";
        $bind  = array($this->idSite, $this->pluginName);

        $this->db->query($sql, $bind);
    }

    /**
     * @internal
     * @param int $idSite
     * @throws \Exception
     */
    public static function removeAllSettingsForSite($idSite)
    {
        try {
            $query = sprintf('DELETE FROM %s WHERE idsite = ?', Common::prefixTable('site_setting'));
            Db::query($query, array($idSite));
        } catch (Exception $e) {
            if ($e->getCode() != 42) {
                // ignore table not found error, which might occur when updating from an older version of Piwik
                throw $e;
            }
        }
    }

    /**
     * @internal
     * @param string $pluginName
     * @throws \Exception
     */
    public static function removeAllSettingsForPlugin($pluginName)
    {
        try {
            $query = sprintf('DELETE FROM %s WHERE plugin_name = ?', Common::prefixTable('site_setting'));
            Db::query($query, array($pluginName));
        } catch (Exception $e) {
            if ($e->getCode() != 42) {
                // ignore table not found error, which might occur when updating from an older version of Piwik
                throw $e;
            }
        }
    }
}
