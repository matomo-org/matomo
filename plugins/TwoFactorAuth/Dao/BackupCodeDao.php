<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth\Dao;

use Piwik\Common;
use Piwik\Db;

class BackupCodeDao
{
    protected $table = 'twofactor_backup_code';
    protected $tablePrefixed = '';

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable($this->table);
    }

    public function createBackupCodesForLogin($login)
    {
        $codes = array();
        $this->deleteAllBackupCodesForLogin($login);

        for ($i = 0; $i < 10; $i++) {
            $backupCode = Common::getRandomString(16);
            $this->insertBackupCode($login, $backupCode);
            $codes[] = $backupCode;
        }
        return $codes;
    }

    public function insertBackupCode($login, $backupCode)
    {
        $sql = sprintf('INSERT INTO %s (`login`, `backup_code`) VALUES(?,?)', $this->tablePrefixed);
        Db::query($sql, array($login, $backupCode));
    }

    public function useBackupCode($login, $backupCode)
    {
        if ($this->deleteBackupCode($login, $backupCode)) {
            return true;
        }
        return false;
    }

    public function deleteBackupCode($login, $backupCode)
    {
        $sql = sprintf('DELETE FROM %s WHERE login = ? and backup_code = ?', $this->tablePrefixed);
        $query = Db::query($sql, array($login, $backupCode));
        return $query->rowCount();
    }

    public function deleteAllBackupCodesForLogin($login)
    {
        $query = sprintf('DELETE FROM %s WHERE login = ?', $this->tablePrefixed);

        Db::query($query, array($login));
    }

    public function updateEntity($columns, $whereColumns)
    {
        if (!empty($columns)) {
            $fields = array();
            $bind = array();
            foreach ($columns as $key => $value) {
                $fields[] = ' ' . $key . ' = ?';
                $bind[] = $value;
            }
            $fields = implode(',', $fields);
            $where = [];
            foreach ($whereColumns as $col => $val) {
                $where[] = '`' . $col .'` = ?';
                $bind[] = $val;
            }
            $where = implode(' AND ', $where);

            $query = sprintf('UPDATE %s SET %s WHERE %s', $this->tablePrefixed, $fields, $where);

            // we do not use $db->update() here as this method is as well used in Tracker mode and the tracker DB does not
            // support "->update()". Therefore we use the query method where we know it works with tracker and regular DB

            Db::query($query, $bind);
        }
    }

}

