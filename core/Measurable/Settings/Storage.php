<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Measurable\Settings;

use Piwik\Db;
use Piwik\Common;
use Piwik\Settings\Setting;

/**
 * Storage for site settings
 */
class Storage extends \Piwik\Settings\Storage
{
    private $idSite = null;

    /**
     * @var Db
     */
    private $db = null;

    private $toBeDeleted = array();

    public function __construct(Db\AdapterInterface $db, $idSite)
    {
        $this->db     = $db;
        $this->idSite = $idSite;
    }

    protected function deleteSettingsFromStorage()
    {
        $table = $this->getTableName();
        $sql   = "DELETE FROM $table WHERE `idsite` = ?";
        $bind  = array($this->idSite);

        $this->db->query($sql, $bind);
    }

    public function deleteValue(Setting $setting)
    {
        $this->toBeDeleted[$setting->getName()] = true;
        parent::deleteValue($setting);
    }

    public function setValue(Setting $setting, $value)
    {
        $this->toBeDeleted[$setting->getName()] = false; // prevent from deleting this setting, we will create/update it
        parent::setValue($setting, $value);
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save()
    {
        $table = $this->getTableName();

        foreach ($this->toBeDeleted as $name => $delete) {
            if ($delete) {
                $sql  = "DELETE FROM $table WHERE `idsite` = ? and `setting_name` = ?";
                $bind = array($this->idSite, $name);

                $this->db->query($sql, $bind);
            }
        }

        $this->toBeDeleted = array();

        foreach ($this->settingsValues as $name => $value) {
            $value = serialize($value);

            $sql  = "INSERT INTO $table (`idsite`, `setting_name`, `setting_value`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `setting_value` = ?";
            $bind = array($this->idSite, $name, $value, $value);

            $this->db->query($sql, $bind);
        }
    }

    protected function loadSettings()
    {
        $sql  = "SELECT `setting_name`, `setting_value` FROM " . $this->getTableName() . " WHERE idsite = ?";
        $bind = array($this->idSite);

        $settings =$this->db->fetchAll($sql, $bind);

        $flat = array();
        foreach ($settings as $setting) {
            $flat[$setting['setting_name']] = unserialize($setting['setting_value']);
        }

        return $flat;
    }

    private function getTableName()
    {
        return Common::prefixTable('site_setting');
    }
}
