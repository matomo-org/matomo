<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
     * Is the connection character set equal to utf8?
     *
     * @return bool
     */
    public function isConnectionUTF8();
}
