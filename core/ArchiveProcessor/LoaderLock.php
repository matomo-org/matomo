<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

use Piwik\Db\Schema;
use Piwik\SettingsPiwik;

class LoaderLock
{
    public const MAX_LEN_LOCK_NAME = 64;
    public const MAX_LOCK_TIME = 60; //in seconds
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
        Schema::getInstance()->getNamedLock($this->id, self::MAX_LOCK_TIME);
    }

    public function unLock()
    {
        Schema::getInstance()->releaseNamedLock($this->id);
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Checks if the lock is available to use.
     *
     * @param string $key
     * @return bool
     */
    public static function isLockAvailable($key)
    {
        return Schema::getInstance()->isNamedLockFree($key);
    }
}
