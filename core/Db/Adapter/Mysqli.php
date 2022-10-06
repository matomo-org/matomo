<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db\Adapter;

use Exception;
use Piwik\Config;
use Piwik\Db;
use Piwik\Db\AdapterInterface;
use Piwik\Piwik;
use Zend_Config;
use Zend_Db_Adapter_Mysqli;

/**
 */
class Mysqli extends Zend_Db_Adapter_Mysqli implements AdapterInterface
{
    /**
     * Constructor
     *
     * @param array|Zend_Config $config database configuration
     */

    // this is used for indicate TransactionLevel Cache
    public $supportsUncommitted;

    public function __construct($config)
    {
        // Enable LOAD DATA INFILE
        $config['driver_options'][MYSQLI_OPT_LOCAL_INFILE] = true;

        if ($config['enable_ssl']) {
            if (!empty($config['ssl_key'])) {
                $config['driver_options']['ssl_key'] = $config['ssl_key'];
            }
            if (!empty($config['ssl_cert'])) {
                $config['driver_options']['ssl_cert'] = $config['ssl_cert'];
            }
            if (!empty($config['ssl_ca'])) {
                $config['driver_options']['ssl_ca'] = $config['ssl_ca'];
            }
            if (!empty($config['ssl_ca_path'])) {
                $config['driver_options']['ssl_ca_path'] = $config['ssl_ca_path'];
            }
            if (!empty($config['ssl_cipher'])) {
                $config['driver_options']['ssl_cipher'] = $config['ssl_cipher'];
            }
            if (!empty($config['ssl_no_verify'])) {
                $config['driver_options']['ssl_no_verify'] = $config['ssl_no_verify'];
            }
        }

        parent::__construct($config);
    }

    /**
     * Reset the configuration variables in this adapter.
     */
    public function resetConfig()
    {
        $this->_config = array();
    }

