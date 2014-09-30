<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db\Adapter\Pdo;

use Exception;
use PDO;
use PDOException;
use Piwik\Config;
use Piwik\Db\AdapterInterface;
use Piwik\Piwik;
use Zend_Db;
use Zend_Db_Adapter_Exception;
use Zend_Db_Adapter_Pdo_Mssql;
use Zend_Db_Profiler;

/**
 */
class Mssql extends Zend_Db_Adapter_Pdo_Mssql implements AdapterInterface
{
    /**
     * Returns connection handle
     *
     * @throws Zend_Db_Adapter_Exception
     * @return resource
     */
    public function getConnection()
    {
        // if we already have a PDO object, no need to re-connect.
        if ($this->_connection) {
            return $this->_connection;
        }

        $this->_pdoType = "sqlsrv";
        // get the dsn first, because some adapters alter the $_pdoType
        //$dsn = $this->_dsn();

        // check for PDO extension
        if (!extension_loaded('pdo')) {
            /**
             * @see Zend_Db_Adapter_Exception
             */
            throw new \Zend_Db_Adapter_Exception('The PDO extension is required for this adapter but the extension is not loaded');
        }

        // check the PDO driver is available
        if (!in_array($this->_pdoType, PDO::getAvailableDrivers())) {
            /**
             * @see Zend_Db_Adapter_Exception
             */
            throw new \Zend_Db_Adapter_Exception('The ' . $this->_pdoType . ' driver is not currently installed');
        }

        // create PDO connection
        $q = $this->_profiler->queryStart('connect', Zend_Db_Profiler::CONNECT);

        // add the persistence flag if we find it in our config array
        if (isset($this->_config['persistent']) && ($this->_config['persistent'] == true)) {
            $this->_config['driver_options'][PDO::ATTR_PERSISTENT] = true;
        }

        try {
            $serverName = $this->_config["host"];
            $database   = $this->_config["dbname"];
            if (is_null($database)) {
                $database = 'master';
            }
            $uid = $this->_config['username'];
            $pwd = $this->_config['password'];
            if ($this->_config["port"] != "") {
                $serverName = $serverName . "," . $this->_config["port"];
            }

            $this->_connection = new PDO("sqlsrv:$serverName", $uid, $pwd, array('Database' => $database));

            if ($this->_connection === false) {
                die(self::FormatErrors(sqlsrv_errors()));
            }

            /*
            $this->_connection = new PDO(
                $dsn,
                $this->_config['username'],
                $this->_config['password'],
                $this->_config['driver_options']
            );
            */

            $this->_profiler->queryEnd($q);

            // set the PDO connection to perform case-folding on array keys, or not
            $this->_connection->setAttribute(PDO::ATTR_CASE, $this->_caseFolding);
            $this->_connection->setAttribute(PDO::SQLSRV_ENCODING_UTF8, true);

            // always use exceptions.
            $this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $this->_connection;
        } catch (PDOException $e) {
            /**
             * @see Zend_Db_Adapter_Exception
             */
            throw new \Zend_Db_Adapter_Exception($e->getMessage(), $e->getCode(), $e);
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
        return 1433;
    }

    /**
     * Check MSSQL version
     *
     * @throws Exception
     */
    public function checkServerVersion()
    {
        $serverVersion   = $this->getServerVersion();
        $requiredVersion = Config::getInstance()->General['minimum_mssql_version'];

        if (version_compare($serverVersion, $requiredVersion) === -1) {
            throw new Exception(Piwik::translate('General_ExceptionDatabaseVersion', array('MSSQL', $serverVersion, $requiredVersion)));
        }
    }

    /**
     * Returns the Mssql server version
     *
     * @return null|string
     */
    public function getServerVersion()
    {
        try {
            $stmt   = $this->query("SELECT CAST(SERVERPROPERTY('productversion') as VARCHAR) as productversion");
            $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);
            if (count($result)) {
                return $result[0][0];
            }
        } catch (PDOException $e) {
        }

        return null;
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

        if (version_compare($serverVersion, '10') >= 0
            && version_compare($clientVersion, '10') < 0
        ) {
            throw new Exception(Piwik::translate('General_ExceptionIncompatibleClientServerVersions', array('MSSQL', $clientVersion, $serverVersion)));
        }
    }

    /**
     * Returns true if this adapter's required extensions are enabled
     *
     * @return bool
     */
    public static function isEnabled()
    {
        $extensions = @get_loaded_extensions();
        return in_array('PDO', $extensions) && in_array('pdo_sqlsrv', $extensions);
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
        /**
         * BULK INSERT doesn't have a way to escape a terminator that appears in a value
         *
         * @link http://msdn.microsoft.com/en-us/library/ms188365.aspx
         */
        return false;
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
        //check the getconnection, it's specified on the connection string.
        return true;
    }

    /**
     * Retrieve client version in PHP style
     *
     * @throws Exception
     * @return string
     */
    public function getClientVersion()
    {
        $this->_connect();
        try {
            $version = $this->_connection->getAttribute(PDO::ATTR_CLIENT_VERSION);
            $requiredVersion = Config::getInstance()->General['minimum_mssql_client_version'];
            if (version_compare($version['DriverVer'], $requiredVersion) === -1) {
                throw new Exception(Piwik::translate('General_ExceptionDatabaseVersion', array('MSSQL', $version['DriverVer'], $requiredVersion)));
            } else {
                return $version['DriverVer'];
            }
        } catch (PDOException $e) {
            // In case of the driver doesn't support getting attributes
        }

        return null;
    }
}
