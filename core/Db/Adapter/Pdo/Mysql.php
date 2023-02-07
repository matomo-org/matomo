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
use Piwik\Db;
use Piwik\Db\AdapterInterface;
use Piwik\Piwik;
use Zend_Config;
use Zend_Db_Adapter_Pdo_Mysql;
use Zend_Db_Select;
use Zend_Db_Statement_Interface;

/**
 */
class Mysql extends Zend_Db_Adapter_Pdo_Mysql implements AdapterInterface
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
     *
     * @return resource
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

    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }

        $sql = 'SET sql_mode = "' . Db::SQL_MODE . '"';

        try {
            parent::_connect();
            $this->_connection->exec($sql);
        } catch (Exception $e) {
            if ($this->isErrNo($e, \Piwik\Updater\Migration\Db::ERROR_CODE_MYSQL_SERVER_HAS_GONE_AWAY)) {
                // mysql may return a MySQL server has gone away error when trying to establish the connection.
                // in that case we want to retry establishing the connection once after a short sleep
                $this->_connection = null; // we need to unset, otherwise parent connect won't retry
                usleep(400 * 1000);
                parent::_connect();
                $this->_connection->exec($sql);
            } else {
                throw $e;
            }
        }
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
     * Returns true if this adapter's required extensions are enabled
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return extension_loaded('PDO') && extension_loaded('pdo_mysql') && in_array('mysql', PDO::getAvailableDrivers());
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
        return self::isPdoErrorNumber($e, $errno);
    }


    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     * @return bool
     */
    public static function isPdoErrorNumber($e, $errno)
    {
        if (preg_match('/(?:\[|\s)([0-9]{4})(?:\]|\s)/', $e->getMessage(), $match)) {
            return $match[1] == $errno;
        }

        return false;
    }

    /**
     * Is the connection character set equal to utf8?
     *
     * @return bool
     */
    public function isConnectionUTF8()
    {
        $charsetInfo = $this->fetchAll('SHOW VARIABLES LIKE ?', array('character_set_connection'));

        if (empty($charsetInfo)) {
            return false;
        }

        $charset = $charsetInfo[0]['Value'];
        return strpos($charset, 'utf8') === 0;
    }


    /**
     * Return number of affected rows in last query
     *
     * @param mixed $queryResult Result from query()
     * @return int
     */
    public function rowCount($queryResult)
    {
        return $queryResult->rowCount();
    }

    /**
     * Retrieve client version in PHP style
     *
     * @return string
     */
    public function getClientVersion()
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
    private $cachePreparedStatement = array();

    /**
     * Prepares and executes an SQL statement with bound data.
     * Caches prepared statements to avoid preparing the same query more than once
     *
     * @param string|Zend_Db_Select $sql The SQL statement with placeholders.
     * @param array $bind An array of data to bind to the placeholders.
     * @return Zend_Db_Statement_Interface
     */
    public function query($sql, $bind = array())
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
     */
    protected function _dsn()
    {
        if (!empty($this->_config['unix_socket'])) {
            unset($this->_config['host']);
            unset($this->_config['port']);
        }

        return parent::_dsn();
    }
}
