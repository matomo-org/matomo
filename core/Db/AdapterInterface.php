<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Db;

use Exception;

/**
 */
interface AdapterInterface
{
    /**
     * Reset the configuration variables in this adapter.
     */
    public function resetConfig();

    /**
     * Return default port.
     *
     * @return int
     */
    public static function getDefaultPort();

    /**
     * Check database server version
     *
     * @throws Exception if database version is less than required version
     */
    public function checkServerVersion();

    /**
     * Returns true if this adapter's required extensions are enabled
     *
     * @return bool
     */
    public static function isEnabled();

    /**
     * Returns true if this adapter supports blobs as fields
     *
     * @return bool
     */
    public function hasBlobDataType();

    /**
     * Returns true if this adapter supports bulk loading
     *
     * @return bool
     */
    public function hasBulkLoader();

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     * @return bool
     */
    public function isErrNo($e, $errno);

    /**
     * Get a database lock
     *
     * @param string $lockName
     * @param int    $maxRetries
     *
     * @return bool True if a lock was obtained
     */
    public function getLock(string $lockName, int $maxRetries = 30) : bool;

    /**
     * Release a database lock
     *
     * @param string $lockName
     *
     * @return bool True if the lock was released
     */
    public function releaseLock(string $lockName) : bool;

    /**
     * Check if a database lock is available
     *
     * @param string $lockName
     *
     * @return bool  True if the lock is available
     */
    public function isLockAvailable(string $lockName) : bool;


    /**
     * Locks the supplied table or tables.
     *
     * **NOTE:** Matomo does not require the `LOCK TABLES` privilege to be available, it
     * should still work if this has not been granted.
     *
     * @param string|array $tablesToRead The table or tables to obtain 'read' locks on. Table names must
     *                                   be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @param string|array $tablesToWrite The table or tables to obtain 'write' locks on. Table names must
     *                                    be prefixed (see {@link Piwik\Common::prefixTable()}).
     *
     * @return bool  True if the lock statement completed successfully
     */
    public function lockTables($tablesToRead, $tablesToWrite = []) : bool;

    /**
     * Releases all table locks.
     *
     * **NOTE:** Matomo does not require the `LOCK TABLES` privilege to be available. It
     * should still work if this has not been granted.
     *
     * @return bool  True if the unlock statement completed successfully
     */
    public function unlockAllTables() : bool;

    /**
     * Is the connection character set equal to utf8?
     *
     * @return bool
     */
}