    /**
     * Return default port.
     *
     * @return int
     */
    public static function getDefaultPort()
    {
        return 3306;
    }

    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }

        // The default error reporting of mysqli changed in PHP 8.1. To circumvent problems in our error handling we set
        // the erroring reporting to the default that was used prior PHP 8.1
        // See https://php.watch/versions/8.1/mysqli-error-mode for more details
        mysqli_report(MYSQLI_REPORT_OFF);

        parent::_connect();

        $this->_connection->query('SET sql_mode = "' . Db::SQL_MODE . '"');
    }

    /**
     * Check MySQL version
     *
     * @throws Exception
     */
    public function checkServerVersion()
    {
        $serverVersion   = $this->getServerVersion();
        $requiredVersion = Config::getInstance()->General['minimum_mysql_version'];

        if (version_compare($serverVersion, $requiredVersion) === -1) {
            throw new Exception(Piwik::translate('General_ExceptionDatabaseVersion', array('MySQL', $serverVersion, $requiredVersion)));
        }
    }

    /**
     * Returns the MySQL server version
     *
     * @return null|string
     */
    public function getServerVersion()
    {
        // prioritizing SELECT @@VERSION in case the connection version string is incorrect (which can
        // occur on Azure)
        $versionInfo = $this->fetchAll('SELECT @@VERSION');

        if (count($versionInfo)) {
            return $versionInfo[0]['@@VERSION'];
        }

        return parent::getServerVersion();
    }

    /**
     * Check client version compatibility against database server
     *
     * @throws Exception
     */
    public function checkClientVersion()
    {
        $serverVersion = $this->getServerVersion();
        $clientVersion = $this->getClientVersion();

        // incompatible change to DECIMAL implementation in 5.0.3
        if (version_compare($serverVersion, '5.0.3') >= 0
            && version_compare($clientVersion, '5.0.3') < 0
        ) {
            throw new Exception(Piwik::translate('General_ExceptionIncompatibleClientServerVersions', array('MySQL', $clientVersion, $serverVersion)));
        }
    }

    /**
     * Return number of affected rows in last query
     *
     * @param mixed $queryResult Result from query()
     * @return int
     */
    public function rowCount($queryResult)
    {
        return mysqli_affected_rows($this->_connection);
    }

    /**
     * Returns true if this adapter's required extensions are enabled
     *
     * @return bool
     */
    public static function isEnabled()
    {
        $extensions = @get_loaded_extensions();
        return in_array('mysqli', $extensions);
    }

    /**
     * Returns true if this adapter supports blobs as fields
     *
     * @return bool
     */
    public function hasBlobDataType()
    {
        return true;
    }

    /**
     * Returns true if this adapter supports bulk loading
     *
     * @return bool
     */
    public function hasBulkLoader()
    {
        return true;
    }

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     * @return bool
     */
    public function isErrNo($e, $errno)
    {
        return self::isMysqliErrorNumber($e, $this->_connection, $errno);
    }

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     * @return bool
     */
    public static function isMysqliErrorNumber($e, $connection, $errno)
    {
        if (is_null($connection)) {
            if (preg_match('/(?:\[|\s)([0-9]{4})(?:\]|\s)/', $e->getMessage(), $match)) {
                return $match[1] == $errno;
            }
            return mysqli_connect_errno() == $errno;
        }

        return mysqli_errno($connection) == $errno;
    }

    /**
     * Execute unprepared SQL query and throw away the result
     *
     * Workaround some SQL statements not compatible with prepare().
     * See http://framework.zend.com/issues/browse/ZF-1398
     *
     * @param string $sqlQuery
     * @return int  Number of rows affected (SELECT/INSERT/UPDATE/DELETE)
     */
    public function exec($sqlQuery)
    {
        $rc = mysqli_query($this->_connection, $sqlQuery);
        $rowsAffected = mysqli_affected_rows($this->_connection);
        if (!is_bool($rc)) {
            mysqli_free_result($rc);
        }
        return $rowsAffected;
    }


    /**
     * Get client version
     *
     * @return string
     */
    public function getClientVersion()
    {
        $this->_connect();

        $version  = $this->_connection->server_version;
        $major    = (int)($version / 10000);
        $minor    = (int)($version % 10000 / 100);
        $revision = (int)($version % 100);

        return $major . '.' . $minor . '.' . $revision;
    }

    /**
     * Get a database lock
     *
     * @param string $lockName
     * @param int    $maxRetries
     *
     * @return bool true if a lock was obtained
     */
    public function getLock(string $lockName, int $maxRetries = 30) : bool
    {
        if (strlen($lockName) > 64) {
            throw new \Exception('DB lock name has to be 64 characters or less for MySQL 5.7 compatibility.');
        }

        /*
         * the server (e.g., shared hosting) may have a low wait timeout
         * so instead of a single GET_LOCK() with a 30 second timeout,
         * we use a 1 second timeout and loop, to avoid losing our MySQL
         * connection
         */
        $sql = 'SELECT GET_LOCK(?, 1)';

        while ($maxRetries > 0) {
            $result = $this->fetchOne($sql, array($lockName));
            if ($result == '1') {
                return true;
            }
            $maxRetries--;
        }

        return false;
    }

    /**
     * Release a database lock
     *
     * @param string $lockName
     *
     * @return bool True if the lock was released
     */
    public function releaseLock(string $lockName) : bool
    {
        return $this->fetchOne('SELECT RELEASE_LOCK(?)', [$lockName]) == '1';
    }

    /**
     * Check if a database lock is available
     *
     * @param string $lockName
     *
     * @return bool  True if the lock is available
     */
    public function isLockAvailable(string $lockName) : bool
    {
        return (bool)$this->fetchOne('SELECT IS_FREE_LOCK(?)', [$lockName]);
    }

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
    public function lockTables($tablesToRead, $tablesToWrite = []) : bool
    {
        if (!is_array($tablesToRead)) {
            $tablesToRead = array($tablesToRead);
        }

        if (!is_array($tablesToWrite)) {
            $tablesToWrite = array($tablesToWrite);
        }

        $lockExprs = array();
        foreach ($tablesToWrite as $table) {
            $lockExprs[] = $table . " WRITE";
        }

        foreach ($tablesToRead as $table) {
            $lockExprs[] = $table . " READ";
        }

        return self::fetchOne("LOCK TABLES " . implode(', ', $lockExprs)) == '1';
    }

    /**
     * Releases all table locks.
     *
     * **NOTE:** Matomo does not require the `LOCK TABLES` privilege to be available. It
     * should still work if this has not been granted.
     *
     * @return bool  True if the unlock statement completed successfully
     */
    public function unlockAllTables() : bool
    {
        return self::fetchOne("UNLOCK TABLES") == '1';
    }

}
