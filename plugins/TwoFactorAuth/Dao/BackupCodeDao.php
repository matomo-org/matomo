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

    public function getAllBackupCodesForLogin($login)
    {
        $sql = sprintf('SELECT backup_code FROM %s WHERE login = ?', $this->tablePrefixed);
        $rows = Db::fetchAll($sql, array($login));
        $codes = array_column($rows, 'backup_code');
        return $codes;
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

}

