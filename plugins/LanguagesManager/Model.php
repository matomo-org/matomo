<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 */
namespace Piwik\Plugins\LanguagesManager;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;

class Model
{
    private static $rawPrefix = 'user_language';
    private $table;

    public function __construct()
    {
        $this->table = Common::prefixTable(self::$rawPrefix);
    }

    public function deleteUserLanguage($userLogin)
    {
        Db::query('DELETE FROM ' . $this->table . ' WHERE login = ?', $userLogin);
    }

    /**
     * Returns the language for the user
     *
     * @param string $userLogin
     * @return string
     */
    public function getLanguageForUser($userLogin)
    {
        return Db::fetchOne('SELECT language FROM ' . $this->table .
                            ' WHERE login = ? ', array($userLogin));
    }

    /**
     * Sets the language for the user
     *
     * @param string $login
     * @param string $languageCode
     * @return bool
     */
    public function setLanguageForUser($login, $languageCode)
    {
        $query = 'INSERT INTO ' . $this->table .
                 ' (login, language) VALUES (?,?) ON DUPLICATE KEY UPDATE language=?';
        $bind  = array($login, $languageCode, $languageCode);
        Db::query($query, $bind);

        return true;
    }

    /**
     * Returns whether the given user has choosen to use 12 hour clock
     *
     * @param $userLogin
     * @return bool
     * @throws \Exception
     */
    public function uses12HourClock($userLogin)
    {
        return (bool) Db::fetchOne('SELECT use_12_hour_clock FROM ' . $this->table .
            ' WHERE login = ? ', array($userLogin));
    }

    /**
     * Sets whether the given user wants to use 12 hout clock
     *
     * @param string $login
     * @param string $use12HourClock
     * @return bool
     */
    public function set12HourClock($login, $use12HourClock)
    {
        $query = 'INSERT INTO ' . $this->table .
            ' (login, use_12_hour_clock) VALUES (?,?) ON DUPLICATE KEY UPDATE use_12_hour_clock=?';
        $bind  = array($login, $use12HourClock, $use12HourClock);
        Db::query($query, $bind);

        return true;
    }

    public static function install()
    {
        $userLanguage = "login VARCHAR( 100 ) NOT NULL ,
					     language VARCHAR( 10 ) NOT NULL ,
					     use_12_hour_clock TINYINT(1) NOT NULL DEFAULT 0 ,
					     PRIMARY KEY ( login )";
        DbHelper::createTable(self::$rawPrefix, $userLanguage);
    }

    public static function uninstall()
    {
        Db::dropTables(Common::prefixTable(self::$rawPrefix));
    }
}
