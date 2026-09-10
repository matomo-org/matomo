<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Session\SaveHandler;

use Piwik\Db;
use Piwik\DbHelper;
use Exception;
use Piwik\SettingsPiwik;
use Piwik\Updater\Migration;
use Zend_Session;

/**
 * Database-backed session save handler
 *
 */
class DbTable implements \SessionHandlerInterface
{
    public static $wasSessionToLargeToRead = false;

    protected $config;
    protected $maxLifetime;

    public const TABLE_NAME = 'session';
    public const TOKEN_HASH_ALGO = 'sha512';

    /**
     * Session data as it was read, keyed by hashed session id. Lets write() tell an unchanged
     * session from a changed one.
     *
     * @var array<string, string>
     */
    private array $readData = [];

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->maxLifetime = ini_get('session.gc_maxlifetime');
    }

    private function hashSessionId($id)
    {
        $salt = SettingsPiwik::getSalt();
        return hash(self::TOKEN_HASH_ALGO, $id . $salt);
    }


    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        Zend_Session::writeClose();
    }

    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path
     * @param string $name
     */
    public function open($save_path, $name): bool
    {
        Db::get()->getConnection();

        return true;
    }

    /**
     * Close Session - free resources
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function read($id)
    {
        $id = $this->hashSessionId($id);
        $sql = 'SELECT ' . $this->config['dataColumn'] . ' FROM `' . $this->config['name'] . '`'
            . ' WHERE ' . $this->config['primary'] . ' = ?'
            . ' AND ' . $this->config['modifiedColumn'] . ' + ' . $this->config['lifetimeColumn'] . ' >= ?';

        $result = $this->fetchOne($sql, [$id, time()]);

        if (!$result) {
            $result = '';
        }

        $this->readData[$id] = $result;

        return $result;
    }

    private function fetchOne($sql, $bind)
    {
        try {
            $result = Db::get()->fetchOne($sql, $bind);
        } catch (Exception $e) {
            if (Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                $this->migrateToDbSessionTable();
                $result = Db::get()->fetchOne($sql, $bind);
            } else {
                throw $e;
            }
        }
        return $result;
    }

    private function query($sql, $bind)
    {
        try {
            $result = Db::get()->query($sql, $bind);
        } catch (Exception $e) {
            if (Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                $this->migrateToDbSessionTable();
                $result = Db::get()->query($sql, $bind);
            } else {
                throw $e;
            }
        }
        return $result;
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     */
    public function write($id, $data): bool
    {
        $id = $this->hashSessionId($id);

        // this request did not change the session, so only the timestamps need storing. rewriting
        // the data column would replace whatever a concurrent request has stored in the meantime.
        if (isset($this->readData[$id]) && $this->readData[$id] === $data) {
            $sql = 'INSERT INTO ' . $this->config['name']
                . ' (' . $this->config['primary'] . ','
                . $this->config['modifiedColumn'] . ','
                . $this->config['lifetimeColumn'] . ','
                . $this->config['dataColumn'] . ')'
                . ' VALUES (?,?,?,?)'
                . ' ON DUPLICATE KEY UPDATE '
                . $this->config['modifiedColumn'] . ' = ?,'
                . $this->config['lifetimeColumn'] . ' = ?';

            $this->query($sql, [$id, time(), $this->maxLifetime, $data, time(), $this->maxLifetime]);

            return true;
        }

        $sql = 'INSERT INTO ' . $this->config['name']
            . ' (' . $this->config['primary'] . ','
            . $this->config['modifiedColumn'] . ','
            . $this->config['lifetimeColumn'] . ','
            . $this->config['dataColumn'] . ')'
            . ' VALUES (?,?,?,?)'
            . ' ON DUPLICATE KEY UPDATE '
            . $this->config['modifiedColumn'] . ' = ?,'
            . $this->config['lifetimeColumn'] . ' = ?,'
            . $this->config['dataColumn'] . ' = ?';

        $this->query($sql, [$id, time(), $this->maxLifetime, $data, time(), $this->maxLifetime, $data]);

        $this->readData[$id] = $data;

        return true;
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     */
    public function destroy($id): bool
    {
        $id = $this->hashSessionId($id);

        unset($this->readData[$id]);

        $sql = 'DELETE FROM `' . $this->config['name'] . '` WHERE ' . $this->config['primary'] . ' = ?';

        $this->query($sql, [$id]);

        return true;
    }

    /**
     * Destroys all Sessions - removes all rows in Session table
     */
    public function destroyAll(): bool
    {
        $this->readData = [];

        $sql = 'TRUNCATE TABLE `' . $this->config['name'] . '`';

        $this->query($sql, []);

        return true;
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime timestamp in seconds
     * @return bool  always true
     */
    #[\ReturnTypeWillChange]
    public function gc($maxlifetime)
    {
        $sql = 'DELETE FROM `' . $this->config['name'] . '`'
            . ' WHERE ' . $this->config['modifiedColumn'] . ' + ' . $this->config['lifetimeColumn'] . ' < ?';

        $this->query($sql, [time()]);

        return true;
    }

    private function migrateToDbSessionTable()
    {
        // happens when updating from Piwik 1.4 or earlier to Matomo 3.7+
        // in this case on update it will change the session handler to dbtable, but it hasn't performed
        // the DB updates just yet which means the session table won't be available as it was only added in
        // Piwik 1.5 => results in a sql error the session table does not exist
        try {
            $sql = DbHelper::getTableCreateSql(self::TABLE_NAME);
            Db::query($sql);
        } catch (Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_EXISTS)) {
                throw $e;
            }
        }
    }
}
