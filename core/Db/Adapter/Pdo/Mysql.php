<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db\Adapter\Pdo;

use Exception;
use PDO;
use PDOException;
use Piwik\Config;
use Piwik\Db\AdapterInterface;
use Piwik\Db\Adapter\MysqlAdapterCommon;
use Piwik\Piwik;
use Zend_Config;
use Zend_Db_Adapter_Pdo_Mysql;
use Zend_Db_Select;
use Zend_Db_Statement_Interface;

/**
 * Database adapter for use with the PDO\Mysql PHP extension
 */
class Mysql extends Zend_Db_Adapter_Pdo_Mysql implements AdapterInterface
{

    /**
     * Constructor
     *
     * @param array|Zend_Config $config database configuration
     */

    public function __construct($config)
    {
        // Enable LOAD DATA INFILE
        if (defined('PDO::MYSQL_ATTR_LOCAL_INFILE')) {
            $config['driver_options'][PDO::MYSQL_ATTR_LOCAL_INFILE] = true;
        }
        if ($config['enable_ssl']) {
            if (!empty($config['ssl_key'])) {
                $config['driver_options'][PDO::MYSQL_ATTR_SSL_KEY] = $config['ssl_key'];
            }
            if (!empty($config['ssl_cert'])) {
                $config['driver_options'][PDO::MYSQL_ATTR_SSL_CERT] = $config['ssl_cert'];
            }
            if (!empty($config['ssl_ca'])) {
                $config['driver_options'][PDO::MYSQL_ATTR_SSL_CA] = $config['ssl_ca'];
            }
            if (!empty($config['ssl_ca_path'])) {
                $config['driver_options'][PDO::MYSQL_ATTR_SSL_CAPATH] = $config['ssl_ca_path'];
            }
            if (!empty($config['ssl_cipher'])) {
                $config['driver_options'][PDO::MYSQL_ATTR_SSL_CIPHER] = $config['ssl_cipher'];
            }
            if (!empty($config['ssl_no_verify'])
                && defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')
            ) {
                $config['driver_options'][PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
        }
        parent::__construct($config);
    }

    /**
     * Returns connection handle
     */
    public function getConnection()
    {
        if ($this->_connection) {
            return $this->_connection;
        }

        $this->_connect();

        /**
         * Before MySQL 5.1.17, server-side prepared statements
         * do not use the query cache.
         * @see http://dev.mysql.com/doc/refman/5.1/en/query-cache-operation.html
         *
         * MySQL also does not support preparing certain DDL and SHOW
         * statements.
         * @see http://framework.zend.com/issues/browse/ZF-1398
         */
        $this->_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        return $this->_connection;
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     *
     * @return void
     */
    protected function _connect(): void
    {
        if ($this->_connection) {
            return;
        }

        parent::_connect();

        // MYSQL_ATTR_USE_BUFFERED_QUERY will use more memory when enabled
        // $this->_connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

        $this->_connection->exec('SET sql_mode = "' . MysqlAdapterCommon::SQL_MODE . '"');
    }

    /**
     * @inheritdoc
     */
    public static function isRecommendedAdapter(): bool
    {
        return true;
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
            throw new Exception(Piwik::translate('General_ExceptionDatabaseVersion', array('MySQL', $serverVersion, $requiredVersion)));
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
     *
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
     * Returns true if this adapter's required extensions are enabled
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return extension_loaded('PDO') && extension_loaded('pdo_mysql') && in_array('mysql', PDO::getAvailableDrivers());
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
     * @param  Exception $e
     * @param  string    $errno
     *
     * @return bool True if the error is valid for this adapter
     */
    public function isErrNo(Exception $e, string $errno): bool
    {
        return self::isPdoErrorNumber($e, $errno);
    }

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string    $errno
     *
     * @return bool
     */
    public static function isPdoErrorNumber(Exception $e, string $errno): bool
    {
        if (preg_match('/(?:\[|\s)([0-9]{4})(?:\]|\s)/', $e->getMessage(), $match)) {
            return $match[1] == $errno;
        }

        return false;
    }

    /**
     * Execute a SQL statement and ignore the result
     *
     * Wrapper for the PDO exec function to return affected row count rather than a zend_db_statement
     *
     * @param mixed $sql
     *
     * @return int
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function exec($sql): int
    {
        /** @var \Zend_Db_Statement $result */
        $result = parent::exec($sql);
        return $result->rowCount();
    }

    /**
     * Return number of affected rows in last query
     *
     * @param mixed $queryResult Result from query()
     * @return int
     */
    public function rowCount($queryResult): int
    {
        return $queryResult->rowCount();
    }

    /**
     * Retrieve client version in PHP style
     *
     * @return string|null
     */
    private function getClientVersion(): ?string
    {
        $this->_connect();
        try {
            $version = $this->_connection->getAttribute(PDO::ATTR_CLIENT_VERSION);
            $matches = null;
            if (preg_match('/((?:[0-9]{1,2}\.){1,3}[0-9]{1,2})/', $version, $matches)) {
                return $matches[1];
            }
        } catch (PDOException $e) {
            // In case of the driver doesn't support getting attributes
        }
        return null;
    }

    /**
     * @var \Zend_Db_Statement_Pdo[]
     */
    private $cachePreparedStatement = [];

    /**
     * Prepares and executes an SQL statement with bound data.
     * Caches prepared statements to avoid preparing the same query more than once
     *
     * @param string|Zend_Db_Select $sql The SQL statement with placeholders.
     * @param array $bind An array of data to bind to the placeholders.
     *
     * @return Zend_Db_Statement_Interface
     */
    public function query($sql, $bind = []): Zend_Db_Statement_Interface
    {
        if (!is_string($sql)) {
            return parent::query($sql, $bind);
        }

        if (isset($this->cachePreparedStatement[$sql])) {
            if (!is_array($bind)) {
                $bind = array($bind);
            }

            $stmt = $this->cachePreparedStatement[$sql];
            $stmt->execute($bind);
            return $stmt;
        }

        $stmt = parent::query($sql, $bind);
        $this->cachePreparedStatement[$sql] = $stmt;
        return $stmt;
    }

    /**
     * Override _dsn() to ensure host and port to not be passed along
     * if unix_socket is set since setting both causes unexpected behaviour
     * @see http://php.net/manual/en/ref.pdo-mysql.connection.php
     *
     * @return string
     */
    protected function _dsn(): string
    {
        if (!empty($this->_config['unix_socket'])) {
            unset($this->_config['host']);
            unset($this->_config['port']);
        }

        return parent::_dsn();
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
        // No reader session parameters supported for the mysql adapter
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
