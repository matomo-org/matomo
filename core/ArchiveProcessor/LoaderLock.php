<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ArchiveProcessor;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Context;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\Model;
use Piwik\DataAccess\RawLogDao;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\SettingsServer;
use Piwik\Site;
use Psr\Log\LoggerInterface;
use Piwik\CronArchive\SegmentArchiving;

/**
 * This class uses PluginsArchiver class to trigger data aggregation and create archives.
 */
class LoaderLock
{

    protected $id;

    public function __construct($id)
    {
        if (strlen($id) > 64) {
            throw new \Exception('excceed lenth');
        }
        $this->id = $id;
    }

    public function setLock()
    {
        Db::fetchOne('SELECT GET_LOCK(?,?)', array($this->id, 10));
    }

    public function unLock()
    {
        Db::query('DO RELEASE_LOCK(?)', array($this->id));
    }

    public function getLock()
    {
        $row = Db::fetchOne('SELECT IS_FREE_LOCK(?);', array($this->id));

        if ($row[0] === 1) {
            return false;
        }
        return true;
    }
}