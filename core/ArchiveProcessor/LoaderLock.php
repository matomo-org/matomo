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

    const MAX_LOCK_TIME = 60; //in seconds
    protected $id;

    public function __construct($id)
    {
        // for multi tenant database solution
        $id = md5(SettingsPiwik::getPiwikInstanceId() . $id);
        $this->id = $id;
    }

    /*
     * this is only support by MySQL 5.6 or above.
     */
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

}