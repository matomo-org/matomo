<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ArchiveProcessor;

use Piwik\Db;
use Piwik\SettingsPiwik;

class LoaderLock
{

    const MAX_LEN_LOCK_NAME = 64;
    const MAX_LOCK_TIME = 60; //in seconds
    protected $id;

    /**
     * @param string $id
     * @throws \Exception
     */
    public function __construct($id)
    {
        // instanceId is needed for multi tenant database solution
        $id = SettingsPiwik::getPiwikInstanceId() . $id;

        if (mb_strlen($id) >= self::MAX_LEN_LOCK_NAME) {
            //convert ot prefix and md5 full length
            $id = mb_substr($id, 0, 32) . md5($id);
        }

        $this->id = $id;
    }

    public function setLock()
    {
        Db::fetchOne('SELECT GET_LOCK(?,?)', array($this->id, self::MAX_LOCK_TIME));
    }

    public function unLock()
    {
        Db::query('DO RELEASE_LOCK(?)', array($this->id));
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @description check if the lock is available to user
     * @param string $key
     * @return bool
     * @throws \Exception
     */
    public static function isLockAvailable($key)
    {
        return (bool)Db::fetchOne('SELECT IS_FREE_LOCK(?)', [$key]);

    }

}
