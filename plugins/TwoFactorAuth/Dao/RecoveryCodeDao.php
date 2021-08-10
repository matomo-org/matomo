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

class RecoveryCodeDao
{
    protected $table = 'twofactor_recovery_code';
    protected $tablePrefixed = '';

    /**
     * @var RecoveryCodeRandomGenerator $generator
     */
    private $generator;

    public function __construct(RecoveryCodeRandomGenerator $generator)
    {
        $this->tablePrefixed = Common::prefixTable($this->table);
        $this->generator = $generator;
    }

    public function getPrefixedTableName()
    {
        return $this->tablePrefixed;
    }

    public function createRecoveryCodesForLogin($login)
    {
        $codes = array();
        $this->deleteAllRecoveryCodesForLogin($login);

        for ($i = 0; $i < 10; $i++) {
            $code = $this->generator->generateCode();
            $code = mb_strtoupper($code);
            $this->insertRecoveryCode($login, $code);
            $codes[] = $code;
        }
        return $codes;
    }

    public function insertRecoveryCode($login, $recoveryCode)
    {
        // we do not really care about duplicates as it is very unlikely to happen, that's why we don't even use a
        // unique login,recovery_code index
        $sql = sprintf('INSERT INTO %s (`login`, `recovery_code`) VALUES(?,?)', $this->tablePrefixed);
        Db::query($sql, array($login, $recoveryCode));
    }

    public function useRecoveryCode($login, $recoveryCode)
    {
        if ($this->deleteRecoveryCode($login, $recoveryCode)) {
            return true;
        }
        return false;
    }

    public function getAllRecoveryCodesForLogin($login)
    {
        $sql = sprintf('SELECT recovery_code FROM %s WHERE login = ?', $this->tablePrefixed);
        $rows = Db::fetchAll($sql, array($login));
        $codes = array_column($rows, 'recovery_code');
        return $codes;
    }

    public function deleteRecoveryCode($login, $recoveryCode)
    {
        $sql = sprintf('DELETE FROM %s WHERE login = ? and recovery_code = ?', $this->tablePrefixed);
        $query = Db::query($sql, array($login, $recoveryCode));
        return $query->rowCount();
    }

    public function deleteAllRecoveryCodesForLogin($login)
    {
        $query = sprintf('DELETE FROM %s WHERE login = ?', $this->tablePrefixed);

        Db::query($query, array($login));
    }

}

