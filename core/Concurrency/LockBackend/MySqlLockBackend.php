<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Concurrency\LockBackend;


use Piwik\Common;
use Piwik\Concurrency\LockBackend;
use Piwik\Db;

class MySqlLockBackend implements LockBackend
{
    const TABLE_NAME = 'locks';

    /**
     * fyi: does not support list keys at the moment just because not really needed so much just yet
     */
    public function getKeysMatchingPattern($pattern)
    {
        $sql = sprintf('SELECT SQL_NO_CACHE distinct `key` FROM %s WHERE `key` like ? and %s', self::getTableName(), $this->getQueryPartExpiryTime());
        $pattern = str_replace('*', '%', $pattern);
        $keys = Db::fetchAll($sql, array($pattern));
        $raw = array_column($keys, 'key');
        return $raw;
    }

    public function setIfNotExists($key, $value, $ttlInSeconds)
    {
        if (empty($ttlInSeconds)) {
            $ttlInSeconds = 999999999;
        }

        // FYI: We used to have an INSERT INTO ... ON DUPLICATE UPDATE ... However, this can be problematic in concurrency issues
        // because the ON DUPLICATE UPDATE may work successfully for 2 jobs at the same time but only one of them got the lock then.
        // This would be perfectly fine if we did something like `return $this->get($key) === $value` to 100% detect which process
        // got the lock as we do now. However, maybe the expireTime gets overwritten with a wrong value or so. That's why we
        // rather try to get the lock with the insert only because only one job can succeed with this. If below flow with the
        // delete becomes to slow, we may be able to use the INSERT INTO ... ON DUPLICATE UPDATE again.

        if ($this->get($key)) {
            return false; // a value is set, won't be possible to insert
        }
        
        $tablePrefixed = self::getTableName();

        // remove any existing but expired lock
        // todo: we could combine get() and keyExists() in one query!
        if ($this->keyExists($key)) {
            // most of the time an expired key should not exist... we don't want to lock the row unnecessarily therefore we check first
            // if value exists... 
            $sql = sprintf('DELETE FROM %s WHERE `key` = ? and not (%s)', $tablePrefixed, $this->getQueryPartExpiryTime());
            Db::query($sql, array($key));
        }

        $query = sprintf('INSERT INTO %s (`key`, `value`, `expiry_time`) 
                                 VALUES (?,?,(UNIX_TIMESTAMP() + ?))',
            $tablePrefixed);
        // we make sure to update the row if the key is expired and consider it as "deleted"

        try {
            Db::query($query, array($key, $value, (int) $ttlInSeconds));
        } catch (\Exception $e) {
            if ($e->getCode() == 23000
                || strpos($e->getMessage(), 'Duplicate entry') !== false
                || strpos($e->getMessage(), ' 1062 ') !== false) {
                return false;
            }
            throw $e;
        }

        // we make sure we got the lock
        return $this->get($key) === $value;
    }

    public function get($key)
    {
        $sql = sprintf('SELECT SQL_NO_CACHE `value` FROM %s WHERE `key` = ? AND %s LIMIT 1', self::getTableName(), $this->getQueryPartExpiryTime());
        return Db::fetchOne($sql, array($key));
    }

    public function deleteIfKeyHasValue($key, $value)
    {
        if (empty($value)) {
            return false;
        }

        $sql = sprintf('DELETE FROM %s WHERE `key` = ? and `value` = ?', self::getTableName());
        return $this->queryDidMakeChange($sql, array($key, $value));
    }

    public function expireIfKeyHasValue($key, $value, $ttlInSeconds)
    {
        if (empty($value)) {
            return false;
        }

        // we need to use unix_timestamp in mysql and not time() in php since the local time might be different on each server
        // better to rely on one central DB server time only
        $sql = sprintf('UPDATE %s SET expiry_time = (UNIX_TIMESTAMP() + ?) WHERE `key` = ? and `value` = ?', self::getTableName());
        $success = $this->queryDidMakeChange($sql, array((int) $ttlInSeconds, $key, $value));

        if (!$success) {
            // the above update did not work because the same time was already set and we just tried to set the same ttl
            // again too fast within one second
            return $value === $this->get($key);
        }

        return true;
    }

    public function keyExists($key)
    {
        $sql = sprintf('SELECT SQL_NO_CACHE 1 FROM %s WHERE `key` = ? LIMIT 1', self::getTableName());
        $value = Db::fetchOne($sql, array($key));
        return !empty($value);
    }

    private function queryDidMakeChange($sql, $bind = array())
    {
        $query = Db::query($sql, $bind);
        if (is_object($query) && method_exists($query, 'rowCount')) {
            // anything else but mysqli in tracker mode
            return (bool) $query->rowCount();
        } else {
            // mysqli in tracker mode
            return (bool) Db::get()->rowCount($query);
        }
    }

    private static function getTableName()
    {
        return Common::prefixTable(self::TABLE_NAME);
    }

    private function getQueryPartExpiryTime()
    {
        return 'UNIX_TIMESTAMP() <= expiry_time';
    }
}