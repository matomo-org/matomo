<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Session\SaveHandler;

use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\DbHelper;
use Exception;
use Piwik\Log\LoggerInterface;
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
     * How often a write may be retried when other requests keep storing the session first. Every
     * failed attempt means another request succeeded and its data has been merged in, so needing
     * this many is only possible under contention that is worth knowing about.
     */
    private const MAX_WRITE_ATTEMPTS = 20;

    /**
     * Session data as it was read, keyed by hashed session id. Lets write() tell an unchanged
     * session from a changed one, and gives the merge something to treat as the common base.
     *
     * @var array<string, string>
     */
    private array $readData = [];

    /**
     * Merging only works while the session is stored the way {@see \Piwik\Session::start()} asks PHP
     * to store it. That ini_set is allowed to fail, and if it ever does every merge below returns
     * null and writing quietly goes back to storing whatever was written last.
     */
    private SessionDataMerger $merger;

    /**
     * @param array $config
     */
    public function __construct($config, ?SessionDataMerger $merger = null)
    {
        $this->config = $config;
        $this->maxLifetime = ini_get('session.gc_maxlifetime');
        $this->merger = $merger ?: new SessionDataMerger();
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

    private function fetchRow($sql, $bind)
    {
        try {
            $result = Db::get()->fetchRow($sql, $bind);
        } catch (Exception $e) {
            if (Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                $this->migrateToDbSessionTable();
                $result = Db::get()->fetchRow($sql, $bind);
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

        $read = isset($this->readData[$id]) ? $this->readData[$id] : null;

        // this request did not change the session, so only the timestamps need storing. rewriting
        // the data column would replace whatever a concurrent request has stored in the meantime.
        if ($read !== null && $read === $data) {
            $this->touchTimestamps($id, $data);

            return true;
        }

        if ($read === null || $read === '') {
            // there was nothing to build on, so claim the row instead of assuming this request is
            // the only one starting the session
            if ($this->insertIfAbsent($id, $data)) {
                $this->readData[$id] = $data;

                return true;
            }

            $row = $this->fetchSessionRow($id);

            if (empty($row)) {
                return $this->store($id, $data);
            }

            $expected = (string) $row[$this->config['dataColumn']];
            // an expired row holds a session that is over. using it as the base lets this request
            // win every key, so none of the old session is merged back in.
            $base = $this->hasExpired($row) ? $expected : '';
        } else {
            $expected = $base = $read;
        }

        return $this->storeMerging($id, $data, $base, $expected);
    }

    /**
     * Stores the data, merging in anything another request stored since this one read the session.
     */
    private function storeMerging($id, $data, $base, $expected)
    {
        // the row already holds something this request never read, so there is something to merge
        // before the first attempt is worth making
        if ($base !== $expected) {
            $merged = $this->merger->merge($base, $data, $expected);

            if (null !== $merged) {
                $data = $merged;
            } else {
                $this->getLogger()->debug(
                    'Session data stored by another request could not be read, so it is being replaced'
                    . ' rather than merged into.'
                );
            }

            $base = $expected;
        }

        for ($attempt = 1; $attempt <= self::MAX_WRITE_ATTEMPTS; $attempt++) {
            if ($this->compareAndSet($id, $expected, $data)) {
                $this->readData[$id] = $data;

                return true;
            }

            $row = $this->fetchSessionRow($id);

            if (empty($row)) {
                // removed while this request was running, so recreate it below
                $this->getLogger()->debug('Session row disappeared while it was being written, recreating it.');

                break;
            }

            $current = (string) $row[$this->config['dataColumn']];

            if ($current === $data) {
                $this->readData[$id] = $data;

                return true;
            }

            $merged = $this->merger->merge($base, $data, $current);

            if (null === $merged) {
                // not a session this can read, so keep the newest value as it always used to
                $this->getLogger()->debug(
                    'Session data stored by another request could not be read, so it is being replaced'
                    . ' rather than merged into.'
                );

                break;
            }

            $data = $merged;
            // what was just merged in is what the next attempt builds on
            $base = $expected = $current;

            if ($attempt < self::MAX_WRITE_ATTEMPTS) {
                // spread retries out so that several requests do not keep colliding in step
                usleep(mt_rand(0, 2000));
            } else {
                $this->getLogger()->warning(
                    'Gave up merging a session after {attempts} attempts, so it is being stored as it is.'
                    . ' Anything another request stored in the meantime is lost.',
                    ['attempts' => self::MAX_WRITE_ATTEMPTS]
                );
            }
        }

        return $this->store($id, $data);
    }

    private function getLogger(): LoggerInterface
    {
        return StaticContainer::get(LoggerInterface::class);
    }

    private function store($id, $data)
    {
        $this->upsert($id, $data);

        $this->readData[$id] = $data;

        return true;
    }

    private function touchTimestamps($id, $data)
    {
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
    }

    private function upsert($id, $data)
    {
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
    }

    private function insertIfAbsent($id, $data)
    {
        $sql = 'INSERT INTO ' . $this->config['name']
            . ' (' . $this->config['primary'] . ','
            . $this->config['modifiedColumn'] . ','
            . $this->config['lifetimeColumn'] . ','
            . $this->config['dataColumn'] . ')'
            . ' VALUES (?,?,?,?)';

        try {
            return $this->didChangeRow($sql, [$id, time(), $this->maxLifetime, $data]);
        } catch (Exception $e) {
            if (Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_DUPLICATE_ENTRY)) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Stores the data only while the row still holds what this request expects it to.
     */
    private function compareAndSet($id, $expected, $data)
    {
        // the column is not case sensitive but the value stored in it is, so compare it as bytes
        $sql = 'UPDATE ' . $this->config['name']
            . ' SET ' . $this->config['modifiedColumn'] . ' = ?,'
            . $this->config['lifetimeColumn'] . ' = ?,'
            . $this->config['dataColumn'] . ' = ?'
            . ' WHERE ' . $this->config['primary'] . ' = ?'
            . ' AND CAST(' . $this->config['dataColumn'] . ' AS BINARY) = ?';

        return $this->didChangeRow($sql, [time(), $this->maxLifetime, $data, $id, $expected]);
    }

    private function fetchSessionRow($id)
    {
        $sql = 'SELECT ' . $this->config['dataColumn'] . ','
            . $this->config['modifiedColumn'] . ','
            . $this->config['lifetimeColumn']
            . ' FROM `' . $this->config['name'] . '`'
            . ' WHERE ' . $this->config['primary'] . ' = ?';

        return $this->fetchRow($sql, [$id]);
    }

    private function hasExpired($row)
    {
        return $row[$this->config['modifiedColumn']] + $row[$this->config['lifetimeColumn']] < time();
    }

    private function didChangeRow($sql, $bind)
    {
        $result = $this->query($sql, $bind);

        if (is_object($result) && method_exists($result, 'rowCount')) {
            return (bool) $result->rowCount();
        }

        // mysqli in tracker mode
        return (bool) Db::get()->rowCount($result);
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
