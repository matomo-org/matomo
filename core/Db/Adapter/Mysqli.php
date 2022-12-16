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
use Piwik\Db\AdapterInterface;
use Piwik\Piwik;
use Zend_Config;
use Zend_Db_Adapter_Mysqli;

/**
 * Database adapter for use with the Mysqli PHP extension
 */
class Mysqli extends Zend_Db_Adapter_Mysqli implements AdapterInterface
{

    /**
     * Constructor
     *
     * @param array|Zend_Config $config database configuration
     */
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
     *
     * @return void
     */
    public function resetConfig(): void
    {
        $this->_config = [];
    }

    /**
     * Return default port.
     *
     * @return int
     */
    public static function getDefaultPort(): int
    {
        return 3306;
    }

    /**
     * @throws \Zend_Db_Adapter_Mysqli_Exception
     *
     * @return void
     */
    protected function _connect(): void
    {
        if ($this->_connection) {
            return;
        }

        // The default error reporting of mysqli changed in PHP 8.1. To circumvent problems in our error handling we set
        // the erroring reporting to the default that was used prior PHP 8.1
        // See https://php.watch/versions/8.1/mysqli-error-mode for more details
        mysqli_report(MYSQLI_REPORT_OFF);

        parent::_connect();

        $this->_connection->query('SET sql_mode = "' . MysqlAdapterCommon::SQL_MODE . '"');
    }

    /**
     * @inheritdoc
     */
    public static function isRecommendedAdapter(): bool
    {
        return false;
    }

    /**
     * Check MySQL version
     *
     * @throws Exception
     *
     * @return void
     */
    public function checkServerVersion(): void
    {
        $serverVersion   = $this->getServerVersion();
        $requiredVersion = Config::getInstance()->General['minimum_mysql_version'];

        if (version_compare($serverVersion, $requiredVersion) === -1) {
            throw new Exception(Piwik::translate('General_ExceptionDatabaseVersion', ['MySQL', $serverVersion, $requiredVersion]));
        }
    }

    /**
     * Returns the MySQL server version
     *
     * @return null|string
     */
    public function getServerVersion(): ?string
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
     * @return void
     */
    public function checkClientVersion(): void
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
    public function rowCount($queryResult): int
    {
        return mysqli_affected_rows($this->_connection);
    }

    /**
     * Returns true if this adapter's required extensions are enabled
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        $extensions = @get_loaded_extensions();
        return in_array('mysqli', $extensions);
    }

    /**
     * Returns true if this adapter supports blobs as fields
     *
     * @return bool
     */
    public function hasBlobDataType(): bool
    {
        return true;
    }

    /**
     * Returns true if this adapter supports bulk loading
     *
     * @return bool
     */
    public function hasBulkLoader(): bool
    {
        return true;
    }

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     *
     * @return bool
     */
    public function isErrNo($e, $errno): bool
    {
        return self::isMysqliErrorNumber($e, $this->_connection, $errno);
    }

    /**
     * Test error number
     *
     * @param Exception $e
     * @param mysqli|null $connection
     * @param string $errno
     *
     * @return bool
     */
    public static function isMysqliErrorNumber(Exception $e, ?mysqli $connection, string $errno): bool
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
     *
     * @return int  Number of rows affected (SELECT/INSERT/UPDATE/DELETE)
     */
    public function exec(string $sqlQuery): int
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
    private function getClientVersion(): string
    {
        $this->_connect();

        $version  = $this->_connection->server_version;
        $major    = (int)($version / 10000);
        $minor    = (int)($version % 10000 / 100);
        $revision = (int)($version % 100);

        return $major . '.' . $minor . '.' . $revision;
    }

    /**
     * Allow any adapter specific read session parameters to be set when the connection is created
     *
     * @param array $dbConfig An array of all database configuration settings
     *
     * @throws Exception
     *
     * @return void
     */
    public function setReaderSessionParameters($dbConfig): void
    {
        // Aurora read replica settings
        if (!empty($dbConfig['aurora_readonly_read_committed'])) {
            $this->exec('set session aurora_read_replica_read_committed = ON;set session transaction isolation level read committed;');
        }
    }

    /**
     * @inheritdoc
     */
    public static function optimizeTables($tables, bool $force = false): bool
    {
        return MysqlAdapterCommon::optimizeTables($tables, $force);
    }

    /**
     * @inheritdoc
     */
    public static function getDbLock(string $lockName, int $maxRetries = 30): bool
    {
        return MysqlAdapterCommon::getDbLock($lockName, $maxRetries);
    }

    /**
     * @inheritdoc
     */
    public static function releaseDbLock(string $lockName): bool
    {
        return MysqlAdapterCommon::releaseDbLock($lockName);
    }

    /**
     * @inheritdoc
     */
    public static function lockTables(array $tablesToRead, array $tablesToWrite = []): void
    {
        MysqlAdapterCommon::lockTables($tablesToRead, $tablesToWrite);
    }

    /**
     * @inheritdoc
     */
    public static function unlockAllTables(): void
    {
        MysqlAdapterCommon::unlockAllTables();
    }

    /**
     * @inheritdoc
     */
    public static function logExtraInfoIfDeadlock($ex): void
    {
        MysqlAdapterCommon::logExtraInfoIfDeadlock($ex);
    }

    /**
     * @inheritdoc
     */
    public static function overriddenExceptionMessage(string $message): string
    {
        return MysqlAdapterCommon::overriddenExceptionMessage($message);
    }

    /**
     * @inheritdoc
     */
    public static function canLikelySetTransactionLevel(): bool
    {
        return MysqlAdapterCommon::canLikelySetTransactionLevel();
    }

    /**
     * @inheritdoc
     */
    public static function getTransationIsolationLevel(): ?string
    {
        return MysqlAdapterCommon::getTransationIsolationLevel();
    }

    /**
     * @inheritdoc
     */
    public static function setTransactionIsolationLevelReadUncommitted(): void
    {
        MysqlAdapterCommon::setTransactionIsolationLevelReadUncommitted();
    }

    /**
     * @inheritdoc
     */
    public static function restorePreviousTransactionIsolationLevel(string $previous): void
    {
        MysqlAdapterCommon::restorePreviousTransactionIsolationLevel();
    }

    /**
     * @inheritdoc
     */
    public static function getDefaultCharset(): string
    {
        MysqlAdapterCommon::getDefaultCharset();
    }

    /**
     * @inheritdoc
     */
    public static function getUtf8mb4ConversionQueries(): array
    {
        MysqlAdapterCommon::getUtf8mb4ConversionQueries();
    }

    /**
     * @inheritdoc
     */
    public static function addMaxExecutionTimeHintToQuery(string $sql, int $limit): string
    {
        return MysqlAdapterCommon::addMaxExecutionTimeHintToQuery($sql, $limit);
    }

}
