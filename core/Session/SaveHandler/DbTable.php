<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Session\SaveHandler;

use Piwik\Common;
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

    private const MAX_MERGE_ATTEMPTS = 3;

    private array $dataAtReadTime = [];

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
        $result = $this->fetchData($id);

        $this->dataAtReadTime[$id] = $result;

        return $result;
    }

    private function fetchData(string $hashedId): string
    {
        $sql = 'SELECT ' . $this->config['dataColumn'] . ' FROM `' . $this->config['name'] . '`'
            . ' WHERE ' . $this->config['primary'] . ' = ?'
            . ' AND ' . $this->config['modifiedColumn'] . ' + ' . $this->config['lifetimeColumn'] . ' >= ?';

        $result = $this->fetchOne($sql, [$hashedId, time()]);

        if (!$result) {
            $result = '';
        }

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

        if (!array_key_exists($id, $this->dataAtReadTime)) {
            $this->save($id, $data);

            return true;
        }

        $dataAtReadTime = $this->dataAtReadTime[$id];

        if ($dataAtReadTime !== '' && $data === $dataAtReadTime) {
            $this->refreshExpiration($id);

            return true;
        }

        for ($attempt = 0; $attempt < self::MAX_MERGE_ATTEMPTS; $attempt++) {
            $storedData = $this->fetchData($id);

            if ($storedData === '' || $storedData === $dataAtReadTime) {
                break;
            }

            $mergedData = $this->mergeSessionData($dataAtReadTime, $storedData, $data);

            if ($mergedData === null) {
                break;
            }

            if ($mergedData === $storedData) {
                $this->refreshExpiration($id);

                return true;
            }

            if ($this->replaceData($id, $storedData, $mergedData)) {
                return true;
            }
        }

        $this->save($id, $data);

        return true;
    }

    private function save(string $hashedId, $data): void
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

        $this->query($sql, [$hashedId, time(), $this->maxLifetime, $data, time(), $this->maxLifetime, $data]);
    }

    private function refreshExpiration(string $hashedId): void
    {
        $sql = 'UPDATE ' . $this->config['name']
            . ' SET ' . $this->config['modifiedColumn'] . ' = ?,'
            . $this->config['lifetimeColumn'] . ' = ?'
            . ' WHERE ' . $this->config['primary'] . ' = ?';

        $this->query($sql, [time(), $this->maxLifetime, $hashedId]);
    }

    private function replaceData(string $hashedId, string $expectedData, string $data): bool
    {
        $sql = 'UPDATE ' . $this->config['name']
            . ' SET ' . $this->config['modifiedColumn'] . ' = ?,'
            . $this->config['lifetimeColumn'] . ' = ?,'
            . $this->config['dataColumn'] . ' = ?'
            . ' WHERE ' . $this->config['primary'] . ' = ?'
            . ' AND ' . $this->config['dataColumn'] . ' = ?';

        $statement = Db::query($sql, [time(), $this->maxLifetime, $data, $hashedId, $expectedData]);

        return $statement->rowCount() > 0;
    }

    private function mergeSessionData(string $dataAtReadTime, string $storedData, $data): ?string
    {
        $valuesAtReadTime = $this->unserializeSessionData($dataAtReadTime);
        $storedValues = $this->unserializeSessionData($storedData);
        $values = $this->unserializeSessionData(is_string($data) ? $data : '');

        if ($valuesAtReadTime === null || $storedValues === null || $values === null) {
            return null;
        }

        return serialize($this->mergeChangedValues($valuesAtReadTime, $storedValues, $values));
    }

    private function mergeChangedValues(array $valuesAtReadTime, array $storedValues, array $values): array
    {
        $merged = $storedValues;

        foreach ($values as $key => $value) {
            $existedAtReadTime = array_key_exists($key, $valuesAtReadTime);

            if ($existedAtReadTime && $valuesAtReadTime[$key] === $value) {
                continue;
            }

            if (
                $existedAtReadTime
                && is_array($value)
                && is_array($valuesAtReadTime[$key])
                && isset($storedValues[$key])
                && is_array($storedValues[$key])
            ) {
                $merged[$key] = $this->mergeChangedValues($valuesAtReadTime[$key], $storedValues[$key], $value);
                continue;
            }

            $merged[$key] = $value;
        }

        foreach ($valuesAtReadTime as $key => $value) {
            if (!array_key_exists($key, $values)) {
                unset($merged[$key]);
            }
        }

        return $merged;
    }

    private function unserializeSessionData(string $data): ?array
    {
        if ($data === '') {
            return null;
        }

        $values = Common::safe_unserialize($data);

        if (!is_array($values) || $this->containsObject($values)) {
            return null;
        }

        return $values;
    }

    private function containsObject(array $values): bool
    {
        foreach ($values as $value) {
            if (is_object($value)) {
                return true;
            }

            if (is_array($value) && $this->containsObject($value)) {
                return true;
            }
        }

        return false;
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

        unset($this->dataAtReadTime[$id]);

        $sql = 'DELETE FROM `' . $this->config['name'] . '` WHERE ' . $this->config['primary'] . ' = ?';

        $this->query($sql, [$id]);

        return true;
    }

    /**
     * Destroys all Sessions - removes all rows in Session table
     */
    public function destroyAll(): bool
    {
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
